<?php
namespace Http\Command;

use Module\Excel;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class SolarExcel 태양광 엑셀 다운로드
 */
class SolarExcel extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /** @var \PHPExcel|null $objPHPExcel 엑셀 */
    private ?\PHPExcel $objPHPExcel = null;

    /** @var string|null $fileName 엑셀 파일 이름 */
    private ?string $fileName = null;

    /** @var int $option 에너지타입 */
    private int $option = 11;

    /**
     * Class SolarExcel constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
        $this->objPHPExcel = new \PHPExcel();
    }

    /**
     * Class SolarExcel destructor.
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
        $this->fileName = '태양광_' . date('YmdHis');
    }

    /**
     * 태양광 생산량,소비량 데이터 추출
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $start
     * @param string $end
     * @param array $solarSensors
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getSolarUsedData(string $complexCodePk, int $option, int $dateType, string $start, string $end, array $solarSensors) : array
    {
        $fcData = [];

        $usage = $this->usage;

        $start = str_replace('-', '', $start);
        $end = str_replace('-', '', $end);

        // 태양광 발전량
        $solarInOptions = [
            'solar_type' => 'I',
            'sensor' => $solarSensors['in'],
            'energy_name' => 'solar_in',
        ];
        // 태양광 소비량
        $solarOutOptions = [
            'solar_type' => 'O',
            'sensor' => $solarSensors['out'],
            'energy_name' => 'solar_out',
        ];

        if ($dateType === 0) {
            $solarInOptions['is_use_next_date'] = false;
            $solarOutOptions['is_use_next_date'] = false;

            $solarIns = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $start, $solarInOptions);
            $solarOuts = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $start, $solarOutOptions);
        } else {
            $solarIns = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $start, $end, $solarInOptions);
            $solarOuts = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $start, $end, $solarOutOptions);
        }

        $fcData = [
            'in' => $solarIns['data'],
            'out' => $solarOuts['data']
        ];

        return $fcData;
    }

    /**
     * 엑셀 포맷 설정
     *
     * @param array $data
     * @param int $decimalPoint
     *
     * @throws \PHPExcel_Exception
     */
    private function setExcel(array $data, int $decimalPoint): void
    {
        $objPHPExcel = $this->objPHPExcel;

        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1", 'No');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B1", '기준');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C1", '생산량');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D1", '소비량');

        $fcKeys = array_keys($data['in']);
        for ($i = 0, $excelSequence = 2; $i < count($fcKeys); $i++, $excelSequence++) {
            $sequence = $i+1;

            $date = $fcKeys[$i];
            $solarInUsed = $data['in'][$date];
            $solarOutUsed = $data['out'][$date];

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$excelSequence}", $sequence);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B{$excelSequence}", $date);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C{$excelSequence}", number_format($solarInUsed, $decimalPoint));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D{$excelSequence}", number_format($solarOutUsed, $decimalPoint));
        }
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

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $start = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '00000000';
        $end = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '00000000';
        $dateType = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 0;
        $decimalPoint = isset($params[3]['value']) === true ? $params[3]['value'] : 0;

        $option = $this->option;
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $solarSensors = $this->sensorObj->getSolarSensor();

        // 태양광 데이터 추출
        $this->initialize();
        $solarUseds = $this->getSolarUsedData($complexCodePk, $option, $dateType, $start, $end, $solarSensors);

        // 엑셀에 데이터 표현
        $this->setExcel($solarUseds, $decimalPoint);

        // 엑셀 모듈 실행
        $excelModules = new Excel($this->objPHPExcel, $this->fileName);
        $excelModules->onDownload();

        return true;
    }
}