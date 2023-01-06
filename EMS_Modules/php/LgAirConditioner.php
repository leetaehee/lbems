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
     */
    public function __construct(string $complexCodePk)
    {
        parent::__construct($complexCodePk);

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
     * @param string $company
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    public function getStatus(string $complexCodePk, string $company, string $id, array $options) : array
    {
        $apiURL = $this->apiURL;
        $controlInfo = $this->controlInfo;

        $statusType = $options['status_type'];
        $mode = $statusType === 'operation_etc' ? 'fc3' : 'fc1';

        $controlInfo = array_values($controlInfo);

        if (in_array($id, $controlInfo) === false) {
            return [];
        }

        // AirConditioner 에서 함수 하나 추가해서 API 에서 조회하는것과 직접 데이터 가져오는 방식(db, 테스트데이터) 할수있도록 할것

        if ($options['is_test'] === true) {
            return Config::AIR_CONDITIONER_SAMPLE_DATA[$company][$statusType];
        }

        $options = [
            'time_out' => 10000,
        ];

        $parameter = [
            'id' => $id,
            'complex_code' => $complexCodePk,
        ];

        $apiURL .= $mode;
        $apiMethod = 'GET';
        $httpHeaders = [];

        $result = Utility::getInstance()->curlProcess($apiURL, $apiMethod, $httpHeaders, $parameter, $options);
        if ($result['code'] != 200) {
            return [];
        }

        if ($result['msg'] == 'None') {
            return [];
        }

        return $this->toArray($result['msg']);
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
    public function setStatus(string $complexCodePk, string $company, string $id, array $options = []) : array
    {
        $apiURL = $this->apiURL;
        $controlInfo = $this->controlInfo;
        $mode = $options['operation'] === 'power' ? 'fc5' : 'fc6';

        // [True, False]
        return [];
    }
}