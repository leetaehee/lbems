<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class SetInfo
 */
class SetInfo extends Command
{
    /**
     * SetInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SetInfo destructor.
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

        // 센서정보 초기화
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 기본정보 조회
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
        // 단지정보 조회
        $rComplexDataQ = $this->emsQuery->getComplexData($complexCodePk);
        $rComplexDataRslt = $this->query($rComplexDataQ);

        // 단지정보의 이메일 조회
        $rComplexEmailQ = $this->emsQuery->getComplexEmail($complexCodePk);
        $rComplexEmailRslt = $this->query($rComplexEmailQ);

        $sensorTypes = Config::SENSOR_TYPES;

        // 무등산에서만 쓰이는 센서 타입 임시로 추가
        $sensorTypes = $this->updateSpecialSensor($sensorTypes);

        // 복호화
        $rComplexDataRslt[0]['name'] = $this->getTransData($rComplexDataRslt[0]['name']);
        $rComplexDataRslt[0]['addr'] = $this->getTransData($rComplexDataRslt[0]['addr']);
        $rComplexDataRslt[0]['tel'] = $this->getTransData($rComplexDataRslt[0]['tel']);
        $rComplexDataRslt[0]['fax'] = $this->getTransData($rComplexDataRslt[0]['fax']);
        $rComplexEmailRslt[0]['email'] = $this->getTransData($rComplexEmailRslt[0]['email']);

        return [
            'data'=> $rComplexDataRslt[0],
            'admin'=> $rComplexEmailRslt[0],
            'types' => $sensorTypes,
        ];
    }

    /**
     * 업체마다 추가 되는 설비명 임시적으로 추가
     *
     * @param array $sensorTypes
     *
     * @return array
     */
    private function updateSpecialSensor(array $sensorTypes) : array
    {
        $fcData = $sensorTypes;

        // 설비 항목 추출
        $specialFacilityData = $this->sensorObj->getSpecialSensorKeyName();
        $keys = array_keys($specialFacilityData);

        for ($fcIndex = 0; $fcIndex < count($keys); $fcIndex++) {
            array_push($fcData, $keys[$fcIndex]);
        }

        return $fcData;
    }

    /**
     * 암호화 된 데이터를 복호화 시킴
     *
     * @param string $value
     *
     * @return string
     */
    private function getTransData(string $value) : string
    {
        return Utility::getInstance()->updateDecryption($value);
    }

}