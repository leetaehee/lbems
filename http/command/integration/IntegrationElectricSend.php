<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class IntegrationElectricSend 연계 업체에 전기 데이터 전송
 */
class IntegrationElectricSend extends Command
{
    /**
     * IntegrationElectricSend constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * IntegrationElectricSend Destructor.
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
    private function sendIntegrationData(string $target, string $complexCodePk) : void
    {
        $integrationOptions = Config::API_INTEGRATION_SEND_OPTIONS[$target];

        $sensorTypes = Config::SENSOR_TYPES;
        $divisors = Config::DIVISOR_VALUES;

        $optionCount = count($integrationOptions);
        if ($optionCount === 0) {
            return;
        }

        $apiUrl = $integrationOptions['API_URL'];

        $url = $this->devOptions[$apiUrl];
        if (empty($url) === true) {
            return;
        }

        $url .= $integrationOptions['ELECTRIC_CONSUME']['URL'];
        $method = $integrationOptions['ELECTRIC_CONSUME']['METHOD'];

        $httpHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $options = [
            'x-form-urlencoded' => true,
        ];

        for ($j = 0; $j < count($sensorTypes); $j++) {

            $sensorType = $sensorTypes[$j];

            $option = $j;
            if ($option === 1 || $option === 2 || $option === 11 || $option === 13) {
                /*
                 * 가스,수도,태양광,등유 제외
                 * 엔텍은 전기만 됨
                 */
                continue;
            }

            $rSensorDataQ = $this->emsQuery->getQuerySelectSysIntegrationSensorData($complexCodePk, $option, $target);
            $data = $this->query($rSensorDataQ);

            for ($k = 0; $k < count($data); $k++) {
                $d = $data[$k];

                $homeGrpPk = $d['home_grp_pk'];
                $val = $d['val'];
                $allData = json_decode($d['all_data'], true);

                $temps = [
                    'complex_code' => $d['complex_code_pk'],
                    'sensor_sn' => $d['sensor_sn'],
                    'val_date' => $allData['val_date'],
                    'frequency' => 0,
                    'type' => 0,
                    'v_l1' => 0,
                    'v_l2' => 0,
                    'v_l3' => 0,
                    'v_ub' => 0,
                    'vpp_l1' => 0,
                    'vpp_l2' => 0,
                    'vpp_l3' => 0,
                    'vpp_ub' => 0,
                    'i_l1' => 0,
                    'i_l2' => 0,
                    'i_l3' => 0,
                    'watt' => 0,
                    '_var' => 0,
                    'va' => 0,
                    'pf' => 0,
                    'kwh_imp' => 0,
                    'kwh_exp' => 0,
                    'kvarh_imp' => 0,
                    'kvarh_exp' => 0,
                    'error_code' => $d['error_code'],
                ];

                if (in_array($homeGrpPk, ['0M']) === true) {
                    $temps['kwh_imp'] = $val/$divisors[$option];
                }

                if (count($allData) > 0) {
                    $temps['frequency'] = Utility::getInstance()->getCheckValueByAllData('frequency', $allData);
                    $temps['type'] = Utility::getInstance()->getCheckValueByAllData('type', $allData);
                    $temps['v_l1'] = Utility::getInstance()->getCheckValueByAllData('v_l1', $allData);
                    $temps['v_l2'] = Utility::getInstance()->getCheckValueByAllData('v_l2', $allData);
                    $temps['v_l3'] = Utility::getInstance()->getCheckValueByAllData('v_l3', $allData);
                    $temps['v_ub'] = Utility::getInstance()->getCheckValueByAllData('v_ub', $allData);
                    $temps['vpp_l1'] = Utility::getInstance()->getCheckValueByAllData('vpp_l1', $allData);
                    $temps['vpp_l2'] = Utility::getInstance()->getCheckValueByAllData('vpp_l2', $allData);
                    $temps['vpp_l3'] = Utility::getInstance()->getCheckValueByAllData('vpp_l3', $allData);
                    $temps['vpp_ub'] = Utility::getInstance()->getCheckValueByAllData('vpp_ub', $allData);
                    $temps['i_l1'] = Utility::getInstance()->getCheckValueByAllData('i_l1', $allData);
                    $temps['i_l2'] = Utility::getInstance()->getCheckValueByAllData('i_l2', $allData);
                    $temps['i_l3'] = Utility::getInstance()->getCheckValueByAllData('i_l3', $allData);
                    $temps['watt'] = Utility::getInstance()->getCheckValueByAllData('watt', $allData);
                    $temps['_var'] = Utility::getInstance()->getCheckValueByAllData('var', $allData);
                    $temps['va'] = Utility::getInstance()->getCheckValueByAllData('va', $allData);
                    $temps['pf'] = Utility::getInstance()->getCheckValueByAllData('pf', $allData);
                    $temps['kwh_imp'] = Utility::getInstance()->getCheckValueByAllData('kwh_imp', $allData);
                    $temps['kwh_exp'] = Utility::getInstance()->getCheckValueByAllData('kwh_exp', $allData);
                    $temps['kvarh_imp'] = Utility::getInstance()->getCheckValueByAllData('kvarh_imp', $allData);
                    $temps['kvarh_exp'] = Utility::getInstance()->getCheckValueByAllData('kvarh_exp', $allData);
                }

                if ($homeGrpPk !== 'ALL') {
                    // ALL 아닌 것만 전송
                    $fcResult = Utility::getInstance()->curlProcess($url, $method, $httpHeaders, $temps, $options);

                    // 로그남기기
                    Utility::getInstance()->log(
                        'integration',
                        'electric',
                        "http response code: {$fcResult['code']} , {$sensorType} {$d['sensor_sn']} 가 추가 되었습니다."
                    );
                }
            }
        }
    }
}