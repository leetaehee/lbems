<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class Control
 */
class Control extends Command
{
    /** @var array $devices 디바이스 정보 */
    private array $devices = [];

    /**
     * Control constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Control destructor.
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
        $currentFloor = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $isReady = isset($params[2]['value']) === true ? $params[2]['value'] : false;
        $onOffDisplay = isset($params[3]['value']) === true ? $params[3]['value'] : false;

        // 제어 사용 중단 된경우..
        if ($isReady === false) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 모든 기기에 대한 전원 상태 조회
        $allPowerStatus = $this->getDeviceAllStatus($complexCodePk, $currentFloor, $onOffDisplay);
        if ($allPowerStatus === null) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 전원 상태
        $powerStatus = $this->getDeviceStatus($complexCodePk, $roomName);
        if ($powerStatus === null) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 세부 상태 조회
        $functionStatus = $this->getDeviceStatus($complexCodePk, $roomName, 'fc3');
        if ($functionStatus === null) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $data = [
            'all_power_on_off' => $allPowerStatus,
            'power_on_off' => $powerStatus,
            'function_status' => $functionStatus,
        ];

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
     * 모든층에 대한 상세 정보 조회
     * 
     * @param string $complexCodePk
     * @param string $floor
     * @param bool $onOffDisplay
     * 
     * @return array|null
     */
    public function getDeviceAllStatus(string $complexCodePk, string $floor, bool $onOffDisplay) :? array
    {
        $fcData = [];

        if ($onOffDisplay === false) {
            return $fcData;
        }

        $floorDevices = $this->sensorObj->getControlDeviceInfo();
        $devices = $floorDevices[$floor];

        foreach ($devices as $key => $id) {

            $cURL = $this->devOptions['CONTROL_API_URL'] . "fc1?id=" . $id . "&complex_code=" . $complexCodePk;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $cURL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MaxTimeout);

            $result = curl_exec($ch);
            if ($result === 'None') {
                return null;
            }

            $temp = $this->toArray($result);

            // 층별 디바이스 전원 여부 저장
            $fcData[$key] = $temp[0];
        }

        return $fcData;
    }

    /**
     * 상태 조회
     *
     * @param string $complexCodePk
     * @param string $roomName
     * @param string $mode
     *
     * @return array|null
     */
    private function getDeviceStatus(string $complexCodePk, string $roomName, string $mode = 'fc1') :? array
    {
        /*
            [파라미터]
            id : 디바이스 고유번호
            complex_code : 건물정보

            http://211.43.14.10:5001/lg/fc1?id=1&complex_code=2001 상태 조회
            fc1 에어컨 전원 상태
            fc3 에어컨 온도, 팬,  등등 상태
        */

        $fcData = [];
        $devices = $this->devices;

        if (array_key_exists($roomName, $devices) === false) {
            return null;
        }

        // 디바이스 번호
        $id = $devices[$roomName];

        // 테스트
        //$complexCodePk = '2001';
        //$id = '25';

        // 상태 데이터 요청
        $cURL = $this->devOptions['CONTROL_API_URL'] . $mode ."?id=" . $id . "&complex_code=" . $complexCodePk;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MaxTimeout);

        $fcData = curl_exec($ch);
        if ($fcData === 'None') {
            return null;
        }

        $fcData = $this->toArray($fcData);

        return $fcData;
    }

    /**
     * 문자열에서 배열로 변환
     *
     * @param string $str
     *
     * @return array
     */
    private function toArray(string $str) : array
    {
        $fcData = [];

        // 시작과 종료부분에서 [, ]  제거하기
        $str = str_replace('[', '',$str);
        $str = str_replace(']', '',$str);

        $fcData = explode(',' , $str);

        return $fcData;
    }
}