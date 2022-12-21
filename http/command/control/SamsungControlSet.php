<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class SamsungControlSet
 */
class SamsungControlSet extends Command
{
    /** @var array $devices 디바이스 정보  */
    private array $devices = [];

    /** @var array $operations 기능 수행 정보 */
    private array $operations = Config::CONTROL_AIR_CONDITION_COMMAND['samsung']['operation'];

    /** @var array $fans 풍량 정보 */
    private array $fans = Config::CONTROL_AIR_CONDITION_COMMAND['samsung']['fan'];

    /** @var array $modes 모드 정보  */
    private array $modes = Config::CONTROL_AIR_CONDITION_COMMAND['samsung']['mode_k'];

    /**
     * SamsungControlSet constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SamsungControlSet destructor.
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
        $result = [];

        $devOptions = $this->devOptions;
        if ($devOptions['IS_DEV'] == '1') {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $currentFloor = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $roomName = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $status = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : '';
        $powerOnOff = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : false;
        $operation = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : '';
        $isReady = isset($params[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']) : false;

        $this->sensorObj = $this->getSensorManager($complexCodePk); // 정보조회를 위한 객체 값 할당.
        $this->setDevice($currentFloor); // 디바이스 정보 추출

        // 제어 사용 중단 된경우..
        if ($isReady === false) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $result = $this->setDeviceStatus($complexCodePk, $roomName, $operation, $powerOnOff, $status);

        if ($result === null) {
            return null;
        }

        $data['result'] = $result;

        // 뷰에 데이터 바인딩
        $this->data = $data;
        return true;
    }

    /**
     * 건물 고유 디바이스별 할당
     *
     * @param string $floor
     */
    private function setDevice(string $floor) : void
    {
        $fcData = [];

        $devices = $this->sensorObj->getControlDeviceInfo();

        $tempDevices = $devices[$floor];

        foreach ($devices as $key => $items) {
            foreach ($items as $k => $v) {
                $fcData[$k] = $v;
            }
        }

        $this->devices = $fcData;
    }

    /**
     * 상태 제어
     *
     * @param string $complexCodePk
     * @param string $roomName
     * @param string $operation
     * @param string $powerOnOff
     * @param string $status
     *
     * @return array|null
     */
    private function setDeviceStatus(string $complexCodePk, string $roomName, string $operation, string $powerOnOff, string $status) :? array
    {
        $fcData = [];

        $devices = $this->devices;
        $operations = $this->operations;
        $modes = $this->modes;

        $deviceValidate = $this->getDeviceValidation($roomName, $status, $operation);
        if ($deviceValidate === false) {
            return null;
        }

        // 디바이스 번호
        $id = $devices[$roomName];
        if (empty($id) === true) {
            return null;
        }

        // 제어 상태 변경을 위한 operation
        $ctOperation = $operations[$operation];
        if (empty($ctOperation) === true) {
            return null;
        }

        if ($operation === 'power') {
            $status = ($powerOnOff === 'off') ? 'on' : 'off';
        }

        if ($operation === 'lower_temperature' || $operation === 'upper_temperature') {
            $operation = 'setTemp';
        }

        $value = ($operation === 'setTemp' || $operation === 'power') ? $status :  $this->getRealValue($ctOperation, $status);

        $ctOperation = "{$ctOperation}_{$value}";

        // 상태 데이터 요청
        $cURL = $this->devOptions['CONTROL_SAMSUNG_API_URI'] . "/set/0/{$id}/{$ctOperation}?complex_code={$complexCodePk}";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $cURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MaxTimeout);
        curl_exec($ch);

        $fcData = [
            'operation' => $operation,
            'status' => $value
        ];

        return $fcData;
    }

    /**
     * 디바이스 정보  유효성 검증
     *
     * @param string $roomName
     * @param string $value
     * @param string $operation
     *
     * @return bool
     */
    private function getDeviceValidation(string $roomName, string $value, string $operation) : bool
    {
        $devices = $this->devices;
        $operations = $this->operations;
        $fans = $this->fans;
        $modes = $this->modes;

        if (array_key_exists($roomName, $devices) === false) {
            // 장소에 대해 값 검증
            return false;
        }

        if (array_key_exists($operation, $operations) === false) {
            // 기능 수행정보에 대한 값 검증
            return false;
        }

        if ($operation === 'fan_speed' && array_key_exists($value, $fans) === false) {
            // 풍량 대한 값 검증
            return false;
        }

        if ($operation === 'mode' && array_key_exists($value, $modes) === false) {
            // 모드에 대한 값 검증
            return false;
        }

        return true;
    }

    /**
     * 연산 정보 조회
     *
     * @param string $mode
     * @param int $status
     *
     * @return int
     */
    private function getRealValue(string $mode, int $status) : string
    {
        $fcResult = 0;

        switch ($mode) {
            case 'fanSpeed' :
                $fcResult = $this->fans[$status];
                break;
            case 'operationMode' :
                $fcResult = $this->modes[$status];
                break;
        }

        return $fcResult;
    }
}