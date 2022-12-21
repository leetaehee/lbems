<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class IntegrationComplexSend 연계 업체에 단지정보 전송
 */
class IntegrationComplexSend extends Command
{
    /**
     * IntegrationComplexSend constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * IntegrationComplexSend Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool|mixed
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        // 연계대상 조회
        $rIntegrationQ = $this->emsQuery->getQuerySelectIntegrationTarget();
        $integrationData = $this->query($rIntegrationQ);

        for ($i = 0; $i < count($integrationData); $i++) {
            $target = $integrationData[$i]['target'];

            // 연계업체 전송
            $this->sendIntegrationComplexData($target);
        }

        $this->data = [];
        return true;
    }

    /**
     * 연계 업체 정보 전송
     *
     * @param string $target
     *
     * @return void
     *
     * @throws \Exception
     */
    private function sendIntegrationComplexData(string $target) : void
    {
        $integrationOptions = Config::API_INTEGRATION_SEND_OPTIONS[$target];

        $optionCount = count($integrationOptions);
        if ($optionCount === 0) {
            return;
        }

        $apiUrl = $integrationOptions['API_URL'];

        $url = $this->devOptions[$apiUrl];
        if (empty($url) === true) {
            return;
        }

        $url .= $integrationOptions['COMPLEX_ADDR']['URL'];
        $method = $integrationOptions['COMPLEX_ADDR']['METHOD'];

        $httpHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $options = [
            'x-form-urlencoded' => true,
        ];

        // 연계 대상에 속한 업체 조회
        $rComplexInfoQ = $this->emsQuery->getQuerySelectIntegrationComplexInfo($target);
        $complexInfo = $this->query($rComplexInfoQ);

        for ($j = 0; $j < count($complexInfo); $j++) {
            $complexCodePk = $complexInfo[$j]['complex_code_pk'];
            $complexName = Utility::getInstance()->updateDecryption($complexInfo[$j]['name']);
            $buildingAddr = Utility::getInstance()->updateDecryption($complexInfo[$j]['addr']);

            $data = [
                'complex_code' => $complexCodePk,
                'complex_nm' => $complexName,
                'building_addr' => $buildingAddr,
            ];

            $fcResult = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $data, $options);

            // 로그남기기
            Utility::getInstance()->log(
                'integration',
                'complex_addr',
                "http response code: {$fcResult['code']} , {$complexCodePk} 가 추가 되었습니다."
            );
        }

    }
}