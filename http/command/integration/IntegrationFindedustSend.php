<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class IntegrationFindedustSend 연계 업체에 환경센서 데이터 전송
 */
class IntegrationFindedustSend extends Command
{
    /**
     * IntegrationFindedustSend constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * IntegrationFindedustSend Destructor.
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

        $url .= $integrationOptions['ENVIRONMENT_SENSOR']['URL'];
        $method = $integrationOptions['ENVIRONMENT_SENSOR']['METHOD'];

        $httpHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $options = [
            'x-form-urlencoded' => true,
        ];

        $rSensorDataQ = $this->emsQuery->getQuerySelectSysIntegrationFinedustData($complexCodePk, $target);
        $data = $this->query($rSensorDataQ);

        for ($k = 0; $k < count($data); $k++) {
            $d = $data[$k];

            $temps = [
                'complex_code' => $d['complex_code_pk'],
                'sensor_sn' => $d['sensor_sn'],
                'val_date' => $d['val_date'],
                'pm25' => $d['pm25'],
                'temperature' => $d['temperature'],
                'humidity' => $d['humidity'],
                'co2' => $d['co2']
            ];

            $fcResult = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $temps, $options);

            // 로그남기기
            Utility::getInstance()->log(
                'integration',
                'environment',
                "http response code: {$fcResult['code']} , {$d['sensor_sn']} 가 추가 되었습니다.",
            );
        }

    }
}