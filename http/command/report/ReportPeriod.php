<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class ReportPeriod 기간별 조회 현황
 */
class ReportPeriod extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * ReportPeriod constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * ReportPeriod destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
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
        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $start = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS( $params[0]['value']) : date('Y-m-d');
        $end = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : date('Y-m-d');
        $dateType = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 2;
        $option = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : 0;
        $floor = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : 'all';
        $room = isset($parmas[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']) : 'all';
        $energyKey = isset($params[6]['value']) === true ? Utility::getInstance()->removeXSS($params[6]['value']) : 'electric';
        $dong = isset($params[7]['value']) === true ? Utility::getInstance()->removeXSS($params[7]['value']) : 'all';

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $data = $this->getReportPeriodData($complexCodePk, $option, $dateType, $start, $end, $dong, $floor, $room , $energyKey);
        $this->data = $data;

        return true;
    }

    /**
     * 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $startDate
     * @param string $endDate
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $energyKey
     *
     * @return array[]
     *
     * @throws \Exception
     */
    private function getReportPeriodData(string $complexCodePk, int $option, int $dateType, string $startDate, string $endDate, string $dong, string $floor, string $room, string $energyKey) : array
    {
        $fcData = [
            'data' => [],
            'price' => []
        ];

        $keySensors = [];
        $keySensor = [];
        $addOptions = [];

        $usage = $this->usage;
        $solarType = $option === 11 ? 'I' : '';
        $sensor = '';

        $startDate = str_replace('-', '', $startDate);
        $endDate = str_replace('-', '', $endDate);

        if ($option === 0) {
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        }

        if (is_null($this->sensorObj) === false) {
            $keySensors = $this->sensorObj->getSpecialSensorKeyName();
            if (is_null($keySensors[$energyKey]) === false) {
                // 설비별 및 에너지 키 이름 & 배열로 넘어와 조회 하는 경우
                $keySensor = $keySensors[$energyKey];
            }
        }

        if (count($keySensors) > 0 && count($keySensor) > 0) {
            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'solar_type' => $solarType,
                'is_use_next_date' => false,
                'energy_name' => $energyKey,
            ];

            if ($dateType === 0) {
                $addOptions['is_use_next_date'] = true;
                $fcData = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $startDate, $addOptions, $keySensor);
            } else {
                $fcData = $usage->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType,  $startDate,  $endDate, $addOptions, $keySensor);
            }
        } else {
            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'solar_type' => $solarType,
                'is_use_next_date' => false,
                'sensor' => $sensor,
                'energy_name' => $energyKey,
            ];

            if ($dateType === 0) {
               $addOptions['is_use_next_date'] = true;
               $fcData = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
            } else {
                $fcData = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
            }
        }

        return $fcData;
    }
}