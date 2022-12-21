<?php
include_once './common.php';

use EMS_Module\Config;
use EMS_Module\Utility;
use EMS_Module\EMSQuery;

use Database\DbModule;

$sensorData = [];

$responses['result'] = [
    'result' => 'ok',
    'reason' => '-'
];

$emsQuery = new EMSQuery();
$db = new DbModule();

$option = 11;
$company = $tokenData['target'];

$divisors = Config::DIVISOR_VALUES;
$sensorTypes = Config::SENSOR_TYPES;
$divisor = $divisors[$option];
$sensorType = $sensorTypes[$option];

$complexCodePk = $_GET['complex_code'];

if (empty($complexCodePk) === true) {
    $responses['result'] = [
        'result' => 'nok',
        'reason' => ErrComplexCodePk,
    ];
    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

$solarQuery = Utility::getInstance()->makeWhereClause('sensor', 'inout', 'I');

$rSensorDataQ = $emsQuery->getQuerySelectSysIntegrationSensorData($complexCodePk, $option,  $company, $solarQuery);
$db->querys($rSensorDataQ);

$data = $db->getData();

if (count($data) === 0) {
    $responses['result'] = [
        'result' => 'nok',
        'reason'=> ErrNoData,
    ];
    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

for ($i = 0; $i < count($data); $i++) {

    $allData = json_decode($data[$i]['all_data'], true);

    $temps = [
        'sensor_sn' => $data[$i]['sensor_sn'],
        'val_date' => $data[$i]['val_date'],
        'val' => $data[$i]['val']/$divisor,
    ];

    array_push($sensorData, $temps);
}

$responses[$sensorType] = $sensorData;

echo Utility::getInstance()->responseJSON($responses);
exit;