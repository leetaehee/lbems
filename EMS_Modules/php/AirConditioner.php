<?php
namespace EMS_Module;

use Http\SensorManager;

/**
 * Class AirConditioner
 */
abstract class AirConditioner
{
    /** @var string|null API URL 정보 */
    protected ?string $apiURL = null;

    /** @var SensorManager|null $sensorManager */
    protected ?SensorManager $sensorManager = null;

    /** @var array $controlInfo 층별 아이디 정보 */
    protected array $controlInfo = [];

    /** @var array $devOptions */
    protected array $devOptions = [];

    /**
     * AirConditioner Constructor.
     *
     * @oaram string $complexCodePk
     */
    public function __construct(string $complexCodePk)
    {
        $this->sensorManager = new SensorManager();
        $this->devOptions = parse_ini_file(dirname(__FILE__) . '/../../.env');

        $this->setAssignDeviceInfo($complexCodePk);
    }

    /**
     * AirConditioner Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 건물 코드에 대하여 디바이스 정보 할당
     *
     * @param string $complexCodePk
     */
    private function setAssignDeviceInfo(string $complexCodePk) : void
    {
        $fcData = [];
        $devices = $this->getSensorManager($complexCodePk)->getControlDeviceInfo();

        foreach ($devices as $key => $items) {
            foreach ($items as $k => $v) {
                $fcData[$k] = $v;
            }
        }

        $this->controlInfo = $fcData;
    }

    /**
    * 건물 센서 객체 반환
    *
    * @param string $complexCodePk
    *
    * @return SensorInterface
    */
    public function getSensorManager(string $complexCodePk) : SensorInterface
    {
        return $this->sensorManager->getSensorObject($complexCodePk);
    }

    /**
     * API를 통해 전달받은 json 데이터를 배열로 변환
     *
     * @param string $jsonString
     *
     * @return array
     */
    protected function toArray(string $jsonString) : array
    {
        $fcData = [];

        // 시작과 종료부분에서 [, ]  제거하기
        $jsonString = str_replace('[', '',$jsonString);
        $jsonString = str_replace(']', '',$jsonString);

        $fcData = explode(',' , $jsonString);

        return $fcData;
    }

    /**
     * 제어 상태 조회
     *
     * @param string $complexCodePk
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    abstract public function getStatus(string $complexCodePk, string $id, array $options) : array;

    /**
     * 제어 상태 처리
     *
     * @param string $complexCodePk
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    abstract public function setStatus(string $complexCodePk, string $id, array $options) : array;
}