<?php
include_once './common.php';

use EMS_Module\Utility;
use EMS_Module\EMSQuery;
use EMS_Module\Config;

use Database\DbModule;

$responses = [
    'result' => 'ok',
    'reason' => '-',
];

$insertColumns = [];

$insertColumnStr = '';
$insertValueStr = '';

$emsQuery = new EMSQuery();
$db = new DbModule();

$logFileName = 'sync_data';
$collectColumnName = 'collect_type';

$sensorTypes = Config::SENSOR_TYPES;
$sensorTypeNos = Config::SENSOR_TYPE_NO;

$requestData = $api->requestData();

// 로그저장
$logTraceNo = Utility::getInstance()->log($logFolder, $logFileName, json_encode($requestData));

if (count($requestData) === 0) {
    $responses = [
        'result' => 'nok',
        'reason' => ErrAPIStandard,
    ];
    exit;
}

$requestData = $requestData['sync_data'];

if (in_array($requestData['type'], $sensorTypes) === false) {
    $responses = [
        'result' => 'nok',
        'reason' => ErrApiSyncData,
    ];
    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

$type = $requestData['type'];
$data = $requestData['data'];

$option = $sensorTypeNos[$type];

if (count($data) === 0) {
    $responses = [
        'result' => 'nok',
        'reason' => ErrApiSyncDataType,
    ];
    echo Utility::getInstance()->responseJSON($responses);
    exit;
}

$columns = array_keys($data[0]);
$insertColumns = $columns;

array_push($insertColumns, $collectColumnName);

for ($i = 0; $i < count($data); $i++) {
    // 수집데이터타입 지정 => 현재는 "하드코딩"..
    // 수집업체 복수 일 경우 파라미터를 동적 개선 필요.
    // 테이블 검토 필요 - bems_api_integration_info 외 별도로 수집 단지 테이블이 필요한지..??
    $data[$i]['collect_type'] = Config::COLLECT_TYPES['edge'];
    $data[$i]['error_code'] = (int)$data[$i]['error_code'];

    $values = array_values($data[$i]);

    $temps = Utility::getInstance()->addCharacter($values);
    $insertValueStr .= "(".Utility::getInstance()->makeImplodeString($temps) . ")";
    if ($i !== count($data)-1) {
        // 마지막은 , 를 붙이지 않는다..
        $insertValueStr .= ",";
    }
}

$uMultiRawQ = $emsQuery->getQueryInsertOrUpdateMulti($option,  $insertColumns, $columns, $insertValueStr);
$result = $db->squery($uMultiRawQ);

if ($result < 1) {
    $responses = [
        'result' => 'nok',
        'reason' => "DB 입력 실패({$logTraceNo})",
    ];
}

echo Utility::getInstance()->responseJSON($responses);
exit;