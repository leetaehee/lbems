<?php
header('Content-Type: application/json; charset=UTF-8');

define('API_NAME', 'KBET');

$documentRoot = $_SERVER['DOCUMENT_ROOT'];

include_once $documentRoot . '/setting.php';

include_once $documentRoot . '/modules/php/database/Database.php';
include_once $documentRoot . '/modules/php/database/DbModule.php';
include_once $documentRoot . '/modules/php/database/Mariadb.php';

include_once $documentRoot . '/EMS_Modules/php/CommonQuery.php';
include_once $documentRoot . '/EMS_Modules/php/Config.php';
include_once $documentRoot . '/EMS_Modules/php/EMSQuery.php';
include_once $documentRoot . '/EMS_Modules/php/Menu.php';
include_once $documentRoot . '/EMS_Modules/php/Utility.php';
include_once $documentRoot . '/EMS_Modules/php/ApiManager.php';
include_once $documentRoot . '/EMS_Modules/php/JWT.php';
include_once $documentRoot . '/EMS_Modules/php/SensorInterface.php';
include_once $documentRoot . '/EMS_Modules/php/ControlFactory.php';
include_once $documentRoot . '/EMS_Modules/php/AirConditioner.php';
include_once $documentRoot . '/EMS_Modules/php/LgAirConditioner.php';
include_once $documentRoot . '/EMS_Modules/php/SamsungAirConditioner.php';

include_once $documentRoot . '/http/SensorManager.php';
include_once $documentRoot . '/http/sensor_include.php';

include_once $documentRoot . '/res/string/string_table.php';

use EMS_Modules\ApiManager;
use EMS_Module\Config;
use EMS_Module\JWT;
use EMS_Module\Utility;

$devOptions = parse_ini_file($documentRoot . '/.env');
$domain = $devOptions['DOMAIN'];
$secretKey = $devOptions['SECRET_KEY'];
$isDev = (int)$devOptions['IS_DEV'];

$cookieSettings = Config::COOKIE_SETTINGS[API_NAME];
$cookieName = $cookieSettings['login_cookie_name'];
$path = $cookieSettings['path'];

$apiRules = $KBET_API_INFO;
$logFolder = 'api';

$isLoginPage = $_SERVER['SCRIPT_NAME'] === '/kbet/login.php' ? true : false;

$api = new ApiManager($cookieName, $path, $domain, $secretKey, $apiRules);
$api->setTarget(API_NAME);
$api->saveApiCallLog();

$result = $api->checkStatusData();
if ($result['result'] === 'nok') {
    echo Utility::getInstance()->responseJSON($result);
    exit;
}

if ($isLoginPage === false) {
    $tokenData = JWT::getInstance()->getCheckLoginStatus($secretKey);
    if ($tokenData['result'] === false) {
        echo Utility::getInstance()->responseJSON($tokenData);
        exit;
    }
}