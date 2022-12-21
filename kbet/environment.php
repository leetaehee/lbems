<?php
include_once './common.php';

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

$company = $tokenData['target'];;
$sensorType = 'environment';

$complexCodePk = $_GET['complex_code'];

if (empty($complexCodePk) === true) {
    $responses['result'] = [
        'result' => 'nok',
        'reason' => ErrComplexCodePk,
    ];
    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

$rSensorDataQ = $emsQuery->getQuerySelectSysIntegrationFinedustData($complexCodePk, $company);
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

    $temps = [
        'sensor_sn' => $data[$i]['sensor_sn'],
        'val_date' => $data[$i]['val_date'],
        'pm25' => $data[$i]['pm25'],
        'temperature' => $data[$i]['temperature'],
        'humidity' => $data[$i]['humidity'],
        'co2' => $data[$i]['co2'],
    ];

    array_push($sensorData, $temps);
}

$responses[$sensorType] = $sensorData;


echo Utility::getInstance()->responseJSON($responses);
exit;