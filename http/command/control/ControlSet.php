<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\ControlFactory;
use EMS_Module\Utility;

/**
 * Class ControlSet
 */
class ControlSet extends Command
{
    /** @var array $devices 디바이스 정보  */
    private array $devices = [];

    /** ControlFactory $controlFactory 제어 팩토리 객채 */
    private ?ControlFactory $controlFactory = null;

    /** @var string $controlType 제어 타입  */
    private string $controlType = 'process';

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

        $devOptions = $this->devOptions;
        if ($devOptions['IS_DEV'] == '1') {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $prefix = Utility::getInstance()->getConnectionMethodPrefix();

        $complexCodePk = $_SESSION[$prefix . 'ss_complex_pk'];
        $roomName = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $status = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $powerOnOff = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : false;
        $operation = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : '';
        $isReady = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : false;
        $company = isset($params[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']) : '';
        $currentFloor = isset($params[6]['value']) === true ? Utility::getInstance()->removeXSS($params[6]['value']) : '';

        if ($isReady === false) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        // 정보조회를 위한 객체 값 할당.
        $this->sensorObj = $this->getSensorManager($complexCodePk);
        $floorDevices = $this->sensorObj->getControlDeviceInfo();
        $this->devices = $floorDevices[$currentFloor];

        // 팩토리 객체 생성
        $this->controlFactory = new ControlFactory();

        $options = [
            'operation' => $operation,
            'status' => $status,
        ];

        // 추후 값을 일반화 필요
        $statusType = $operation === 'power' ? 'fc5' : 'fc6';

        $result = $this->setDeviceStatus($complexCodePk, $company, $roomName, $statusType, $options);
        if (is_null($result) === true) {
            $this->data['control_error'] = 'Error';
            return true;
        }

        $data['result'] = $result;

        // 뷰에 데이터 바인딩
        $this->data = $data;
        return true;
    }

    /**
     * 상태 제어
     *
     * @param string $complexCodePk
     * @param string $company
     * @param string $roomName
     * @param string $statusType
     * @param array $options
     *
     * @return array|null
     */
    private function setDeviceStatus(string $complexCodePk, string $company, string $roomName, string $statusType, array $options) :? array
    {
        $fcData = [];

        $id = $this->devices[$roomName];

        $controlType = $this->controlType;
        $controlFactory = $this->controlFactory;

        $controlOptions = [
            'id' => $id,
            'status_type' => $this->getStatusType($statusType),
            'parameter' => $options
        ];
        $result = $controlFactory->processControl($complexCodePk, $company, $controlType, $controlOptions);
        if ($result['result'] === 'False') {
            return null;
        }

        $fcData = $result['data'];

        return $fcData;
    }
}