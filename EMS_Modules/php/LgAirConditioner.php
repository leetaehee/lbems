<?php
namespace EMS_Module;

/**
 * Class LgAirConditioner LG 에어컨 제어
 */
class LgAirConditioner extends AirConditioner
{
    /**
     * LgAirConditioner Construct.
     *
     * @param string $complexCodePk
     * @param string $company
     */
    public function __construct(string $complexCodePk, string $company)
    {
        parent::__construct($complexCodePk, $company);

        $this->apiURL = $this->devOptions['CONTROL_API_URL'];
    }

    /**
     * LgAirConditioner Destruct.
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
    public function getStatus(string $complexCodePk, string $id, array $options) : array
    {
        $fcData = [];

        $apiURL = $this->apiURL;
        $controlInfo = $this->controlInfo;

        $statusType = $options['status_type'];

        /*
            상태 조회

            [파라미터]
            id : 디바이스 고유번호
            complex_code : 건물정보

            http://ip:포트/lg/fc1?id=1&complex_code=[단지코드]
            fc1 에어컨 전원 상태
            fc3 에어컨 온도, 팬,  등등 상태
        */

        $mode = $statusType === 'operation_etc' ? 'fc3' : 'fc1';

        $controlInfo = array_values($controlInfo);
        if (in_array($id, $controlInfo) === false) {
            return [];
        }

        $parameter = [
            'id' => $id,
            'complex_code' => $complexCodePk,
        ];

        $apiURL .= $mode;
        $apiMethod = 'GET';

        $fcData = $this->getData($apiURL, $apiMethod, $parameter, $options);

        $sampleOptions = [
            'status_type' => $statusType,
        ];

        // 샘플데이터 생성
        $fcData = $this->makeSampleData($fcData, $sampleOptions);

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
    public function setStatus(string $complexCodePk, string $id, array $options = []) : array
    {
        $fcData = [
            'result' => 'True',
            'data' => [],
        ];

        /*
            [파라미터]
            id: 디바이스 고유번호
            complex_code : 단지 정보
            operation : 기능 명칭
            cmd : 상태값
            http://ip:포트/lg/fc5?id=[EHP 아이디]&complex_code=[단지코드]&operation=명령어&cmd=상태
            fc5 에어컨 전원 제어
            fc6 에어컨 온도, 팬,  등등 제어
        */

        $statusType = $options['status_type'];

        $mode = $statusType === 'power_etc' ? 'fc5' : 'fc6';
        $apiURL = $this->apiURL;

        $parameter = $options['parameter'];
        $operation = $parameter['operation'];
        $status = $parameter['status'];

        $validateResult = $this->validateDeviceValue($id, $operation, $status);
        if ($validateResult === false) {
            $fcData['result'] = 'False';
            return $fcData;
        }

        $tempParameter = $this->makeParameter($statusType, $parameter);

        $apiURL .= $mode;
        $apiMethod = 'GET';

        $parameter = [
            'id' => $id,
            'complex_code' => $complexCodePk,
            'operation' => $mode,
            'cmd' => $tempParameter['status'],
        ];

        if ($operation === 'lower_temperature' || $operation === 'upper_temperature') {
            $options['parameter']['operation'] = 'set_temperature';
        }

        $fcData = $this->setData($apiURL, $apiMethod, $parameter, $options);

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
     protected function getData(string $url, string $method, array $parameter, array $options) : array
     {
         $fcData = [];
         $statusType = $options['status_type'];

         $communicationMethod = $this->communicationMethod;
         switch ($communicationMethod) {
             case 'API' :
                 $httpHeaders = $this->httpHeaders;
                 $httpOptions = $this->httpOptions;

                 $result = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $parameter, $httpOptions);
                 if ($result['code'] != 200) {
                     return $fcData;
                 }

                 if ($result['msg'] == 'None') {
                     return $fcData;
                 }

                 $fcData = $this->toArray($result['msg']);
                 break;
             case 'DATABASE' :
                 $db = $this->db;

                 $complexCodePk = $parameter['complex_code'];
                 $id = $parameter['id'];
                 
                 $rControlQ = $this->emsQuery->getQuerySelectAirConditionerData($complexCodePk, $id);
                 $db->query($rControlQ);

                 $data = $this->db->getData();

                 $fcData = $this->makeFormatting($options['status_type'], $data);
                 break;
             case 'SAMPLE' :
                 $fcData = TestSampleMap::AIR_CONDITIONER_SAMPLE_DATA[$this->company][$statusType];
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
                $id = $parameter['id'];
                $status = $parameter['cmd'];

                $operation = $options['parameter']['operation'];

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

        $fcData['data'] = $options['parameter'];

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

        switch ($options['status_type']) {
            case 'power_etc' :
                $powerValues = array_values($airConditionerFormats['power']);

                $fcData[0] = $powerValues[array_rand($powerValues, 1)];
                break;
            case 'operation_etc' :
                $fanSpeedValues = array_values($airConditionerFormats['fan_speed']);
                $opModeValues = array_values($airConditionerFormats['op_mode']);
                $temperatureValue = $airConditionerFormats['temperature'];

                $temperature = $temperatureValue[array_rand($temperatureValue, 1)];

                $fcData[0] = $opModeValues[array_rand($opModeValues, 1)];
                $fcData[1] = $fanSpeedValues[array_rand($fanSpeedValues, 1)];
                $fcData[2] = $temperature;
                $fcData[3] = $temperature;
                $fcData[4] = $temperature;
                $fcData[5] = $temperature;

                break;
        }

        return $fcData;
    }
}