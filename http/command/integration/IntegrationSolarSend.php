<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class IntegrationSolarSend 연계 업체에 태양광 데이터 전송
 */
class IntegrationSolarSend extends Command
{
    /**
     * IntegrationSolarSend constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * IntegrationSolarSend Destructor.
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
    public function execute(array $params): ?bool
    {
        // 연계대상 조회
        $rIntegrationQ = $this->emsQuery->getQuerySelectIntegrationTargetAndInfo();
        $integrationData = $this->query($rIntegrationQ);

        for ($i = 0; $i < count($integrationData); $i++) {
            $target = $integrationData[$i]['target'];
            $complexCodePk = $integrationData[$i]['complex_code_pk'];

            // 연계업체 전송
            $this->sendIntegrationData($target, $complexCodePk);
        }

        $this->data = [];
        return true;
    }

    /**
     * 연계 업체 정보 전달
     *
     * @param string $target
     * @param string $complexCodePk
     *
     * @return void
     *
     * @throws \Exception
     */
    private function sendIntegrationData(string $target, string $complexCodePk): void
    {
        $integrationOptions = Config::API_INTEGRATION_SEND_OPTIONS[$target];

        $divisors = Config::DIVISOR_VALUES;
        $option = 11;

        $optionCount = count($integrationOptions);
        if ($optionCount === 0) {
            return;
        }

        $apiUrl = $integrationOptions['API_URL'];

        $url = $this->devOptions[$apiUrl];
        if (empty($url) === true) {
            return;
        }

        $url .= $integrationOptions['SOLAR_CONSUME']['URL'];
        $method = $integrationOptions['SOLAR_CONSUME']['METHOD'];

        $httpHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $options = [
            'x-form-urlencoded' => true,
        ];

        $solarQuery = Utility::getInstance()->makeWhereClause('sensor', 'inout', 'I');

        $rSensorDataQ = $this->emsQuery->getQuerySelectSysIntegrationSensorData($complexCodePk, $option,  $target, $solarQuery);
        $data = $this->query($rSensorDataQ);

        for ($k = 0; $k < count($data); $k++) {
            $d = $data[$k];

            $temps = [
                'complex_code' => $d['complex_code_pk'],
                'sensor_sn' => $d['sensor_sn'],
                'val' => $d['val'],
                'val_date' => $d['val_date'],
            ];

            $fcResult = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $temps, $options);

            // 로그남기기
            Utility::getInstance()->log(
                'integration',
                'solar',
                "http response code: {$fcResult['code']} , {$d['sensor_sn']} 가 추가 되었습니다."
            );
        }
    }
}