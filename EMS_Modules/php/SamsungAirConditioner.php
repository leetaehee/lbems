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

        $fcData = $this->getData($apiURL, $apiMethod, $parameter, $options);

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
    public function setStatus(string $complexCodePk, string $id, array $options = []): array
    {
        $fcData = [
            'result' => 'True',
            'data' => [],
        ];

        /*
            [파라미터]
            id : 디바이스 고유번호
            complex_code : 단지코드
            ctOperation : 기능과 상태
            http://ip:포트/set/0/[EHP 아이디]/기능_상태?complex_code=[단지코드]
            fanSpeed 풍량
            setTemp 온도 상승, 하락
            power 전원
            operationMode mode
         */

        $statusType = $options['status_type'];

        $apiURL = $this->apiURL . '/set/0';

        $parameter = $options['parameter'];
        $operation = $parameter['operation'];
        $status = $parameter['status'];

        $validateResult = $this->validateDeviceValue($id, $operation, $status);
        if ($validateResult === false) {
            $fcData['result'] = 'False';
            return $fcData;
        }

        $temps = $this->makeParameter($statusType, $parameter);

        // api에서 사용할 상태 만들기, 파라미터는 단지만 보내면 됨
        $cmdStatus = $this->makeCommandStatus($operation, $temps['status']);

        $apiURL .= "/{$id}/{$cmdStatus}";
        $method = 'GET';

        $parameter = [
            'complex_code' => $complexCodePk,
        ];

        if ($operation === 'lower_temperature' || $operation === 'upper_temperature') {
            $options['parameter']['operation'] = 'set_temperature';
        }

        $options['parameter']['id'] = $id;
        $options['parameter']['cmd_status'] = $cmdStatus;

        $fcData = $this->setData($apiURL, $method, $parameter, $options);

        return $fcData;
    }

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
    protected function getData(string $url, string $method, array $parameter, array $options): array
    {
        $fcData = [];

        $complexCodePk = $parameter['complex_code'];

        $communicationMethod = $this->communicationMethod;
        switch ($communicationMethod) {
            case 'API' :
                $httpHeaders = $this->httpOptions;
                $httpOptions = $this->httpOptions;

                $result = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $parameter, $httpOptions);
                if ($result['code'] != 200) {
                    return $fcData;
                }

                $fcData = json_decode($result['msg'], true);
                break;
            case 'DATABASE' :
                $fcData = $this->downloadSampleFormat($complexCodePk);
                break;
            case 'SAMPLE' :
                $fcData = $this->downloadSampleFormat($complexCodePk);
                break;
        }

        return $fcData;
    }

    /**
     * 데이터 반영
     *
     * @param string $url
     * @param string $method
     * @param array $parameter
     * @param array $options
     *
     * @return array
     */
     protected function setData(string $url, string $method, array $parameter, array $options) : array
     {
         $fcData = [
             'result' => 'True',
             'data' => [],
         ];

         $dataBaseColumns = Config::AIR_CONDITIONER_FORMAT['database_column'];

         $communicationMethod = $this->communicationMethod;
         switch ($communicationMethod) {
             case 'API' :
                 $httpHeaders = $this->httpOptions;
                 $httpOptions = $this->httpOptions;

                 $result = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $parameter, $httpOptions);
                 if ($result['code'] != 200) {
                     $fcData['result'] = 'False';
                     return $fcData;
                 }
                 break;
             case 'DATABASE' :
                 $db = $this->db;

                 $complexCodePk = $parameter['complex_code'];
                 $temps = $options['parameter'];

                 $cmdStatus = $temps['cmd_status'];
                 $cmdStatusData = Utility::getInstance()->getExplodeData($cmdStatus,'_');

                 $id = $temps['id'];

                 $operation = $temps['operation'];
                 $status = $cmdStatusData[1];

                 $column = $dataBaseColumns[$operation];

                 $uControlQ = $this->emsQuery->getQueryUpdateAirConditionerData($complexCodePk, $id, $column, $status);
                 $result = $db->squery($uControlQ);

                 if ($result === false) {
                     $fcData['result'] = 'False';
                     return $fcData;
                 }

                 break;
             case 'SAMPLE' :
                 break;
         }

         return $fcData;
    }

    /**
     * 삼성에서 제공하는 API 샘플을 받는다.
     *
     * @param string $complexCodePk
     *
     * @return array
     */
    private function downloadSampleFormat(string $complexCodePk) : array
    {
        $fcData = [];

        $controlInfo = array_values($this->controlInfo);
        $sampleFormat = TestSampleMap::AIR_CONDITIONER_SAMPLE_DATA[$this->company];

        $options = [
            'complex_code_pk' => $complexCodePk,
        ];

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
     * 샘플 데이터 생성 - Config::COMMUNICATION_METHOD = SAMPLE | DATABASE 인 경우에만 적용
     *
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function makeSampleData(array $data, array $options): array
    {
        $fcData = $data;

        $communicationMethod = $this->communicationMethod;
        if (in_array($communicationMethod, ['SAMPLE', 'DATABASE']) === false) {
            return $fcData;
        }

        switch ($communicationMethod) {
            case 'SAMPLE' :
                $airConditionerFormats = Config::AIR_CONDITIONER_FORMAT;

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

                break;
            case 'DATABASE' :
                $db = $this->db;

                $id = $data['addr'];
                $complexCodePk = $options['complex_code_pk'];

                $rControlQ = $this->emsQuery->getQuerySelectAirConditionerData($complexCodePk, $id);
                $db->query($rControlQ);

                $data = $this->db->getData();

                $fcData['power'] = $data['power'];
                $fcData['opMode'] = $data['opMode'];
                $fcData['fanSpeed'] = $data['fanSpeed'];
                $fcData['setTemp'] = $data['setTemp'];
                $fcData['upperTemperature'] = $data['upperTemperature'];
                $fcData['lowerTemperature'] = $data['lowerTemperature'];
                $fcData['roomTemp'] = $data['roomTemp'];

                break;
        }

        return $fcData;
    }

    /**
     * 삼성 EHP 를 제어하기 위해 명령어와 상태 조합
     *
     * @param string $command
     * @param string $value
     *
     * @return string
     */
    private function makeCommandStatus(string $command, string $value) : string
    {
        $fcCommand = $command;

        /*
         * [명령어와 상태 조합]
         * fan_speed -> fanSpeed
         * set_temp , upper_temperature, lower_temperature -> setTemp
         * power
         * op_mode -> operationMode
         * [형식]
         * 명령어_상태
         * [예시]
         * power_off
         */

        switch ($fcCommand) {
            case 'fan_speed' :
                $fcCommand = 'fanSpeed';
                break;
            case 'set_temp' :
            case 'upper_temperature' :
            case 'lower_temperature' :
                $fcCommand = 'setTemp';
                break;
            case 'op_mode' :
                $fcCommand = 'operationMode';
                break;
        }

        $fcCommand .= "_{$value}";

        return $fcCommand;
    }
}