<?php
namespace Http\Command;

use EMS_Module\ControlFactory;
use EMS_Module\Utility;

/**
 * Class Control
 */
class Control extends Command
{
    /** @var array $devices 디바이스 정보 (삭제) */
    private array $devices = [];

    /** ControlFactory $controlFactory 제어 팩토리 객채 */
    private ?ControlFactory $controlFactory = null;

    /** @var string $controlType 제어 타입  */
    private string $controlType = 'read';

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

        $devOptions = $this->devOptions;
        if ($devOptions['IS_DEV'] == '1') {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $prefix = Utility::getInstance()->getConnectionMethodPrefix();

        $complexCodePk = $_SESSION[$prefix . 'ss_complex_pk'];
        $roomName = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $currentFloor = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $isReady = isset($params[2]['value']) === true ? $params[2]['value'] : false;
        $onOffDisplay = isset($params[3]['value']) === true ? $params[3]['value'] : false;
        $company = isset($params[4]['value']) === true ? $params[4]['value'] : '';

        // 정보조회를 위한 객체 값 할당.
        $this->sensorObj = $this->getSensorManager($complexCodePk);
        $floorDevices = $this->sensorObj->getControlDeviceInfo();
        $this->devices = $floorDevices[$currentFloor];

        // 팩토리 객체 생성
        $this->controlFactory = new ControlFactory();

        // 모든 기기에 대한 전원 상태 조회
        $allPowerStatus = $this->getDeviceAllStatus($complexCodePk, $company, $currentFloor, $onOffDisplay);
        if ($allPowerStatus === null) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 전원 상태
        $powerStatus = $this->getDeviceStatus($complexCodePk, $company, $roomName);
        if ($powerStatus === null) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 세부 상태 조회
        $functionStatus = $this->getDeviceStatus($complexCodePk, $company, $roomName, 'fc3');
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
     * 모든층에 대한 상세 정보 조회
     * 
     * @param string $complexCodePk
     * @param string $company
     * @param bool $onOffDisplay
     * 
     * @return array|null
     */
    public function getDeviceAllStatus(string $complexCodePk, string $company, bool $onOffDisplay) :? array
    {
        $fcData = [];

        if ($onOffDisplay === false) {
            return $fcData;
        }

        $devices = $this->devices;

        $controlType = $this->controlType;
        $controlFactory = $this->controlFactory;

        $options = [
            'status_type' => $this->getStatusType('fc1'),
            'is_display' => true,
        ];

        foreach ($devices as $key => $id) {
            $options['id'] = $id;

            $result = $controlFactory->processControl($complexCodePk, $company, $controlType, $options);
            if ($result === '') {
                return null;
            }

            if (count($result) === 0) {
                return null;
            }

            // 층별 디바이스 전원 여부 저장
            $fcData[$key] = $result[0];
        }

        return $fcData;
    }

    /**
     * 상태 조회
     *
     * @param string $complexCodePk
     * @param string $company
     * @param string $roomName
     * @param string $mode
     *
     * @return array|null
     */
    private function getDeviceStatus(string $complexCodePk, string $company, string $roomName, string $mode = 'fc1') :? array
    {
        $fcData = [];

        // 디바이스 번호
        $id = $this->devices[$roomName];

        $controlType = $this->controlType;

        $options = [
            'id' => $id,
            'status_type' => $this->getStatusType($mode),
            'is_display' => true,
        ];

        $fcData = $this->controlFactory->processControl($complexCodePk, $company,$controlType, $options);
        if ($fcData === '') {
            return null;
        }

        if (count($fcData) === 0) {
            return null;
        }

        return $fcData;
    }

    /**
     * status_type 반환
     *
     * @param string $mode
     *
     * @return string
     */
    private function getStatusType(string $mode) : string
    {
        $statusType = 'power_etc';

        switch ($mode) {
            case 'fc3'  :
                $statusType = 'operation_etc';
                break;
        }

        return $statusType;
    }
}