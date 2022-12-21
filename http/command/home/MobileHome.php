<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Indication;
use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class MobileHome
 */
class MobileHome extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /** @var Indication|null $indication 자립률 */
    private ?Indication $indication = null;

    /**
     * Home constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage= new Usage;
        $this->indication = new Indication($this);
    }

    /**
     * Home destructor.
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
        $data = [];

        $sessionComplexPk = $this->getSettingComplexCodePk($_SESSION['mb_ss_complex_pk']);
        $this->sensorObj = $this->getSensorManager($sessionComplexPk);

        // 소비량, 생산량 자립률 정보 조회
        $independenceData = $this->getIndependenceByPeriod($sessionComplexPk);

        // 에너지 사용현황
        $energyData = $this->getEnergyData($sessionComplexPk);

        // 용도별 사용현황
        $usageData = $this->getUsageData($sessionComplexPk);

        // 뷰에 데이터 바인딩
        $this->data = [
            'energy_data' => $energyData,
            'usage_data' => $usageData,
            'independence_data' => $independenceData,
        ];

        return true;
    }

    /**
     * 전일/금월/금년 소비량, 생산량 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getIndependenceByPeriod(string $complexCodePk) : array
    {
        $indication = $this->indication;
        $usage = $this->usage;

        // 금일
        $today = $this->baseDateInfo['date'];

        // 전일
        $preDailys = $indication->getIndependencePercent($complexCodePk, 2, $today);

        // 금년
        $years = $indication->getIndependencePercent($complexCodePk, 0, $today);

        // 전월
        $months = $indication->getIndependencePercent($complexCodePk, 1, $today);

        return [
            'daily' => [
                'rate' => $preDailys[0],
                'grade' => $indication->getIndependenceGrade($preDailys[0]),
                'production' => $preDailys[1] > 0 ? floor($preDailys[1]) : 0,
                'consumption' => $preDailys[2] > 0 ? floor($preDailys[2]) : 0,
            ],
            'month' => [
                'rate' => $months[0],
                'grade' => $indication->getIndependenceGrade($months[0]),
                'production' => $months[1] > 0 ? floor($months[1]) : 0,
                'consumption' => $months[2] > 0 ? floor($months[2]) : 0,
            ],
            'year' => [
                'rate' => $years[0],
                'grade' => $indication->getIndependenceGrade($years[0]),
                'production' => $years[1] > 0 ? floor($years[1]) : 0,
                'consumption' => $years[2] > 0 ? floor($years[2]) : 0,
            ]
        ];
    }

    /**
     * 주기에 대한 에너지 사용현황 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEnergyData(string $complexCodePk) : array
    {
        $fcData = [];
        $nows = [];
        $lasts = [];
        $targets = [];
        $addOptions = [];

        $floor = 'all';
        $room = 'all';
        $dong = 'all';
        $sensor = '';

        $usage = $this->usage;
        $sensorTypes = Config::SENSOR_TYPES;

        // 건물 위치 센서 조회
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

        $date = $this->baseDateInfo['date'];
        $dateType = (int)1; // 금일, 금월, 금년으로 할 경우 함수 파라미터로 값 전달받을 것
        $date = $usage->getDateByOption($date, $dateType);

        $option = 0;
        $sensorType = $sensorTypes[$option];

        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $sensorType,
            'sensor' => $sensor,
        ];

        // 사용량|요금 조회
        $nows = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
        $tempDate = $usage->getLastDate($date, 1);
        $lasts = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $tempDate, $addOptions);

        // 기준값 조회
        $target = $usage->getReference($this, $complexCodePk, $option);

        // 데이터 반환
        $fcData[$sensorType] = [
            'nows' => $nows['current'],
            'lasts' => $lasts['current'],
            'target' => $this->getTargetUsedByDateType($dateType, $target),
        ];

        return $fcData;
    }

    /**
     * 주기에 따른 기준값 분리
     *
     * @param int $dateType
     * @param string $data
     *
     * @return string $target
     */
    private function getTargetUsedByDateType(int $dateType, string $data) : string
    {
        $target = '';
        $targetExplodes = explode('/', $data);

        switch ($dateType)
        {
            case 0:
                // 년
                $target = $targetExplodes[3];
                break;
            case 1:
                // 월
                $target = $targetExplodes[2];
                break;
            case 2:
                // 일
                $target = $targetExplodes[1];
                break;
            case 3:
                // 시
                $target = $targetExplodes[0];
                break;
        }

        return $target;
    }

    /**
     * 주기에 대한 용도별 사용현황 조회
     *
     * @param string $complexCodePk
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getUsageData(string $complexCodePk) :?array
    {
        $fcData = [];
        $aedOptions = [];

        $floor = 'all';
        $room = 'all';
        $dong = 'all';
        $sensor = '';

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $dateType = 2; // 금일, 금월, 금년  선택할 경우 함수 파라미터로 전달 받을 것
        $date = $usage->getDateByOption($date, $dateType);

        $energyPartData = $this->sensorObj->getEnergyPartData();
        if (count($energyPartData) === 0) {
            return null;
        }

        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
        ];

        $usageData = $energyPartData['usage'];
        $facilityData = is_null($energyPartData['facility']) === true ? [] : $energyPartData['facility'];
        $separatedData = $this->sensorObj->getSpecialSensorKeyName();

        foreach ($usageData as $key => $values) {
            if (in_array($key, Config::SPECIAL_ENERGY_DATA) === true) {
                continue;
            }

            $option = (int)$values['option'];

            $addOptions['energy_name'] = $key;
            $addOptions['separated_sensors'] = Utility::getInstance()->arrayKeyCheckResult($key, $separatedData);

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        $addOptions['energy_name'] = '';

        if (array_key_exists('power_train', $usageData) === true) {
            // 동력(펌프)가 있는 경우 해당 설비 정보 추출
            $selectedItems = ['feed_pump', 'sump_pump'];
            $fcData = $this->getSelectedItemsSum($complexCodePk, $dateType, $date, 'power_train', $fcData, $facilityData, $separatedData, $selectedItems);
        }

        // 건물 위치 센서 조회
        $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

        $addOptions['sensor'] = $sensor;
        $electricData = $usage->getUsageSumData($this, $complexCodePk, 0, $dateType, $date, $addOptions);
        $fcData['electric'] = $electricData['current']['data'];

        // 소비량 계산하기 위해 태양광 소비량 조회
        $solarKeys = $this->sensorObj->getSolarSensor();

        $addOptions = [
            'energy_name' => 'solar',
            'sensor' => $solarKeys['out'],
            'solar_type' => 'O',
        ];
        $d = $usage->getUsageSumData($this, $complexCodePk, 11, $dateType, $date, $addOptions);
        $fcData['solar_out'] = $d['current']['data'];

        // 사용분포도 조회
        $fcData = $this->getUsedAndDistribution($complexCodePk, $fcData);

        return $fcData;
    }

    /**
     * 사용량 배열 값을 모두 sum
     *
     * @param array $used
     *
     * @return float $useSum
     */
    private function getUsedArraySum(array $used) : float
    {
        $useSum = 0;

        $values = array_values($used);
        if (count($values) > 0) {
            $useSum = array_sum($values);
        }

        return $useSum;
    }

    /**
     * 사용량과 사용분포도 조회
     *
     * @param string $complexCodePk
     * @param array $data
     *
     * @return array $fcData
     */
    private function getUsedAndDistribution(string $complexCodePk, array $data) : array
    {
        $fcData = [];

        $indication = $this->indication;
        $electricBuildingExceptSolarInfo = Config::ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO;

        $buildingElectric = $data['electric'];
        if (in_array($complexCodePk, $electricBuildingExceptSolarInfo) === false) {
            $buildingElectric += $data['solar_out'];
        }

        foreach ($data as $key => $value) {
            if ($key === 'electric' || $key === 'solar_out') {
                continue;
            }
            $useDistribution = $indication->getUseDistribution($value, $buildingElectric);

            $fcData['usage'][$key] = $value;
            $fcData['distribution'][$key] = $useDistribution;
        }

        return $fcData;
    }

    /**
     * 설비별 데이터 중에서 해당 되는 데이터를 조회 후  기존데이터에 추가
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $date
     * @param string $itemKey
     * @param array $originItems
     * @param array $facilityData
     * @param array $specialSensors
     * @param array $selectedItems
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getSelectedItemsSum(string $complexCodePk, int $dateType, string $date, string $itemKey, array $originItems, array $facilityData, array $specialSensors, array $selectedItems = []) : array
    {
        $usage = $this->usage;

        $fcOriginItems = $originItems;
        $fcData = [];
        $fcAddOptions = [];
        $fcValue = 0;

        if (count($selectedItems) === 0) {
            return $fcOriginItems;
        }

        foreach ($facilityData as $key => $values) {
            if (in_array($key, $selectedItems) === false) {
                continue;
            }

            $option = (int)$values['option'];

            $fcAddOptions = [
                'energy_name' => $key,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $specialSensors),
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $fcAddOptions);
            $fcData[$key] = $d['current']['data'];
        }


        if (count($fcData) > 0) {
            $fcValue = array_sum($fcData);
        }

        $fcOriginItems[$itemKey] = $fcValue;

        return $fcOriginItems;
    }
}