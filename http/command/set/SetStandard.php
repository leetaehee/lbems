<?php
namespace Http\Command;

use EMS_Module\Config;

/**
 * Class SetStandard
 */
class SetStandard extends Command
{
    /**
     * SetStandard constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SetStandard destructor.
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
        $complexCodePk = $_SESSION['ss_complex_pk'];
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 센서타입 추가
        $complexData = $this->getComplexBySetInfo($complexCodePk);

        $data['complex_data'] = $complexData;

        $this->data = $data;

        return true;
    }

    /**
     * 설정 > 정보관리에 필요한  building 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getComplexBySetInfo(string $complexCodePk) : array
    {
        $sensorTypes = Config::SENSOR_TYPES;

        // 단지정보 조회
        $rComplexDataQ = $this->emsQuery->getComplexData($complexCodePk);
        $rComplexDataRslt = $this->query($rComplexDataQ);

        // 업체에서 별도로 쓰이는 센서 타입 추가
        $data = $this->updateSpecialSensor($sensorTypes);
        $sensorTypes = $data['sensor_types'];
        $specialTypes = $data['special_types'];

        return [
            'data'=> $rComplexDataRslt[0],
            'types' => $sensorTypes,
            'specials' => $specialTypes,
        ];
    }

    /**
     * 업체 전용 센서 추가
     *
     * @param array $sensorTypes
     *
     * @return mixed|array $fcData
     */
    private function updateSpecialSensor(array $sensorTypes) : array
    {
        $fcData = [];

        // 설비 항목 추출
        $specialFacilityData = $this->sensorObj->getSpecialSensorKeyName();
        $keys = array_keys($specialFacilityData);

        for ($fcIndex = 0; $fcIndex < count($keys); $fcIndex++) {
            array_push($sensorTypes, $keys[$fcIndex]);
        }

        // 업체 특화 기능에 대한 정보 정의
        $specialTypes = $this->getFacilityInfoData($keys, $sensorTypes);

        $fcData = [
            'sensor_types' => $sensorTypes,
            'special_types' => $specialTypes,
        ];

        return $fcData;
    }

    /**
     * 업체 전용 설비에 대한 타입 재정의
     *
     * @param array $keys
     * @param array $sensorTypes
     *
     * @return array
     */
    private function getFacilityInfoData(array $keys, array $sensorTypes) : array
    {
        $fcData = [];

        $energyData = $this->sensorObj->getEnergyPartData();

        for ($fcIndex = 0; $fcIndex < count($keys); $fcIndex++) {
            $key = $keys[$fcIndex];
            $option = '';

            if (isset($energyData['energy'][$key])) {
                // 에너지원별
                $option = $energyData['energy'][$key]['option'];
            }

            if (isset($energyData['usage'][$key])) {
               // 용도별
               $option = $energyData['usage'][$key]['option'];
            }

            if (isset($energyData['facility'][$key])) {
                // 설비별
                $option = $energyData['facility'][$key]['option'];
            }

            $fcData[$key] = [
                'sensor_type' => $sensorTypes[$option],
                'option' => $option,
            ];
        }

        return $fcData;
    }

}