<?php
namespace EMS_Module;

use Database\DbModule;

use Http\Command\Paper;
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
     * 명령어에 대해 변경해야 할 데이터베이스 컬럼 조회
     *
     * @param string $operation
     *
     * @return array
     */
    protected function getDataBaseColumn(string $operation) : array
    {
        return [
        ];
    }

    /**
     * 장비의 값을 검증
     *
     * @param string $id
     * @param string $operation
     * @param string $value
     *
     * @return bool
     */
    protected function validateDeviceValue(string $id, string $operation, string $value) : bool
    {
        $airConditionerFormats = Config::AIR_CONDITIONER_FORMAT;

        $controlInfo = array_values($this->controlInfo);
        if (in_array($id, $controlInfo) === false) {
            return false;
        }

        if (array_key_exists($operation, $airConditionerFormats) == false) {
            return false;
        }

        if ($operation === 'power' &&
            (is_string($value) === true && in_array($value, ['1', '0', '']) === false)) {
            return false;
        }

        $fanSpeedValues = array_values($airConditionerFormats['fan_speed']);
        if ($operation === 'fan_speed' && in_array($value, $fanSpeedValues) === false) {
            return false;
        }

        $opModeValues = array_values($airConditionerFormats['op_mode']);
        if ($operation === 'op_mode' && in_array($value, $opModeValues) === false) {
            return false;
        }

        if ($operation === 'upper_temperature' && $value > $airConditionerFormats[$operation]) {
            return false;
        }

        if ($operation === 'lower_temperature' && $value < $airConditionerFormats[$operation]) {
            return false;
        }

        return true;
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

    /**
     * 하위 클래스 특성에 맞게 파라미터를 반환.
     *
     * @param string $statusType
     * @param array $parameter
     *
     * @return array
     */
    abstract protected function makeParameter(string $statusType, array $parameter) : array;
}