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
     * @param string $company
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    public function getStatus(string $complexCodePk, string $id, array $options) : array
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

        // 아이디가 주어진 경우 아이디에 해당한 것만 뽑기
        $fcData = Utility::getInstance()->makeSelectedDataByKey($fcData, $id, $searchColumn);

        // webB에서 보여주는 경우 lg처럼 결과 나오도록 하기. 파라미터는 검토
        //$options['web'] = true

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
    public function setStatus(string $complexCodePk, string $id, array $options) : array
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
    public function requestData(string $url, string $method, array $parameter, array $options) : array
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
                break;
            case 'SAMPLE' :
                $fcData = TestSampleMap::AIR_CONDITIONER_SAMPLE_DATA[$this->company];
                break;
        }

        return $fcData;
    }
}