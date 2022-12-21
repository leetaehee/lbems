<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Indication;
use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class DashboardFloor
 */
class DashboardFloor extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /** @var Indication|null $indication 자립률 */
    private ?Indication $indication = null;

    /** @var array $energyType 에너지 타입 */
    private array $energyType = Config::SENSOR_TYPES;

    /** @var array $floors 층 타입 */
    private array $floors = [];

    /**
     * DashboardFloor constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage;
        $this->indication = new Indication($this);
    }

    /**
     * DashboardFloor Destructor.
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

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);

        $date = $this->baseDateInfo['date'];
        $dateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        $floorType = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : 'all';
        $roomType = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 'all';
        $predictDateType = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : 2;
        $predictLoading = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : false;
        $dongType = 'all';

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        if (is_bool($predictLoading) === true && $predictLoading === true) {
            // 예측 데이터 조회
            $predictData = $this->getPredictUsedData($complexCodePk, $date, $predictDateType, $dongType, $floorType, $roomType);

            // 뷰에 보여줄 데이터
            $data = [
                'predict_data' => $predictData,
            ];

        } else {
            // 에너지원 데이터 조회
            $energyData = $this->getEnergyData($complexCodePk, $dateType, $dongType, $floorType, $roomType);
            // 용도별 사용분포도 조회
            $usageData = $this->getUsageData($complexCodePk, $dateType, $dongType, $floorType, $roomType);
            // 예측 데이터 조회
            $predictData = $this->getPredictUsedData($complexCodePk, $date, $predictDateType, $dongType, $floorType, $roomType);
            // 단위 면적당 사용현황 조회
            $areaData = $this->getEnergyByAreaData($complexCodePk, $dateType, $dongType, $floorType, $roomType);
            // 층별 사용량 조회
            $floorData = $this->getFloorData($complexCodePk, $dateType, $dongType);

            // 뷰에 보여줄 데이터
            $data = [
                'energy_data' => $energyData,
                'usage_data' => $usageData,
                'predict_data' => $predictData,
                'area_data' => $areaData,
                'floor_data' => $floorData,
            ];
        }

        $this->data = $data;
        return true;
    }

    /**
     * 에너지원 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     * @param string $floor
     * @param string $room
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEnergyData(string $complexCodePk, int $dateType, string $dong, string $floor, string $room) : array
    {
        $fcData = [];
        $nows = [];
        $lasts = [];
        $targets = [];
        $homeData = [];

        $usage = $this->usage;
        $energyKeys = $this->energyType;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $option = 0;
        $key = $energyKeys[$option];

        // 건물 위치 센서 조회
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        $solarType = '';

        $addOptions = [
            'dong' => $dong,
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
     * @param string $dong
     * @param string $floor
     * @param string $room
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getUsageData(string $complexCodePk, int $dateType, string $dong, string $floor, string $room) :? array
    {
        $fcData = [];
        //$keySensors = [];
        //$keySensor = [];
        $addOptions = [];

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $energyPartData = $this->sensorObj->getEnergyPartData();
        if (count($energyPartData) === 0) {
            return null;
        }

        $separatedData = $this->sensorObj->getSpecialSensorKeyName();
        $energyData = $energyPartData['usage'];

        foreach ($energyData as $key => $values) {
            $option = (int)$values['option'];
            //if (is_null($keySensors[$key]) === false) {
            //    $keySensor = $keySensors[$key];
            //}

            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'energy_name' => $key,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData),
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];

            /*
            if (count($keySensors) > 0 && count($keySensor) > 0) {
                $d = $usage->getEnergyHomeDataBySensor($this, $complexCodePk, $option, $dateType, $date, $addOptions, $keySensor);
            } else {
                $temps = $usage->getEnergyDataByHome($this, $complexCodePk, $option, $dateType, $date, $addOptions);
                $d = $temps['current'];
            }

            if (count($d) > 0) {
                $fcData[$key] = $d['data'];
            }
            */
        }

        // 건물 위치 센서 조회
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

        // 건물 전체 전력 조회
        $fcData['electric'] = $this->getBuildingElectricUsed($complexCodePk, $dateType, $dong, $floor, $room, $sensor);

        // 용도별 분포도 조회
        $fcData = $this->getUsedAndDistribution($fcData);

        return $fcData;
    }

    /**
     * 사용량과 사용분포도 조회
     *
     * @param array $data
     *
     * @return array
     */
    private function getUsedAndDistribution(array $data) : array
    {
        $fcData = [];
        $indication = $this->indication;

        $electricUsed = $data['electric'];

        foreach ($data as $key => $value) {
            if (in_array($key, Config::SPECIAL_ENERGY_DATA) === true) {
                continue;
            }
            $useDistribution = $indication->getUseDistribution($value, $electricUsed);

            $fcData['usage'][$key] = $value;
            $fcData['distribution'][$key] = $useDistribution;
        }

        return $fcData;
    }

    /**
     * 예측에서 보여질 데이터 조회 (금일/금주/금월 현재와 예상사용량 조회)
     *
     * @param string $complexCodePk
     * @param string $date
     * @param int $dateType
     * @param string $dong
     * @param string $floor
     * @param string $room
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getPredictUsedData(string $complexCodePk, string $date, int $dateType, string $dong, string $floor, string $room) : array
    {
        $fcData = [];
        $addOptions = [];

        $option = 0; // 전기에 대해서 보여줌.

        $periodData = $this->getPeriod($option, $complexCodePk);
        $periodMessage = $periodData[$dateType]['start'] . '~' . $periodData[$dateType]['end'];

        $usage = $this->usage;

        $date = $usage->getDateByOption($date, $dateType);

        // 건물 위치 센서 조회
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'energy_name' => 'electric',
        ];
        $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);

        $fcData = [
            'current' => $d['current']['data'],
            'predict' => $d['predict']['data'],
            'period_message' => $periodMessage,
        ];

        return $fcData;
    }

    /**
     * 단위면적당 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     * @param string $floor
     * @param string $room
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEnergyByAreaData(string $complexCodePk, int $dateType, string $dong, string $floor, string $room) : array
    {
        $fcData = [];
        $nows = [];
        $lasts = [];
        $addOptions = [];

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);
        $option = 0;
        $energyName = Config::SENSOR_TYPES[0];

        // 건물 위치 센서 조회
        // 층에 대한 건물면적이 존재하지 않으므로 전체로 함.. 변경이 될 경우 위에 값으로 대체 할 것..
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, 'all', $room);

        /*
         * 건물 단위 면적당 사용현황은 시간별, 일, 월로 조회하여 평균을 내야 하기 때문에 getEnergyData 함수를 사용함
         * - 층별로 검색 하려면 층에 대한 건축면적 정보가 필요함.
         * - 현재는 건물 전체에 대해서만 bems_complex 테이블에서 존재함.
         * - 층까지 검색을 허용 할 경우 $addOptions에 $floor로 수정 할 것
         */

        $dailyIsArea = $this->getIsDaily($dateType);

        $addOptions = [
            'dong' => $dong,
            'floor' => 'all',
            'room' => $room,
            'sensor' => $sensor,
            'energy_name' => $energyName, // 전기에 대해서 함.
            'is_area' => $dailyIsArea
        ];

        $nows = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
        if ($dailyIsArea === false) {
            // 금일 인 경우 kwh -> wh로 변환해야 함 (캐시가 wh가 아니라 아니기 때문..)
            $nows = $usage->setAreaValue($this, $complexCodePk, $option, $energyName, $dateType, $date, $nows, true);
        }

        // 이전은 캐시 할 필요 없음
        $addOptions['is_area'] = true;
        $tempDate = $usage->getLastDate($date, $dateType);
        $lasts = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $tempDate, $addOptions);

        $fcData = [
            'now' => $nows,
            'last' => $lasts,
        ];

        return $fcData;
    }

    /**
     * 주기별 기간 조회
     *
     * @param int $option
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getPeriod(int $option, string $complexCodePk) : array
    {
        $date = $this->baseDateInfo['date'];
        $time = strtotime($date);

        $usage = $this->usage;
        $lastMonth = date('Ym', strtotime($date . '-1 month'));

        // 금일
        $dailyStartDate = $dailyEndDate = date('Y.m.d', strtotime($date));

        // 금주
        $dayOfTheWeek = date('w', $date);
        if ($dayOfTheWeek === '0') {
            // 현재가 일요일인 경우 다음날로 +1 변경한다.
            $time = strtotime($date . '+1 day');
        }
        $weeklyStartDate = date('Y.m.d', strtotime('LAST SUNDAY', $time));
        $weeklyEndDate = date('Y.m.d', strtotime('SATURDAY', $time));

        // 금월
        $monthPeriods = $usage->getDueDatePeriodByMonth($this, $complexCodePk, $option, $lastMonth, 'Y.m.d');
        $monthStartDate = $monthPeriods['start_date'];
        $monthEndDate = $monthPeriods['end_date'];

        return [
            2 => [
                'start' => $dailyStartDate,
                'end' => $dailyEndDate
            ],
            5 => [
                'start' => $weeklyStartDate,
                'end' => $weeklyEndDate
            ],
            1 => [
                'start' => $monthStartDate,
                'end' => $monthEndDate
            ]
        ];
    }

    /**
     * 층/룸별 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getFloorData(string $complexCodePk, int $dateType, string $dong) : array
    {
        $fcData = [];
        $addOptions = [];

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);
        $option = 0;

        $this->floors = $this->sensorObj->getFloorInfo();
        $floors = $this->floors;

        for ($i = 0; $i < count($floors); $i++) {
            $room = 'all';
            $floor = $floors[$i];

            // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'sensor' => $sensor,
                'energy_name' => 'electric'
            ];
            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$floor] = $d['current']['data'];
        }

        return $fcData;
    }

    /**
     * 금일인지 확인 하여 금일인 경우 후속작업을 위해 단위면적 여부를 false 반환
     *
     * @param int $dateType
     *
     * @return bool
     */
    private function getIsDaily(int $dateType) : bool
    {
        return $dateType === 2 ? false : true;
    }

    /**
     * 건물전체전력 조회 (전기 + 태양광 소비량)
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $sensor
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getBuildingElectricUsed(string $complexCodePk, int $dateType, string $dong, string $floor, string $room, string $sensor) : int
    {
        $usage = $this->usage;
        $electricBuildingExceptSolarInfo = Config::ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        // 전기 사용량
        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'energy_name' => 'electric',
        ];
        $electricData = $usage->getUsageSumData($this, $complexCodePk, 0, $dateType, $date, $addOptions);
        $electricUsed = $electricData['current']['data'];

        $solarOutUsed = 0;
        if ($floor === 'all') {
            // 소비량 계산하기 위해 태양광 소비량 조회
            $solarKeys = $this->sensorObj->getSolarSensor();
            $addOptions = [
                'sensor' => $solarKeys['out'],
                'solar_type' => 'O',
                'energy_name' => 'solar'
            ];
            $d = $usage->getUsageSumData($this, $complexCodePk, 11, $dateType, $date, $addOptions);
            $solarOutUsed = $d['current']['data'];
        }

        if (in_array($complexCodePk, $electricBuildingExceptSolarInfo) === false) {
            $electricUsed += $solarOutUsed;
        }

        return $electricUsed;
    }
}