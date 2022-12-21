<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class SamsungControl 삼성 에어컨 제어
 */
class SamsungControl extends Command
{
    /** @var array $devices 디바이스 정보 */
    private array $devices = [];

    /** @var array $fans 풍량 정보 */
    private array $fans = Config::CONTROL_AIR_CONDITION_COMMAND['samsung']['fan'];

    /** @var array $modes 모드 정보  */
    private array $modes = Config::CONTROL_AIR_CONDITION_COMMAND['samsung']['mode_v'];

    /**
     * SamsungControl constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SamsungControl destructor.
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
        $currentFloor = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $isReady = isset($params[1]['value']) === true ? $params[1]['value'] : false;

        // 제어 사용 중단 된경우..
        if ($isReady === false) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $devOptions = $this->devOptions;
        if ($devOptions['IS_DEV'] == '1') {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $this->sensorObj = $this->getSensorManager($complexCodePk); // 정보조회를 위한 객체 값 할당.
        $this->setDevice($currentFloor); // 디바이스 정보 추출

        // 모든 기기에 대한 전원 상태 조회
        $controlData = $this->getDeviceAllStatus($complexCodePk);
        if ($controlData === null) {
            $data['control_error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $data = [
            'status' => $controlData,
        ];

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

        foreach ($tempDevices as $k => $v) {
            $fcData[$v] = $k;
        }

        $this->devices = $fcData;
    }

    /**
     * 모든층에 대한 상세 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return array|null
     */
    public function getDeviceAllStatus(string $complexCodePk) :? array
    {
        $fcData = [];
        $fcDeviceAdders = array_keys($this->devices);

        $fans = $this->fans;
        $modes = $this->modes;

        $cURL = $this->devOptions['CONTROL_SAMSUNG_API_URI'] ."/get/0?complex_code={$complexCodePk}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MaxTimeout);

        $result = curl_exec($ch);

        $statusData = json_decode($result);
        if (count($statusData) < 1) {
            return null;
        }

        foreach ($statusData as $index => $items) {
            $adder = $items->addr;
            $keyName = $this->devices[$adder];

            if (in_array($adder, $fcDeviceAdders) === false) {
                continue;
            }

            $fcData[$keyName]  = [
                'power' => $items->power, // 전원 상태
                'set_temperature' => $items->setTemp, // 실내기 설정온도
                'room_temperature' => $items->roomTemp, // 현재온도
                'op_mode' =>  $modes[$items->opMode], // 동작모드
                'fan_speed' => $fans[$items->fanSpeed], // 팬 동작 모드
            ];
        }

        return $fcData;
    }
}