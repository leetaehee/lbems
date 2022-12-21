<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Config;
use EMS_Module\Indication;
use EMS_Module\Utility;

/**
 * Class Dashboard 대시보드 전체
 */
class Dashboard extends Command
{
    /** @var Usage|null $usage 사용량 | 요금  */
    private ?Usage $usage = null;

    /** @var Indication|null $indication 자립률 */
    private ?Indication $indication = null;

    /** @var array $energyType 에너지 타입 */
    private array $energyType = Config::SENSOR_TYPES;

    /**
     * Dashboard constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage;
        $this->indication = new Indication($this);
    }

    /**
     * Dashboard Destructor.
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
     * @return bool|mixed
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $usage = $this->usage;
        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);

        $dateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        $dong = 'all';
        $floor = 'all';
        $room = 'all';

        // 건물 위치 센서 조회
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

        // 전기 층별 센서 조회
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 에너지원 데이터 조회
        $energyData = $this->getEnergyData($complexCodePk, $dateType, $floor, $room, $sensor);
        // 용도별 사용분포도 조회
        $usageData = $this->getUsageData($complexCodePk, $dateType, $floor, $room, $sensor);
        // 설비별 사용현황 데이터 조회
        $facilityData = $this->getFacilityData($complexCodePk, $dateType, $floor, $room);
        // 태양광 데이터 조회
        $solarData = $this->getSolarData($complexCodePk, $dateType);
        // 에너지 소비량 대비 생산량, Co2 조회
        $independenceData = $this->getIndependenceData($complexCodePk);
        // 미세먼지 조회
        $finedustData = $this->getFinedustData($complexCodePk);
        // 캐시버튼 활성화
        $devOptions = $this->devOptions;

        if (is_null($usageData) === false && is_null($facilityData) === false) {
            // 용도별 분포도에 항목추가
            $electricBuildingUsed = $this->getBuildingElectricUsed($complexCodePk, $dateType, $floor, $room, $sensor);
            $usageData = $this->addUsageItem('power_train', $electricBuildingUsed, $usageData, $facilityData);
        }

        // 뷰에 보여줄 데이터
        $data = [
            'energy_data' => $energyData,
            'usage_data' => $usageData,
            'facility_data' => $facilityData,
            'solar_data' => $solarData,
            'independence_data' => $independenceData,
            'finedust_data' => $finedustData,
            'is_enabled_watchdog' => $devOptions['IS_ENABLED_WATCHDOG'],
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 에너지원 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $floor
     * @param string $room
     * @param string $sensor
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEnergyData(string $complexCodePk, int $dateType, string $floor, string $room, string $sensor) :array
    {
        $fcData = [];
        $nows = [];
        $lasts = [];
        $targets = [];
        $homeData = [];
        $temps = [];

        $usage = $this->usage;
        $energyKeys = $this->energyType;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $option = 0;
        $key = $energyKeys[$option];

        $addOptions = [
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'energy_name' => 'electric', // 전기만 진행하기 때문에..
        ];
        $nows[$key] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
        $homeNowData = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);

        // 전일
        $tempDate = $usage->getLastDate($date, $dateType);

        $addOptions['time_type'] = 'last';
        $lasts[$key] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $tempDate, $addOptions);
        $homeLastData= $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $tempDate, $addOptions);

        // 기준값
        $targets[$key] = $usage->getReference($this, $complexCodePk, 0);

        $homeData = [
            'now' => [
                'used' => $homeNowData['current']['data'],
                'price' => $homeNowData['current']['price'],
            ],
            'last' => [
                'used' => $homeLastData['current']['data'],
                'price' => $homeLastData['current']['price'],
            ],
        ];

        $fcData = [
            'now_data' => $nows,
            'last_data' => $lasts,
            'target_data' => $targets,
            'home_data' => $homeData,
        ];

        return $fcData;
    }

    /**
     * 용도별 사용현황에서 사용분포도 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $floor
     * @param string $room
     * @param string $sensor
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getUsageData(string $complexCodePk, int $dateType, string $floor, string $room, string $sensor) :? array
    {
        $fcData = [];
        $addOptions = [];

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $energyPartData = $this->sensorObj->getEnergyPartData();
        if (count($energyPartData) === 0) {
            return null;
        }

        $separatedData = $this->sensorObj->getSpecialSensorKeyName();
        $usageData = $energyPartData['usage'];

        foreach ($usageData as $key => $values) {
            $option = (int)$values['option'];

            $addOptions = [
                'floor' => $floor,
                'room' => $room,
                'energy_name' => $key,
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        // 건물 전체 전력 조회
        $fcData['electric'] = $this->getBuildingElectricUsed($complexCodePk, $dateType, $floor, $room, $sensor);

        $fcData = $this->getUsageDistribution($fcData);
        return $fcData;
    }

    /**
     * 사용분포도 조회
     *
     * @param array $data
     *
     * @return array
     */
    private function getUsageDistribution(array $data) : array
    {
        $fcData = [];

        $indications = $this->indication;
        $buildingAllElectric = array_sum(array_values($data['electric']));

        foreach ($data as $key => $value) {
            if (in_array($key, Config::SPECIAL_ENERGY_DATA) === true) {
                continue;
            }

            $useDistribution = $indications->getUseDistribution($value, $data['electric']);

            $fcData[$key] = $useDistribution;
        }

        return $fcData;
    }

    /**
     * 설비별 사용현황에서 사용분포도 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $floor
     * @param string $room
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getFacilityData(string $complexCodePk, int $dateType, string $floor, string $room) :? array
    {
        $fcData = [];
        $addOptions = [];

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $energyPartData = $this->sensorObj->getEnergyPartData();
        if (count($energyPartData) === 0) {
            return null;
        }

        $separatedSensors = $this->sensorObj->getSpecialSensorKeyName();
        $facilityData = $energyPartData['facility'];
        foreach ($facilityData as $key => $values) {
            $option = (int)$values['option'];

            $addOptions = [
                'floor' => $floor,
                'room' => $room,
                'energy_name' => $key,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedSensors),
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        return $fcData;
    }

    /**
     * 태양광 정보 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getSolarData(string $complexCodePk, int $dateType) : array
    {
        $fcData = [];
        $solarIns = [];
        $solarOuts = [];
        $addOptions = [];

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);
        $option = 11;

        // 태양광 발전량
        $addOptions = [
            'energy_name' => 'solar',
            'solar_type' => 'I'
        ];
        $solarIns = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);

        // 태양광 소비량
        $addOptions['solar_type'] = 'O';
        $solarOuts = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);

        $fcData = [
            'in' => $solarIns['current']['data'],
            'out' => $solarOuts['current']['data'],
        ];

        return $fcData;
    }

    /**
     * 에너지 자립률 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getIndependenceData(string $complexCodePk) : array
    {
        $fcData = [];

        $indication = $this->indication;

        // 금일
        $lToday = $this->baseDateInfo['date'];

        // 전일
        $preDailys = $indication->getIndependencePercent($complexCodePk, 2, $lToday);

        // 금년
        $years = $indication->getIndependencePercent($complexCodePk, 0, $lToday);

        // 전월
        $months = $indication->getIndependencePercent($complexCodePk, 1, $lToday);

        // 전일 온실가스 배출량
        $co2Emission = $indication->getCo2Emission($complexCodePk, 2, $lToday);

        $fcData = [
            'daily' => [
                'independence_rate' => $preDailys[0],
                'production' => $preDailys[1],
                'consumption' => $preDailys[2],
                'co2_emission' => $co2Emission,
            ],
            'month' => [
                'independence_rate' => $months[0],
                'production' => $months[1],
                'consumption' => $months[2],
            ],
            'year' => [
                'independence_rate' => $years[0],
                'production' => $years[1],
                'consumption' => $years[2],
                'independence_grade' => $indication->getIndependenceGrade($years[0]),
            ]
        ];

        return $fcData;
    }

    /**
     * 미세먼지 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getFinedustData(string $complexCodePk) : array
    {
        $fcData = [];

        $today = date('Y-m-d', strtotime($this->baseDateInfo['date']));

        // 미세먼지, 초미세먼지, co2 조회
        $rFinedustQ = $this->emsQuery->getQueryFinedust($complexCodePk, $today);
        $finedustData = $this->query($rFinedustQ);

        // 환경부 미세먼지 가져오기
        $complexQuery = Utility::getInstance()->makeWhereClause('complex', 'complex_code_pk', $complexCodePk);
        $rAirStationQ = $this->emsQuery->getQueryAirStationByComplex($complexQuery);
        $airStationData = $this->query($rAirStationQ);

        $fcData = [
            'air_pm10' => $airStationData[0]['air_pm10'],
            'air_pm25' => $airStationData[0]['air_pm25'],
            'pm10' => $finedustData[0]['pm10'] === null ? 0 : $finedustData[0]['pm10'],
            'pm25' => $finedustData[0]['pm25'] === null ? 0 : $finedustData[0]['pm25'],
            'co2' => $finedustData[0]['co2'] === null ? 0 : $finedustData[0]['co2'],
        ];

        return $fcData;
    }

    /**
     * 용도별 분포도에  특정 항목 추가
     *
     * @param string $addItemKey
     * @param int $electricUsed
     * @param array $originItems
     * @param array $addItems
     *
     * @return array
     */
    private function addUsageItem(string $addItemKey, int $electricUsed, array $originItems, array $addItems) : array
    {
        $indications = $this->indication;
        $fcData = $originItems;

        if ($addItemKey === 'power_train'
            && (array_key_exists('feed_pump', $addItems) === true && array_key_exists('sump_pump', $addItems) === true)) {
            // 급수펌프,배수펌프 존재 할경우  동력 사용량 = 급수 + 배수
            $feedPump = array_sum(array_values($addItems['feed_pump']));
            $sumpPump = array_sum(array_values($addItems['sump_pump']));
            $usageSum = $feedPump + $sumpPump;

            $fcData['power_train'] = $indications->getUseDistribution($usageSum, $electricUsed);
        }

        return $fcData;
    }

    /**
     * 건물전체전력 조회 (전기 + 태양광 소비량)
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $floor
     * @param string $room
     * @param string $sensor
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getBuildingElectricUsed(string $complexCodePk, int $dateType, string $floor, string $room, string $sensor) : int
    {
        $usage = $this->usage;
        $electricBuildingExceptSolarInfo = Config::ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        // 전기 사용량
        $addOptions = [
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'energy_name' => 'electric',
        ];
        $electricData = $usage->getUsageSumData($this, $complexCodePk, 0, $dateType, $date, $addOptions);
        $electricUsed = $electricData['current']['data'];

        // 소비량 계산하기 위해 태양광 소비량 조회
        $solarKeys = $this->sensorObj->getSolarSensor();
        $addOptions = [
            'sensor' => $solarKeys['out'],
            'solar_type' => 'O',
            'energy_name' => 'solar',
        ];
        $d = $usage->getUsageSumData($this, $complexCodePk, 11, $dateType, $date, $addOptions);
        $solarOutUsed = $d['current']['data'];

        if (in_array($complexCodePk, $electricBuildingExceptSolarInfo) === false) {
            $electricUsed += $solarOutUsed;
        }

        return $electricUsed;
    }
}