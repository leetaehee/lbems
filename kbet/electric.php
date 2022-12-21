<?php
include_once './common.php';

use EMS_Module\Utility;
use EMS_Module\EMSQuery;
use EMS_Module\Config;

use Database\DbModule;

$sensorData = [];

$responses['result'] = [
    'result' => 'ok',
    'reason' => '-',
];

$emsQuery = new EMSQuery();
$db = new DbModule();

$sensorTypes = Config::SENSOR_TYPES;
$divisors = Config::DIVISOR_VALUES;
$company = $tokenData['target'];

$complexCodePk = $_GET['complex_code'];

$keyName = $sensorTypes[0];

if (empty($complexCodePk) === true) {
    $responses['result'] = [
        'result' => 'nok',
        'reason' => ErrComplexCodePk
    ];
    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

for ($i = 0; $i < count($sensorTypes); $i++) {
    $option = $i;
    if ($option === 1 || $option === 2 || $option === 13) {
        /*
         * 가스,수도,등유 제외
         * 엔텍은 전기만 됨
         */
        continue;
    }

    $solarQuery = '';
    if ($option === 11) {
        /*
         * 태양광 발전량은 /kbet/solar.php에서 조회 가능
         * 태양광 소비량은 옵션에 따라 조회되도록 함.
         */
        if (in_array($complexCodePk, Config::NTEK_SOLAR_OUT_COMPLEX_INFO) === false) {
            continue;
        }

        $solarQuery = Utility::getInstance()->makeWhereClause('sensor', 'inout', 'O');
    }

    $rSensorDataQ = $emsQuery->getQuerySelectSysIntegrationSensorData($complexCodePk, $option,  $company, $solarQuery);
    $db->querys($rSensorDataQ);

    $data = $db->getData();

    $dataCount = count($data);

    if ($dataCount === 0) {
        continue;
    }

    for ($j = 0; $j < $dataCount; $j++) {
        $dataRanges = $data[$j];

        $homeGrpPk = $dataRanges['home_grp_pk'];
        $val = $dataRanges['val'];

        $allData = json_decode($dataRanges['all_data'], true);

        $temps = [
            'sensor_sn' => $dataRanges['sensor_sn'],
            'val_date' => $dataRanges['val_date'],
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
            'var' => 0,
            'va' => 0,
            'pf' => 0,
            'kwh_imp' => 0,
            'kwh_exp' => 0,
            'kvarh_imp' => 0,
            'kvarh_exp' => 0
        ];

        if (in_array($homeGrpPk, ['0M', 'ALL']) === true) {
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
            $temps['var'] = Utility::getInstance()->getCheckValueByAllData('var', $allData);
            $temps['va'] = Utility::getInstance()->getCheckValueByAllData('va', $allData);
            $temps['pf'] = Utility::getInstance()->getCheckValueByAllData('pf', $allData);
            $temps['kwh_imp'] = Utility::getInstance()->getCheckValueByAllData('kwh_imp', $allData);
            $temps['kwh_exp'] = Utility::getInstance()->getCheckValueByAllData('kwh_exp', $allData);
            $temps['kvarh_imp'] = Utility::getInstance()->getCheckValueByAllData('kvarh_imp', $allData);
            $temps['kvarh_exp'] = Utility::getInstance()->getCheckValueByAllData('kvarh_exp', $allData);
        }

        array_push($sensorData, $temps);
    }
}

if (count($sensorData) === 0) {
    $responses['result'] = [
        'result' => 'nok',
        'reason' => ErrNoData
    ];

    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

$responses[$keyName] = $sensorData;

echo Utility::getInstance()->responseJSON($responses);
exit;