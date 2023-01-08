<?php
namespace EMS_Module;

/**
 * Class SamsungAirConditioner 삼성 에어컨 제어
 */
class SamsungAirConditioner extends AirConditioner
{
    /**
     * SamsungAirConditioner Construct.
     *
     * @param string $complexCodePk
     * @param string $company
     */
    public function __construct(string $complexCodePk, string $company)
    {
        parent::__construct($complexCodePk, $company);

        $this->apiURL = $this->devOptions['CONTROL_SAMSUNG_API_URI'];
    }

    /**
     * SamsungAirConditioner Destruct.
     */
    public function __destruct()
    {
        parent::__destruct();
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
    public function getStatus(string $complexCodePk, string $id, array $options): array
    {
        $fcData = [];

        $apiURL = $this->apiURL . '/get/0';
        $controlInfo = $this->controlInfo;

        $searchColumn = 'addr';

        if ($id !== '' && in_array($id, $controlInfo) === false) {
            // 삼성에어컨은 아이디를 받지않으나 옵션으로 처리할 생각임
            return $fcData;
        }

        $parameter = [
            'complex_code' => $complexCodePk,
        ];

        $apiMethod = 'GET';

        $fcData = $this->requestData($apiURL, $apiMethod, $parameter, $options);

        if ($options['is_display'] === true) {
            // 해당한 것만 뽑기
            $fcData = Utility::getInstance()->makeSelectedDataByKey($fcData, $id, $searchColumn);

            // 화면에 보여주는 경우에 포맷을 함.
            $fcData = $this->makeFormatting($options['status_type'], $fcData);
        }

        return $fcData;
    }

    /**
     * 제어 상태 처리
     *
     * @param string $complexCodePk
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    public function setStatus(string $complexCodePk, string $id, array $options): array
    {
        // True, False
        return [];
    }

    /**
     * API 데이터 요청
     *
     * @param string $url
     * @param string $method
     * @param array $parameter
     * @param array $options
     *
     * @return array
     */
    protected function requestData(string $url, string $method, array $parameter, array $options): array
    {
        $fcData = [];

        $communicationMethod = $this->communicationMethod;
        switch ($communicationMethod) {
            case 'API' :
                $httpHeaders = $this->httpOptions;
                $options = $this->httpOptions;

                $result = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $parameter, $options);
                if ($result['code'] != 200) {
                    return $fcData;
                }

                $fcData = json_decode($result['msg'], true);
                break;
            case 'DATABASE' :
                $fcData = $this->downloadSampleFormat();
                break;
            case 'SAMPLE' :
                $fcData = $this->downloadSampleFormat();
                break;
        }

        return $fcData;
    }

    /**
     * 삼성에서 제공하는 API 샘플을 받는다.
     *
     * @return array
     */
    private function downloadSampleFormat(): array
    {
        $fcData = [];

        $controlInfo = array_values($this->controlInfo);
        $sampleFormat = TestSampleMap::AIR_CONDITIONER_SAMPLE_DATA[$this->company];

        $options = [];

        for ($fcIndex = 0; $fcIndex < count($controlInfo); $fcIndex++) {
            $id = $controlInfo[$fcIndex];
            $temps = $sampleFormat;

            $temps['addr'] = $id;
            $temps = $this->makeSampleData($temps, $options);

            array_push($fcData, $temps);
        }

        return $fcData;
    }

    /**
     * 샘플 데이터 생성 - Config::COMMUNICATION_METHOD = SAMPLE  인 경우에만 적용
     *
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function makeSampleData(array $data, array $options): array
    {
        $fcData = $data;
        $airConditionerFormats = Config::AIR_CONDITIONER_FORMAT;

        $communicationMethod = $this->communicationMethod;
        if ($communicationMethod !== 'SAMPLE') {
            return $fcData;
        }

        $powerKeys = array_keys($airConditionerFormats['power']);
        $fanSpeedKeys = array_keys($airConditionerFormats['fan_speed']);
        $opModeKeys = array_keys($airConditionerFormats['op_mode']);

        $temperatureValues = $airConditionerFormats['temperature'];
        $temperature = $temperatureValues[array_rand($temperatureValues, 1)];

        $fcData['power'] = $powerKeys[array_rand($powerKeys, 1)];
        $fcData['opMode'] = $opModeKeys[array_rand($opModeKeys, 1)];
        $fcData['fanSpeed'] = $fanSpeedKeys[array_rand($fanSpeedKeys, 1)];
        $fcData['setTemp'] = $temperature;
        $fcData['upperTemperature'] = $temperature;
        $fcData['lowerTemperature'] = $temperature;
        $fcData['roomTemp'] = $temperature;

        return $fcData;
    }
}