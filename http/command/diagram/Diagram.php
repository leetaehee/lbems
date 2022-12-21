<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Config;
use EMS_Module\Indication;
use EMS_Module\Utility;

/**
 * Class Diagram 계통도 (전기, 태양광, 용도별 내용만 포함하는 기본 내용)
 */
class Diagram extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /** @var Indication|null $indication 자립률 */
    private ?Indication $indication = null;

    /** @var array $energyType 에너지 타입 */
    private array $energyType = Config::SENSOR_TYPES;

    /** @var array $floors 층별 타입 */
    private array $floors = [];

    /** @var array $separatedData 독립적으로 조회되는 에너지원 */
    private array $separatedData = [];

    /**
     * Diagram constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage;
        $this->indication = new Indication($this);
    }

    /**
     * Diagram Destructor.
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
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $ss_complex_pk = $_SESSION['ss_complex_pk'];
        $complexCodePk = $this->getSettingComplexCodePk($ss_complex_pk);

        $data = [];

        $dateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        $floor = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : 'all';
        $dong = 'all';

        $this->sensorObj = $this->getSensorManager($complexCodePk);
        $this->floors = $this->sensorObj->getFloorInfo();
        $this->separatedData = $this->sensorObj->getSpecialSensorKeyName();

        // 사용량
        $usageData = $this->getUsageData($complexCodePk, $dateType, $dong, $floor);
        if (is_null($usageData) === true) {
            $data['Error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 사용분포도 조회
        $distributionData = $this->getUsageDistribution($complexCodePk, $usageData);

        // 층별 사용량 조회
        $floorData = $this->getUsageFloorData($complexCodePk, $dateType, $dong);

        // 에너지 자립률 및 등급 조회- 금년만 표시
        $independenceData = $this->getIndependenceRate($complexCodePk, 0);

        if (array_key_exists('solar_out', $usageData) === true) {
            // solar_out 키 삭제
            unset($usageData['solar_out']);
        }

        // 뷰에 보여지고자 하는 데이터 배열에 담기
        $data = [
            'usage_data' => $usageData,
            'distribution_data' => $distributionData,
            'floor_data' => $floorData,
            'independence_data' => $independenceData,
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 에너지원별 사용량 구하기
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     * @param string $floor
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getUsageData(string $complexCodePk, int $dateType, string $dong, string $floor) :? array
    {
        $fcData = [];
        $addOptions = [];

        $energyKeys = $this->energyType;
        $separatedData = $this->separatedData;

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $room = 'all';

        $energyPartData = $this->sensorObj->getEnergyPartData();
        if (count($energyPartData) === 0) {
            return null;
        }

        // 에너지원별
        $energyData = $energyPartData['energy'];
        foreach ($energyData as $key => $values) {
            $option = (int)$values['option'];

            $electricSensor = '';

            if ($option === 0) {
                // 건물 위치 센서 조회
                $electricSensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
            }

            $addOptions = [
                'energy_name' => $key,
                'sensor' => $electricSensor,
                'floor' => $floor,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData),
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        // 용도별
        $usageData = $energyPartData['usage'];
        foreach ($usageData as $key => $values) {
            $option = (int)$values['option'];

            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'energy_name' => $key,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData),
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        // 태양광 발전량 조회
        $solarKeys = $this->sensorObj->getSolarSensor();

        $addOptions = [
            'sensor' => $solarKeys['in'],
            'solar_type' => 'I',
        ];
        $d = $usage->getUsageSumData($this, $complexCodePk, 11, $dateType, $date, $addOptions);
        $fcData['solar'] = $d['current']['data'];

        $addOptions = [
            'solar_type' => 'O',
            'sensor' => $solarKeys['out'],
        ];

        $d = $usage->getUsageSumData($this, $complexCodePk, 11, $dateType, $date, $addOptions);
        $fcData['solar_out'] = $d['current']['data'];

        return $fcData;
    }

    /**
     * 사용분포도 조회
     *
     * @param string $complexCodePk
     * @param array $data
     *
     * @return array
     */
    private function getUsageDistribution(string $complexCodePk, array $data) : array
    {
        $fcData = [];

        $indication = $this->indication;
        $electricBuildingExceptSolarInfo = Config::ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO;

        $buildingAllElectric = $data['electric'];

        if (in_array($complexCodePk, $electricBuildingExceptSolarInfo) === false) {
            $buildingAllElectric += $data['solar_out'];
        }

        foreach ($data as $key => $value) {
            if ($key === 'electric' || $key === 'solar_out') {
                continue;
            }

            $useDistribution = $indication->getUseDistribution($value, $buildingAllElectric);
            $fcData[$key] = $useDistribution;
        }


        return $fcData;
    }

    /**
     * 층별 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getUsageFloorData(string $complexCodePk, int $dateType, string $dong) : array
    {
        $fcData = [];
        $addOptions = [];
        $floors = $this->floors;

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);
        $room = 'all';

        // 전체 추가
        array_push($floors, 'all');

        $option = 0;
        for ($i = 0; $i < count($floors); $i++) {
            $floor = $floors[$i];

            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

            $addOptions = [
                'floor' => $floor,
                'sensor' => $sensor,
            ];
            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$floor] = $d['current']['data'];
        }

        return $fcData;
    }

    /**
     * 에너지 자립률 및 등급 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getIndependenceRate(string $complexCodePk, int $dateType) : array
    {
        $fcData = [];

        $independences = [];

        $usage = $this->usage;
        $indication = $this->indication;

        $date = $this->baseDateInfo['date'];

        switch ($dateType)
        {
            case 0:
                // 금년
                $independences = $indication->getIndependencePercent($complexCodePk, 0, $date);
                break;
            case 1:
                // 금월
                $date = $usage->getLastDate($date, $dateType);
                $independences = $indication->getIndependencePercent($complexCodePk, 1, $date);
                break;
            case 2:
                // 금일
                $date = $usage->getLastDate($date, $dateType);
                $independences = $indication->getIndependencePercent($complexCodePk, 2, $date);
                break;
        }

        $independenceRate = $independences[0];

        $fcData = [
            'independence_rate' => number_format($independenceRate),
            'grade' => $indication->getIndependenceGrade($independenceRate),
        ];

        return $fcData;
    }
}