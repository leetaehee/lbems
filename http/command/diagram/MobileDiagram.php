<?php
namespace Http\Command;

use EMS_Module\Indication;
use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class MobileDiagram
 */
class MobileDiagram extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /** @var Indication|null $indication 자립률 */
    private ?Indication $indication = null;

    /** @var array $separatedData  독립적으로 조회되는 에너지원 */
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
     * Diagram destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $sessionComplexPk = $this->getSettingComplexCodePk($_SESSION['mb_ss_complex_pk']);
        $this->sensorObj = $this->getSensorManager($sessionComplexPk);
        $this->separatedData = $this->sensorObj->getSpecialSensorKeyName();

        $data = [];
        $dateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 2;
        $dong = 'all';

        // 자립률 정보 조회 (년간)
        $independenceData = $this->getIndependenceByPeriod($sessionComplexPk, 0);

        // 계통도 데이터 조회
        $diagramData = $this->getDiagramData($sessionComplexPk, $dateType, $dong);

        // 뷰에 데이터 바인딩
        $this->data = [
            'independence_data' => $independenceData,
            'diagram_data' => $diagramData,
        ];
        return true;
    }

    /**
     * 자립률, 등급 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getIndependenceByPeriod(string $complexCodePk, int $dateType) : array
    {
        $fcData = $temps = [];

        // 금일
        $today = $this->baseDateInfo['date'];

        $indication = $this->indication;
        $usage = $this->usage;

        switch ($dateType)
        {
            case 0:
                $temps = $indication->getIndependencePercent($complexCodePk, $dateType, $today);
                break;
            case 1:
                // 전월
                $temps = $indication->getIndependencePercent($complexCodePk,$dateType, $today);
                break;
            case 2:
                // 전일
                $temps = $indication->getIndependencePercent($complexCodePk, $dateType, $today);
                break;
        }

        $fcData = [
            'rate' => $temps[0],
            'grade' => $indication->getIndependenceGrade($temps[0]),
        ];

        return $fcData;
    }

    /**
     * 계통도 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $dong
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getDiagramData(string $complexCodePk, int $dateType, string $dong) :?array
    {
        $fcData = [];
        $keySensors = [];
        $keySensor = [];
        $addOptions = [];

        $floor = 'all';
        $room = 'all';
        $sensor = '';

        $usage = $this->usage;

        $date = $this->baseDateInfo['date'];
        $date = $usage->getDateByOption($date, $dateType);

        $energyPartData = $this->sensorObj->getEnergyPartData();
        if (count($energyPartData) === 0) {
            return null;
        }

        $energyData = $energyPartData['energy'];
        $usageData = $energyPartData['usage'];
        $facilityData = is_null($energyPartData['facility']) === true ? [] : $energyPartData['facility'];
        $separatedData = $this->sensorObj->getSpecialSensorKeyName();

        foreach ($energyData as $key => $values) {
            $option = (int)$values['option'];

            $electricSensor = '';

            if ($option === 0) {
                // 건물 위치 센서 조회
                $electricSensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
            }

            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'sensor' => $electricSensor,
                'energy_name' => $key,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData)
            ];
            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        foreach ($usageData as $key => $values) {
            if ($key === 'power_train') {
                // 동력(펌프)로 하기 때문에 넣지 않음.
                continue;
            }

            $option = (int)$values['option'];

            $addOptions = [
                'dong' => $dong,
                'sensor' => $sensor,
                'energy_name' => $key,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData),
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$key] = $d['current']['data'];
        }

        // 동력(펌프)가 있는 경우 해당 설비 정보 추출
        $selectedItems = ['feed_pump', 'sump_pump'];
        $fcData = $this->getSelectedItemsSum($complexCodePk, $dateType, $date, 'power_train', $fcData, $facilityData, $separatedData, $selectedItems);

        // 태양광 발전량
        $solarKeys = $this->sensorObj->getSolarSensor();
        $solarInSensor = '';
        if (empty($solarKeys['in']) === false) {
            $solarInSensor = $solarKeys['in'];
        }
        $addOptions = [
            'sensor' => $solarInSensor,
            'solar_type' => 'I',
            'energy_name' => 'solar',
        ];
        $d = $usage->getUsageSumData($this, $complexCodePk, 11, $dateType, $date, $addOptions);
        $fcData['solar'] = $d['current']['data'];

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