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
     */
    public function __construct(string $complexCodePk)
    {
        parent::__construct($complexCodePk);

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
    public function getStatus(string $complexCodePk, string $company, string $id, array $options) : array
    {
        $apiURL = $this->apiURL . '/get/0';
        $controlInfo = $this->controlInfo;

        if ($id !== '' && in_array($id, $controlInfo) === false) {
            // 삼성에어컨은 아이디를 받지않으나 옵션으로 처리할 생각임
            return [];
        }

        // AirConditioner 에서 함수 하나 추가해서 API 에서 조회하는것과 직접 데이터 가져오는 방식(db, 테스트데이터) 할수있도록 할것

        if ($options['is_test'] === true) {
            return Config::AIR_CONDITIONER_SAMPLE_DATA[$company];
        }

        $options = [
            'time_out' => 30000,
        ];

        $parameter = [
            'complex_code' => $complexCodePk,
        ];

        $apiMethod = 'GET';
        $httpHeaders = [];

        $result = Utility::getInstance()->curlProcess($apiURL, $apiMethod, $httpHeaders, $parameter, $options);
        if ($result['code'] != 200) {
            return [];
        }

        $data = json_decode($result['msg'], true);

        // 아이디가 주어진 경우 아이디에 해당되는것만 뽑기..
        // 해당 함수에서 addr 라고 고정되어 있는 함수를 일반화 시키기
        $fcData = Utility::getInstance()->makeSelectedDataByKey($data, $id);

        // web에서 보여주고자 하는 경우 데이터 결과를 lg처럼 할 것 ..
        // $options['web'] = true

        return $fcData;
    }

    /**
     * 제어 상태 처리
     *
     * @param string $complexCodePk
     * @param string $company
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    public function setStatus(string $complexCodePk, string $company, string $id, array $options) : array
    {
        // True, False
        return [];
    }
}