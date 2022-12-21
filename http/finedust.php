<?php
include_once '../setting.php';

include_once '../modules/php/database/Database.php';
include_once '../modules/php/database/DbModule.php';
include_once '../modules/php/database/Mariadb.php';

include_once '../res/string/string_table.php';

use Database\DbModule;

$payloadSize = 38;
$appEui = "00000624";
$deli1 = "remoteCSE-".$appEui;
$deli2 = "/container-";

$result = file_get_contents('php://input');

$xml = simplexml_load_string($result);
$con = (string)$xml->con[0];
$sr  = (string)$xml->sr[0];

// 로그찍기
//error_log(var_export($xml, 1), 3, '/kevin/lbems/logs/finedust/finedust_error.log');

if ($con == null || strlen($con) < $payloadSize) {
    exit;
}

if ($sr == null || strlen($sr) <= 0) {
    exit;
}

$pos1 = strpos($sr, $deli1);
$pos2 = strpos($sr, $deli2);

if ($pos1 === false || $pos2 === false) {
    return;
}

$pos1 = $pos1 + strlen($deli1);
$pos2 = $pos2 - $pos1;

if ($pos2 < 0) {
    return;
}

$deviceEui  = substr($sr, $pos1, $pos2);

$db = new DbModule();

$selQuery = "SELECT `device_eui`
             FROM `bems_sensor_finedust`
             WHERE `device_eui` = '{$deviceEui}'
             AND `fg_use` = 'y'
            ";

$data = $db->querys($selQuery);
$data = $db->getData();

if (empty($data[0]['device_eui']) === false) {
    $temp = round(hexdec(substr($con, 10, 4)) / 10 - 100, 1);
    $humi = round(hexdec(substr($con, 16, 4)) / 10, 1);
    $pm10 = hexdec(substr($con, 22, 4));
    $pm25 = hexdec(substr($con, 28, 4));
    $pm1_0 = hexdec(substr($con, 34, 4));

    $insQuery = "INSERT INTO `bems_meter_finedust`
                 SET `w_date` = NOW(),
                     `pm10` = '{$pm10}',
                     `pm25` = '{$pm25}',
                     `temperature` = '{$temp}',
                     `humidity` = '{$humi}',
                     `pm1_0` = '{$pm1_0}',
                     `device_eui` = '{$deviceEui}'
                ";
    $db->squery($insQuery);

    $valDate = date('YmdHis');

    $uSensorQ = "UPDATE `bems_sensor_finedust` 
                 SET `val_date` = '{$valDate}',
                     `pm10` = '{$pm10}',
                     `pm25` = '{$pm25}',
                     `temperature` = '{$temp}',
                     `humidity` = '{$humi}',
                     `pm1_0` = '{$pm1_0}'
                 WHERE `device_eui` = '{$deviceEui}'
             ";
    $db->squery($uSensorQ);
}