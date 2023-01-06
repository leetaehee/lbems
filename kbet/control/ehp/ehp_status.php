<?php
include_once '../../common.php';

use EMS_Module\Utility;
use EMS_Module\Config;
use EMS_Module\ControlFactory;
use EMS_Module\EMSQuery;

use Database\DbModule;

$emsQuery = new EMSQuery();
$db = new DbModule();


$responses['result'] = [
    'result' => 'ok',
    'reason' => '-',
];

$target = $tokenData['target'];

$complexCodePk = $_GET['complex_code'];
$company = $_GET['company'];
$statusType = $_GET['status_type'];
$ehpId = $_GET['ehp_id'];

$controlType = 'read';

$airConditionInfo = Config::COMPLEX_AIR_CONDITIONER_INFO;
$settingCompany = $airConditionInfo[$complexCodePk];

if (empty($complexCodePk) === true) {
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrComplexCodePk
        ]
    ]);
    exit;
}
$rIntegrationChkQ = $emsQuery->getQuerySelectSystemIntegrationCheck($complexCodePk, $target);
$db->querys($rIntegrationChkQ);

$data = $db->getData();
if (count($data) < 1) {
    // 연계업체가 맞는지 확인
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrNoData,
        ]
    ]);
    exit;
}

if (empty($company) === true
    || ($company !== $settingCompany)) {
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrAirConditionerCompany,
        ]
    ]);
    exit;
}

if ($company === 'lg'
    && in_array($statusType, Config::AIR_CONDITIONER_STATUS_TYPE) === false) {
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrApiControlStatusType,
        ]
    ]);
    exit;
}

if ($company === 'lg' && $ehpId === '') {
    // 아이디 빈값 체크
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrApiAirConditionalId,
        ]
    ]);
    exit;
}

$controlOptions = [
    'id' => $ehpId,
    'status_type' => $statusType,
];

$controlFactory = new ControlFactory($company);

$result = $controlFactory->processControl($complexCodePk, $controlType, $controlOptions);
if ($result === '') {
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrNoData,
        ]
    ]);
    exit;
}

$responses['response'] = $result;

echo Utility::getInstance()->responseJSON($responses);
exit;