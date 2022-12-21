<?php
include_once './common.php';

use EMS_Module\Utility;

$jwt = null;
$postData = $api->postData();

$id = $postData['id'];
$pw = $postData['pw'];

if (empty($id) === true || empty($pw) === true) {
    echo Utility::getInstance()->responseJSON([
        'result' => 'nok',
        'reason' => ErrAPIStandard
    ]);
    exit;
}

// 로그인 정보 확인
$loginResult = $api->loginProcess($id, $pw);
if ($loginResult['result'] === 'nok') {
    echo Utility::getInstance()->responseJSON($loginResult);
    exit;
}

echo Utility::getInstance()->responseJSON([
    'result' => $loginResult['result'],
    'reason' => $loginResult['reason'],
]);

exit;