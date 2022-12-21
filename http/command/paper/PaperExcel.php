<?php
namespace Http\Command;

use Module\Excel;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class PaperExcel 보고서 엑셀 다운로드
 */
class PaperExcel extends Command
{
	/** @var Usage|null $usage 사용량 | 요금 */
	private ?Usage $usage = null;

    /** @var \PHPExcel|null $objPHPExcel 엑셀 */
	private ?\PHPExcel $objPHPExcel = null;

	/** @var string|null $paperFileName 보고서 파일 이름 */
	private ?string $paperFileName = null;

	/** @var array $floorByEnergyTypes 층별 에너지원  타입 및 층 정보  */
	private array $floorByEnergyTypes = [];

	/**
	 * Class PaperExcel constructor.
	 */
	public function __construct() 
	{
		parent::__construct();

        $this->usage = new Usage();
		$this->objPHPExcel = new \PHPExcel();
	}

	/**
	 * Class PaperExcel destructor.
	 */
	public function __destruct() 
	{
		parent::__destruct();
	}

	/** 
	 * 엑셀 설정 
	 */
	private function initialize()
	{
		$this->paperFileName = '보고서_' . date('YmdHis');
	}

	/**
     * 파라미터 체크
     *
     * @param array $params
     *
     * @return array
     */
    private function getCheckValue(array $params) : array
    {
        $fcData = [];
        $complexCodePk = $_SESSION['ss_complex_pk'];
        $dateType = isset($params[0]['period']) === true ? Utility::getInstance()->removeXSS($params[0]['period']) : 0;
        $start = isset($params[0]['start']) === true ? Utility::getInstance()->removeXSS($params[0]['start']) : '0000-00-00';
        $end = isset($params[0]['end']) === true ? Utility::getInstance()->removeXSS($params[0]['end']) : '0000-00-00';
        $differDay = isset($params[0]['differ_day']) === true ? Utility::getInstance()->removeXSS($params[0]['differ_day']) : '0000-00-00';
        $timelineFlag = isset($params[0]['timeline_flag']) === true ? Utility::getInstance()->removeXSS($params[0]['timeline_flag']) : 0;
        $decimalPoint = isset($params[0]['decimal_point']) === true ? Utility::getInstance()->removeXSS($params[0]['decimal_point']) : 0;
        $start = str_replace('-', '', $start);
        $end = str_replace('-', '', $end);
        $dong = 'all';

        $fcData = [
            'complex_code_pk' => $complexCodePk,
            'date_type' => $dateType,
            'differ_day' => $differDay,
            'dong' => $dong,
            'date' => $start,
            'start' => str_replace('-', '', $start),
            'end' => str_replace('-', '', $end),
            'timeline_flag' => $timelineFlag,
            'decimal_point' => $decimalPoint,
        ];

        return [
            'params' => $fcData
        ];
    }

    /**
     * 엑셀 포맷 설정
     *
     * @param array $used
     * @param int $differDay
     * @param int $decimalPoint
     *
     * @throws \PHPExcel_Exception
     */
	private function setExcel(array $used, int $differDay, int $decimalPoint) : void
	{
		$objPHPExcel = $this->objPHPExcel;
		$sheet = $objPHPExcel->getActiveSheet();

		$start = 1;
		$excelIndex = 2;
		$usedSum = 0;
		$usedAverage = 0.0;

		$count = count($used);

		// 컬럼 
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$start}", '층');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B{$start}", '에너지원');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C{$start}", '용도');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D{$start}", '사용량');

		foreach ($used AS $key => $values) {
			$used = $values['used'];
			if ($used < 1) {
				$used = 0;
			}

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$excelIndex}", $values['floor_group']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B{$excelIndex}", $values['energy_group']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C{$excelIndex}", $values['energy_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D{$excelIndex}", number_format($used, $decimalPoint));

			// 총 사용량 누적 
			$usedSum += $used;

			$excelIndex++;
		}

		if ($usedSum > 0) {
			$averageCount = $count;
			if ($differDay > 1) {
				$averageCount = $differDay;
			}

			$usedAverage = ceil($usedSum/$averageCount);
		}

		if ($count == 0) {
			// 총 사용량과 평균 사용량 표기 할 순서 표기
			$start = 4;
		}

		if ($count > 0) {
			$start = $count + 3;
		}

		// 총사용량과 위치 라인 설정
		$sheet->mergeCells("A{$start}:C{$start}");
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$start}", '총 사용량');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D{$start}", $usedSum);

		// 평균사용량과 위치 라인 설정
		$start += 1;
		$sheet->mergeCells("A{$start}:C{$start}");
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$start}", '평균 사용량');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D{$start}", $usedAverage);
	}

    /**
     * 엑셀에 출력될 데이터 조회
     *
     * @param array $params
     *
     * @return array|null
     *
     * @throws \Exception
     */
	private function getUsedData(array $params) :? array
	{
		$fcData = [];
		$keySensors = [];

        $usage = $this->usage;
		$floorData = $this->floorByEnergyTypes;
        $complexCodePk = $_SESSION['ss_complex_pk'];

		if (count($floorData) === 0) {
			return false;
		}

		$room = 'all';
		$sensor = '';
		$dateType = $params['date_type'];
        $start = $params['start'];
        $end = $params['end'];
        $dong = $params['dong'];

        $keySensors = $this->sensorObj->getSpecialSensorKeyName();

		foreach ($floorData AS $key => $values) {
            $addOptions = [];
            $keySensor = [];

			$option = $values['option'];
			$floor = $values['floor'];
			$energyType = $values['energy_type'];

            // 키네임에 해당되는 센서번호 조회
            if (is_null($keySensors[$energyType]) === false) {
                $keySensor = $keySensors[$energyType];
            }

            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'energy_name' => $energyType,
            ];

            // 사용량, 요금 조회
            if (count($keySensors) > 0 && count($keySensor) > 0) {
                if ($dateType === 0) {
                    $addOptions['is_use_next_date'] = false;

                    $usedData = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $start, $addOptions, $keySensor);
                } else {
                    $usedData = $usage->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType, $start, $end, $addOptions, $keySensor);
                }
            } else {
                $addOptions['sensor'] = $sensor;
                if ($dateType === 0) {
                    $addOptions['is_use_next_date'] = false;

                    $usedData = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $start, $addOptions);
                } else {
                    $usedData = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $start, $end, $addOptions);
                }
            }

            $usedSum = array_sum($usedData['data']);

			$fcData[$key] = [
				'floor_group' => $values['floor_group'],
				'energy_group' => $values['energy_group'],
				'energy_name' => $values['energy_name'],
				'used' => $usedSum
			];
		}

		return $fcData;
	}

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \PHPExcel_Exception
     */
	public function execute(array $params) :? bool
	{
		$data = [];

		// 값 체크 및 가공
        $result = $this->getCheckValue($params);
        $params = $result['params'];

        $this->sensorObj = $this->getSensorManager($params['complex_code_pk']);

        // 보고서 키 정보 조회
        $paperInfoData = $this->sensorObj->getPaperInfo();
        $this->floorByEnergyTypes = $paperInfoData['excel_keys'];

		// 엑셀에 필요한 값 설정 
		$this->initialize();

		// 엑셀에 보여질 데이터 조회
		$useds = $this->getUsedData($params);
		if ($useds === false) {
			$this->data = 'Error';
			return true;
		}

		// 일수
		$differDay = $params['differ_day'];

		// 엑셀에 데이터 출력 
		$this->setExcel($useds, $differDay, $params['decimal_point']);

		// 엑셀 모듈 실행 
		$excelModules = new Excel($this->objPHPExcel, $this->paperFileName);
		$excelModules->onDownload();

		return true;
	}
}