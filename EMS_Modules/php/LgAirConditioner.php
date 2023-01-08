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
        $apiURL = $this->apiURL;
        $controlInfo = $this->controlInfo;

        /*
            상태 조회

            [파라미터]
            id : 디바이스 고유번호
            complex_code : 건물정보

            http://ip:포트/lg/fc1?id=1&complex_code=[단지코드]
            fc1 에어컨 전원 상태
            fc3 에어컨 온도, 팬,  등등 상태
        */

        $mode = $options['status_type'] === 'operation_etc' ? 'fc3' : 'fc1';

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

        return $this->requestData($apiURL, $apiMethod, $parameter, $options);
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
        $apiURL = $this->apiURL;
        $controlInfo = $this->controlInfo;
        $mode = $options['operation'] === 'power' ? 'fc5' : 'fc6';

        // [True, False]
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
     protected function requestData(string $url, string $method, array $parameter, array $options) : array
     {
         $fcData = [];
         $statusType = $options['status_type'];

         $communicationMethod = $this->communicationMethod;
         switch ($communicationMethod) {
             case 'API' :
                 $httpHeaders = $this->httpHeaders;
                 $options = $this->httpOptions;

                 $result = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $parameter, $options);
                 if ($result['code'] != 200) {
                     return $fcData;
                 }

                 if ($result['msg'] == 'None') {
                     return $fcData;
                 }

                 $fcData = $this->toArray($result['msg']);
                 break;
             case 'DATABASE' :
                 break;
             case 'SAMPLE' :
                 $fcData = TestSampleMap::AIR_CONDITIONER_SAMPLE_DATA[$this->company][$statusType];
                 break;
         }

         return $fcData;
     }
}