<?php
namespace EMS_Module;

use Database\DbModule;

use Http\SensorManager;

/**
 * Class AirConditioner
 */
abstract class AirConditioner
{
    /** @var DbModule|null $db 데이터베이스 객체 */
    protected ?DbModule $db = null;

    /** @var EMSQuery|null $emsQuery 쿼리 객체 */
    protected ?EMSQuery $emsQuery = null;

    /** @var string|null $company 에어컨 제조사 */
    protected ?string $company = null;

    /** @var string|null $apiURL API URL 정보 */
    protected ?string $apiURL = null;

    /** @var string|null $communicationMethod API 통신 방식 */
    protected ?string $communicationMethod = null;

    /** @var SensorManager|null $sensorManager */
    protected ?SensorManager $sensorManager = null;

    /** @var array $controlInfo 층별 아이디 정보 */
    protected array $controlInfo = [];

    /** @var array $httpHeaders HttpHeader 정보 */
    protected array $httpHeaders = [];

    /** @var array $httpOptions Http 선택 정보 */
    protected array $httpOptions = [
        'time_out' => 10000,
    ];

    /** @var array $devOptions */
    protected array $devOptions = [];

    /**
     * AirConditioner Constructor.
     *
     * @oaram string $complexCodePk
     */
    public function __construct(string $complexCodePk, string $company)
    {
        $this->sensorManager = new SensorManager();
        $this->db = new DbModule();
        $this->emsQuery = new EMSQuery();
        $this->devOptions = parse_ini_file(dirname(__FILE__) . '/../../.env');
        $this->company = $company;
        $this->communicationMethod = Config::COMMUNICATION_METHOD;

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
        $jsonString = str_replace('[', '', $jsonString);
        $jsonString = str_replace(']', '', $jsonString);

        $fcData = explode(',' , $jsonString);

        return $fcData;
    }

    /**
     * EHP  상태 조회 시 기본 포맷 제공
     * (참고. db 에서 조회 시 절대 배열로 받지말 것 - 받을 경우 함수 수정해야 함)
     *
     * @param string $statusType
     * @param array $data
     *
     * @return array
     */
    protected function makeFormatting(string $statusType, array $data) : array
    {
        $fcData = [];

        $airConditionerFormats = Config::AIR_CONDITIONER_FORMAT;

        switch ($statusType) {
            case 'power_etc' :
                $power = $data['power'];

                $fcData = [
                    $airConditionerFormats['power'][$power],
                    'False',
                    'False',
                    'False',
                    'False',
                    'False',
                    'False',
                ];
                break;
            case 'operation_etc' :
                $opMode = $data['opMode'];
                $fanSpeed = $data['fanSpeed'];
                $setTemp = $data['setTemp'];
                $upperTemperature = $data['upperTemperature'];
                $lowerTemperature = $data['lowerTemperature'];
                $roomTemp = $data['roomTemp'];

                $fcData = [
                    $airConditionerFormats['op_mode'][$opMode],
                    $airConditionerFormats['fan_speed'][$fanSpeed],
                    $setTemp,
                    $upperTemperature,
                    $lowerTemperature,
                    $roomTemp,
                    0,
                    0,
                    0,
                ];
                break;
        }

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

    /**
     * 데이터 조회
     *
     * @param string $url
     * @param string $method
     * @param array $parameter
     * @param array $options
     *
     * @return array
     */
    abstract protected function getData(string $url, string $method, array $parameter, array $options) : array;

    /**
     * 데이터 반영
     *s
     * @param string $url
     * @param string $method
     * @param array $parameter
     * @param array $options
     *
     * @return array
     */
    abstract protected function setData(string $url, string $method, array $parameter, array $options) : array;

    /**
     * 샘플 데이터 생성 - Config::COMMUNICATION_METHOD = SAMPLE  인 경우에만 적용
     *
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    abstract protected function makeSampleData(array $data, array $options) : array;
}