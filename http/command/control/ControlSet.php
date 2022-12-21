<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ControlSet
 */
class ControlSet extends Command
{
    /** @var array $devices 디바이스 정보  */
    private array $devices = [];

    /** @var array $operations 기능 수행 정보 */
    private array $operations = Config::CONTROL_AIR_CONDITION_COMMAND['lg']['operation'];
    
    /** @var array $fans 풍량 정보 */
    private array $fans = Config::CONTROL_AIR_CONDITION_COMMAND['lg']['fan'];
    
    /** @var array $modes 모드 정보  */
    private array $modes = Config::CONTROL_AIR_CONDITION_COMMAND['lg']['mode'];

    /**
     * ControlSet constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ControlSet destructor.
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

        $complexCodePk = $_SESSION['ss_complex_pk'];

        $devOptions = $this->devOptions;
        if ($devOptions['IS_DEV'] == '1') {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 정보조회를 위한 객체 값 할당.
        $this->sensorObj = $this->getSensorManager($complexCodePk);
        // 층 정보 건물별로 할당하기
        $this->setFloor();

        $roomName = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $status = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $powerOnOff = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : false;
        $operation = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : '';
        $isReady = isset($params[4]['value']) === true ? $params[4]['value'] : false;

        // 제어 사용 중단 된경우..
        if ($isReady === false) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        if ($operation === 'power') {
            // 전원 상태 변경
            $result = $this->setDeviceStatus($complexCodePk, $roomName, $operation, $powerOnOff, $status);
        } else {
            // 기능 상태 변경
            $result = $this->setDeviceStatus($complexCodePk, $roomName, $operation, $powerOnOff, $status, 'fc6');
        }

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
     */
    private function setFloor() : void
    {
        $fcData = [];

        $devices = $this->sensorObj->getControlDeviceInfo();

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
     * @param bool $powerOnOff
     * @param string $status
     * @param string $mode
     *
     * @return array|null
     */
    private function setDeviceStatus(string $complexCodePk, string $roomName, string $operation, bool $powerOnOff, string $status, string $mode = 'fc5') :? array
    {
        /*
            [파라미터]
            id: 디바이스 고유번호
            complex_code : 건물 정보
            operation : 기능 수행 정보
            cmd : 상태값
            http://211.43.14.10:5001/lg/fc5?id=1&complex_code=2002&operation=1&cmd=2 상태 제어
            fc5 에어컨 전원 제어
            fc6 에어컨 온도, 팬,  등등 제어
        */

        $fcData = '';

        $devices = $this->devices;
        $operations = $this->operations;

        $deviceValidate = $this->getDeviceValidation($mode, $roomName, $status, $operation);
        if ($deviceValidate === false) {
            return null;
        }

        if ($operation === 'power') {
            $status = $powerOnOff;
        }

        if ($operation === 'lower_temperature' || $operation === 'upper_temperature') {
            $operation = 'set_temperature';
        }

        $status = $this->getTransferValue($operation, $status);

        // 디바이스 번호
        $id = $devices[$roomName];
        // 제어 상태 변경을 위한 operation
        $ctOperation = $operations[$mode][$operation];

        // 테스트
        //$complexCodePk = '2001';
        //$id = 25;

        // 상태 데이터 요청
        $cURL = $this->devOptions['CONTROL_API_URL'] . $mode ."?id=" . $id . "&complex_code=" . $complexCodePk . "&operation=" . $ctOperation . "&cmd=" . $status;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $cURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MaxTimeout);
        curl_exec($ch);

        $fcData = [
            'operation' => $operation,
            'status' => $status
        ];

        return $fcData;
    }

    /**
     * 사용자가 선택한 항목에 따라 자료형 변경
     *
     * @param String $mode
     * @param string $value
     *
     * @return bool|int|string
     */
    private function getTransferValue(string $mode, string $value)
    {
        $status = $value;

        switch ($mode)
        {
            case 'power':
                // boolean으로 강제 형변환
                $status = (boolean)!$value;
                if ($status === false) {
                    $status = (int)0;
                }
                break;
            case 'upper_temperature':
            case 'lower_temperature':
                // 정수타입으로 강제형변환
                $status = (int)$status;
                break;
        }

        return $status;
    }

    /**
     * 디바이스 정보  유효성 검증
     *
     * @param string $mode
     * @param string $roomName
     * @param string $value
     * @param string $operation
     *
     * @return bool
     */
    private function getDeviceValidation(string $mode, string $roomName, string $value, string $operation) : bool
    {
        $devices = $this->devices;
        $operations = $this->operations;
        $fans = $this->fans;
        $modes = $this->modes;

        if (array_key_exists($roomName, $devices) === false) {
            // 장소에 대해 값 검증
            return false;
        }

        if (array_key_exists($operation, $operations[$mode]) === false) {
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
}