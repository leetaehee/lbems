<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class Paper 보고서 조회
 */
class Paper extends Command
{
	/** @var array $checkboxKeys 폼 체크박스 키  */
	private array $checkboxKeys = [];

	/** @var array $floors 층 정보 */
	private array $floors = [];

	/** @var Usage|null $usage 사용량 | 요금 */
	private ?Usage $usage = null;

	/**
	 * Class Paper constructor.
	 */
	public function __construct() 
	{
		parent::__construct();

        $this->usage = new Usage();
	}

	/**
	 * Class Paper destructor.
	 */
	public function __destruct() 
	{
		parent::__destruct();
	}

	/**
	 * 입력 데이터 중 체크박스 가공 
	 *
	 * @param array $formData
	 *
	 * @return array $fcData
	 */
	private function getCheckBoxEnergyTypeKey(array $formData) : array
	{
		$fcData = [];

		$checkes = $this->checkboxKeys;

		$formDataKeys = array_keys($formData);
		$checkKeys = array_keys($checkes);

		if (count($formDataKeys) > 1) {
			unset($formDataKeys[0]);
			$formDataKeys = array_values($formDataKeys);
		}

		for ($i = 0; $i < count($formDataKeys); $i++) {
			$key = $formDataKeys[$i];
            $keyName = $checkes[$key]['key'];

			if (in_array($key, $checkKeys) === false) {
				return false;
			}
			$fcData[$keyName] = $checkes[$key];
		}

		return $fcData;
	}

	/**
	 * 사용량, 요금 조회를 위한 파라미터 정리 
	 *
	 * @param array $params
	 *
	 * @return array $fcData
	 */
	private function getCheckValue(array $params) : array
	{
		// 체크박스 데이터 받기
		parse_str($params[0]['energy_type'], $energyType);

        $fcData = [];
        $complexCodePk = $_SESSION['ss_complex_pk'];
        $dateType = isset($params[0]['period']) == true ? Utility::getInstance()->removeXSS($params[0]['period']) : '0';
        $realDateType = isset($params[0]['period']) == true ? Utility::getInstance()->removeXSS($params[0]['period']) : '0';
		$start = isset($params[0]['start']) == true ? Utility::getInstance()->removeXSS($params[0]['start']) : '0000-00-00';
		$end = isset($params[0]['end']) == true ? Utility::getInstance()->removeXSS($params[0]['end']) : '0000-00-00';
		$timelineFlag = isset($params[0]['timeline_flag']) == true ? Utility::getInstance()->removeXSS($params[0]['timeline_flag']) : '0';
		$floor = isset($params[0]['end']) == true ? Utility::getInstance()->removeXSS($params[0]['grp']) : 'all';
		$room = 'all';
        $sensor = '';
		$start = str_replace('-', '', $start);
        $end = str_replace('-', '', $end);
        $dong = 'all';

		$fcData = [
			'date' => $start,
			'date_type' => $dateType,
			'real_date_type' => $realDateType,
			'complex_code_pk' => $complexCodePk,
			'start' => $start,
			'end' => $end,
            'dong' => $dong,
			'floor' => $floor,
			'room' => $room,
            'sensor' => $sensor,
			'timeline_flag' => $timelineFlag
		];

		return [
			'params' => $fcData,
			'energy_type' => $energyType,
		];
	}

    /**
     * 전체 층에 사용량/요금 조회
     *
     * @param array $params
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
	private function getAllFloorData(array $params) : array
	{
		$fcData = [];
		$addOptions = [];

		$floors = $this->floors;
        $usage = $this->usage;

        $dateType = $params['date_type'];
		$complexCodePk = $params['complex_code_pk'];
		$start = $params['start'];
		$end = $params['end'];
		$room = $params['room'];
		$dong = $params['dong'];

        $this->sensorObj = $this->getSensorManager($complexCodePk);

		for ($i = 0; $i < count($floors); $i++) {
			$floor = $floors[$i];

            // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

            $addOptions = [
                'floor' => $floor,
                'room' => $room,
                'sensor' => $sensor,
                'energy_name' => 'electric', // 전기만 보여주기 때문에 하드코딩..
            ];

            if ($dateType === 0) {
                $addOptions['is_use_next_date'] = false;

                $usedData = $usage->getEnergyData($this, $complexCodePk, 0, $dateType, $start, $addOptions);
            } else {
                $usedData = $usage->getEnergyDataByRange($this, $complexCodePk, 0, $dateType, $start, $end, $addOptions);
            }
            
            // 층별로 키이름 지정하여 저장.
            $fcData[$floor] = $usedData['data'];
        }

        // 무등산 전체전력 + 소비량 계산하기 위해 태양광 소비량 조회
        $solarKeys = $this->sensorObj->getSolarSensor();

        $addOptions = [
            'sensor' => $solarKeys['out'],
            'solar_type' => 'O',
            'energy_name' => 'solar_out',
        ];

        if ($dateType === 0) {
            $addOptions['is_use_next_date'] = false;

            $d = $usage->getEnergyData($this, $complexCodePk, 11, $dateType, $start, $addOptions);
        } else {
            $d = $usage->getEnergyDataByRange($this, $complexCodePk, 11, $dateType, $start, $end, $addOptions);
        }

        $fcData['solar_out']  = $d['data'];

		return $fcData;
	}

    /**
     * 층별 세부 데이터 조회
     *
     * @param array $params
     * @param array $checks
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
	private function getFloorData(array $params, array $checks) : array
	{
		$fcData = [];
		$keySensors = [];

        $usage = $this->usage;

        $dateType = $params['date_type'];
		$complexCodePk = $params['complex_code_pk'];
		$start = $params['start'];
		$end = $params['end'];
        $floor = $params['floor'];
		$room = $params['room'];
        $sensor = $params['sensor'];

        $keySensors = $this->sensorObj->getSpecialSensorKeyName();

		foreach ($checks as $key => $value) {
            $addOptions = [];
            $keySensor = [];

		    // 에너지원 이름
			$saveKeyName = $key;

			// 키네임에 해당되는 센서번호 조회
            if (is_null($keySensors[$key]) === false) {
                $keySensor = $keySensors[$key];
            }

			// 에너지원 인덱스 번호
			$option = $value['option'];

            $addOptions = [
                'floor' => $floor,
                'room' => $room,
                'energy_name' => $key,
            ];

            // 사용량, 요금 조회
            if (count($keySensors) > 0 && count($keySensor) > 0) {
                if ($dateType === 0) {
                    $addOptions['is_use_next_date'] = false;

                    $usedData = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $start, $addOptions, $keySensor);
                } else {
                    $usedData = $usage->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType, $start, $end,$addOptions, $keySensor);
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

			// 데이터 저장 
			$fcData[$saveKeyName] = $usedData['data'];
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
     * @throws \Exception
     */
	public function execute(array $params) :? bool
	{
		$data = [];

		// 값 체크 및 가공
		$result = $this->getCheckValue($params);

		$params = $result['params'];
		$energyType = $result['energy_type'];

        $this->sensorObj = $this->getSensorManager($params['complex_code_pk']);

        // 보고서 키 정보 조회
        $paperInfoData = $this->sensorObj->getPaperInfo();
        $this->checkboxKeys = $paperInfoData['web_keys'];
        $this->floors = $this->sensorObj->getFloorInfo();

		// 체크박스 키를 에너지원 번호로 매핑 
		$checkboxKeys = $this->getCheckBoxEnergyTypeKey($energyType);
		if ($checkboxKeys === false) {
			$this->data = 'Error';
			return true;
		}

		if ($params['floor'] === 'all') {
			// 전체전기
			$data = $this->getAllFloorData($params);
		} else {
			// 층별 
			$data = $this->getFloorData($params, $checkboxKeys);
		}

		// 뷰에 데이터 전달 
		$this->data = $data;

		return true;
	}
}