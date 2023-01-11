<?php
include_once '../../common.php';

use EMS_Module\EMSQuery;
use EMS_Module\Config;
use EMS_Module\ControlFactory;
use EMS_Module\Utility;

use Database\DbModule;

$emsQuery = new EMSQuery();
$db = new DbModule();

$airConditionInfo = Config::COMPLEX_AIR_CONDITIONER_INFO;
$airConditionerFormats = Config::AIR_CONDITIONER_FORMAT;

$responses['result'] = [
    'result' => 'ok',
    'reason' => '-',
];

$target = $tokenData['target'];

$postData = $api->postData();

$complexCodePk = $postData['complex_code'];
$company = $postData['company'];
$ehpId = $postData['ehp_id'];
$mode = $postData['mode'];
$value = $postData['value'];

$controlType = 'process';

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

if ($ehpId === '') {
    // 아이디 빈값 체크
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrApiAirConditionalId,
        ]
    ]);
    exit;
}

if (empty($mode) === true) {
    // mode 빈 값 체크
    echo Utility::getInstance()->responseJSON([
       'result' => [
           'result' => 'nok',
           'reason' => ErrApiControlModeEmpty,
       ]
    ]);
    exit;
}

if (empty($value) === true) {
    // value 빈 값 체크
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrApiControlValueEmpty,
        ]
    ]);
    exit;
}

if ($mode === 'power'
    && empty($airConditionerFormats['power'][$value]) === true) {
    // value 빈 값 체크
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrNoData,
        ]
    ]);
    exit;
}

$statusType = $mode === 'power' ? 'power_etc' : 'operation_etc';

// 디바이스 정보로 변경
switch ($mode) {
    case 'power' :
        $value = $value === 'on' ? false : true;
        break;
    case 'fan_speed' :
        $value = $airConditionerFormats['fan_speed'][$value];
        break;
    case 'op_mode' :
        $value = $airConditionerFormats['op_mode'][$value];
        break;
}

$controlOptions = [
    'id' => $ehpId,
    'status_type' => $statusType,
    'parameter' => [
        'operation' => $mode,
        'status' => (string)$value,
    ],
];

$controlFactory = new ControlFactory();

$result = $controlFactory->processControl($complexCodePk, $company, $controlType, $controlOptions);
if ($result === '') {
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrNoData,
        ]
    ]);
    exit;
}

if ($result['result'] === 'False') {
    echo Utility::getInstance()->responseJSON([
        'result' => [
            'result' => 'nok',
            'reason' => ErrApiControlChange,
        ]
    ]);
    exit;
}

$responses['response'] = $result['result'];

echo Utility::getInstance()->responseJSON($responses);
exit;
