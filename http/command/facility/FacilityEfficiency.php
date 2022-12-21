<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Efficiency;
use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class FacilityEfficiency 설비 효율 메뉴
 */
class FacilityEfficiency extends Command
{
    /** @var Efficiency|null $efficiency 효율 객체 */
    private ?Efficiency $efficiency = null;

    /** @var Usage|null  $usage 사용량 객체  */
    private ?Usage $usage = null;

    /**
     * FacilityEfficiency constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->efficiency = new Efficiency();
        $this->usage = new Usage();
    }

    /**
     * FacilityEfficiency Destructor.
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
    public function execute(array $params): ?bool
    {
        $data = [];

        $floor = 'all';
        $room = 'all';
        $sensor = '';

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $dateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 2;
        $date = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '0000-00-00';
        $option = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 0;
        $energyKey = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : 'electric';
        $startPage = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : 1;
        $viewPageCount = isset($params[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']) : 8;
        $sensor = isset($params[6]['value']) === true ? Utility::getInstance()->removeXSS($params[6]['value']) : '';

        $this->sensorObj = $this->getSensorManager($complexCodePk);
        if (is_null($this->sensorObj) === true) {
            $data['error'] = null;
            $this->data = $data;
            return true;
        }

        // 페이징 번호 셋팅
        $startPage = $startPage - 1;
        $startPage = $startPage < 1 ? 0 : ($startPage * $viewPageCount);

        // 설비 내역 조회
        if (empty($sensor) === true) {
            $data['summary'] = $this->getEfficiencyData($complexCodePk, $option, $dateType, $date, $floor, $room, $energyKey, $startPage, $viewPageCount);
        }

        if (empty($sensor) === true && isset($data['summary']['data']) === true) {
            // 프론트에서 역률 차트를 클릭하지 않은 경우 디폴트로 가져온다.
            $sensor = $this->getFirstSensorNo($data['summary']['data']);
        }

        $data['time'] = $this->getEfficiencyChartData($complexCodePk, $option, $date, $dateType, $floor, $room, $sensor, $energyKey);

        $this->data = $data;
        return true;
    }

    /**
     * 효율 정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $floor
     * @param string $room
     * @param string $energyKey
     * @param int $startPage
     * @param int $viewPageCount
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getEfficiencyData(string $complexCodePk, int $option, int $dateType, string $date, string $floor, string $room, string $energyKey, int $startPage, int $viewPageCount) :? array
    {
        $fcData = [];
        $keySensor = [];
        //$addOptions = [];

        $obj = $this->efficiency;
        $usage = $this->usage;

        // 주기에 따른 조회 날짜 출력
        $periods = $this->getPeriod($date, $dateType);
        $startDate = $periods['start_date'];
        $endDate = $periods['end_date'];

        // 키 네임으로 센서 정보 찾고자 하는 경우
        $keySensors = $this->sensorObj->getSpecialSensorKeyName();

        $efficiencyTables = Config::EFFICIENCY_TABLES;
        if (empty($efficiencyTables[$option]) === true) {
            return $fcData;
        }

        if (is_null($keySensors[$energyKey]) === false) {
            $keySensor = $keySensors[$energyKey];
        }

        $keySensor = $this->getSensorList($keySensor);
        if (count($keySensor) === 0) {
            return $fcData;
        }

        $keySensor = array_slice($keySensor, $startPage, $viewPageCount); // 페이징하기..

        $addOptions = [
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $energyKey,
        ];

        for ($fcIndex = 0; $fcIndex < count($keySensor); $fcIndex++) {
            $fcSensor = $keySensor[$fcIndex];

            if ($dateType === 1) {
                // 월로 검색 시 시작일~종료일 추가 되어야 함
                $addOptions['start_date'] = $startDate;
                $addOptions['end_date'] = $endDate;
            }

            $addOptions['sensor'] = $fcSensor;

            $totalsEfficiency = $obj->getEfficiencyDataByHome($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
            $efficiencyValues = isset($totalsEfficiency['current']) === true ? array_values($totalsEfficiency['current']['data']) : array_values($totalsEfficiency['data']);

            $totalUsage = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);

            $fcData['data'][$fcSensor] = [
                'efficiency' => [
                    //'list' => $efficiency,
                    'total' => $efficiencyValues[0],
                ],
                'usage' => [
                    'total' => $totalUsage['current']['data'],
                ],
            ];
        }

        $fcData['paging_total'] = count($keySensor);

        return $fcData;
    }

    /**
     * 주기에 따른 검색 날짜 조회
     *
     * @param string $date
     * @param string $dateType
     *
     * @return array $fcData
     */
    private function getPeriod(string $date, string $dateType) : array
    {
        $fcData = [];

        switch ($dateType)
        {
            case 0:
                // 금년
                $endMonth = date('Y-m', strtotime($date));
                $temp = explode('-', $endMonth);

                // 시작(월)~종료(월)
                $startDate = $temp[0];
                $endDate = $temp[0];

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                break;
            case 1:
            case 6:
                // 금월
                $date = date('Y-m-d', strtotime($date));
                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));

                $fcData = [
                    'start_date' => $today,
                    'end_date' => $today
                ];
                break;
        }

        return $fcData;
    }

    /**
     * 2차원 배열을 1차원으로 변환
     *
     * @param array $data
     *
     * @return array
     */
    private function getSensorList(array $data) : array
    {
        $fcData = [];

        if (count($data) === 0) {
            return $fcData;
        }

        foreach ($data as $k => $items) {
            foreach ($items as $i => $v) {
                array_push($fcData, $v);
            }
        }

        return $fcData;
    }

    /**
     * 배열의 첫번째 키 정보를 조회
     *
     * @param array $sensorData
     *
     * @return string
     */
    private function getFirstSensorNo(array $sensorData) : string
    {
        foreach ($sensorData as $key => $value) {
            return $key;
        }

        return '';
    }

    /**
     * 효율 차트 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param int $dateType
     * @param string $floor
     * @param string $room
     * @param string $sensor
     * @param string $energyKey
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEfficiencyChartData(string $complexCodePk, int $option, string $date, int $dateType, string $floor, string $room, string $sensor, string $energyKey) : array
    {
        $fcData = [];
        $fcEfficiency = [];
        $fcAddOptions = [];

        $obj = $this->efficiency;

        $fcAddOptions = [
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'energy_name' => $energyKey,
        ];

        // 주기에 따른 조회 날짜 출력
        $periods = $this->getPeriod($date, $dateType);
        $startDate = $periods['start_date'];
        $endDate = $periods['end_date'];

        if ($dateType === 0) {
            $fcAddOptions['is_use_next_date'] = false;

            $fcEfficiency = $obj->getEfficiencyData($this, $complexCodePk, $option, $dateType, $startDate, $fcAddOptions);
        } else {
            $fcEfficiency = $obj->getEfficiencyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $fcAddOptions);
        }

        $fcData = $fcEfficiency['data'];

        return $fcData;
    }
}