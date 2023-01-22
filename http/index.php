<?php
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
setcookie('samesite-test', '1', 0, '/; samesite=strict');

/** setting 정보 */
include_once '../setting.php';

/** 쿼리 */
include_once '../EMS_Modules/php/CommonQuery.php';
include_once '../EMS_Modules/php/EMSQuery.php';
include_once '../EMS_Modules/php/MobileQuery.php';
include_once '../EMS_Modules/php/MigrationQuery.php';

/** 데이터베이스 */
include_once '../modules/php/database/Database.php';
include_once '../modules/php/database/DbModule.php';
include_once '../modules/php/database/Mariadb.php';

/** 세션 */
include_once '../modules/php/Session.php';

/** EMS 공용모듈 */
include_once '../EMS_Modules/php/Utility.php';
include_once '../EMS_Modules/php/Fee.php';
include_once '../EMS_Modules/php/Usage.php';
include_once '../EMS_Modules/php/Indication.php';
include_once '../EMS_Modules/php/SensorInterface.php';
include_once '../EMS_Modules/php/Finedust.php';
include_once '../EMS_Modules/php/ElectricPriceQuery.php';
include_once '../EMS_Modules/php/Config.php';
include_once '../EMS_Modules/php/Efficiency.php';
include_once '../EMS_Modules/php/MobileMenu.php';
include_once '../EMS_Modules/php/ControlFactory.php';
include_once '../EMS_Modules/php/AirConditioner.php';
include_once '../EMS_Modules/php/LgAirConditioner.php';
include_once '../EMS_Modules/php/SamsungAirConditioner.php';
include_once '../EMS_Modules/php/TestSampleMap.php';

/** 공용모듈 */
include_once '../modules/php/CacheInterface.php';
include_once '../modules/php/FileCache.php';
include_once '../modules/php/Excel.php';
include_once '../modules/php/Mail.php';

/** libs */
include_once '../res/string/string_table.php';
include_once '../libs/php/PHPExcel/Classes/PHPExcel.php';
include_once '../libs/php/PHPMailer/PHPMailerAutoload.php';
include_once '../libs/php/Fee/ElectricMariaDB.php';
include_once '../libs/php/Fee/ElectricPrice.php';

/** 모바일 */
include_once '../EMS_Modules/php/MobileQuery.php';

/** command, parser,  sensor */
include_once 'parser_include.php';
include_once 'command_include.php';
include_once 'sensor_include.php';
include_once 'HttpManager.php';
include_once 'SensorManager.php';

use Module\Session;
use EMS_Module\Utility;

$conPrefix = Utility::getInstance()->getConnectionMethodPrefix();

$session = new Session();
$gComplexCodePk = $_SESSION[$conPrefix . 'ss_complex_pk'];

$ret = [
	'result' => false,
	'data' => [],
	'msg' => ''
];

$requester = '';
$request = '';
$params = [];
$grp = 'all';
$formSubmit = '';

if (isset($_POST['requester'])) {
	$requester = Utility::getInstance()->removeXSS($_POST['requester']);
}

if (isset($_POST['request'])) {
	$request = Utility::getInstance()->removeXSS($_POST['request']);
}

if (isset($_POST['params'])) {
	$params = json_decode($_POST['params'], true);
	$params = is_null($params) === true ? [] : $params; // null 처리
}

if (isset($_POST['form_submit'])) {
	$formSubmit = $_POST['form_submit'];
}

$manager = new Http\HttpManager();
$result = $manager->parse($requester, $request, $params);

/** 
 * 엑셀 다운로드 시 ajax가 아니라 form submit으로 요청.
 * - $_POST['form_submit'] 변수 체크
 */
if ($formSubmit === 'excel_download') {
	if ($result === false) {
        $errAccessMsg = ManualNoAccess;
		echo "<script>
                alert('{$errAccessMsg}'); 
                document.location.href='../pages/index.php'
              </script>";
	}
	exit;
}

if ($result == false) {
	$ret['msg'] = $manager->getMessage();
	$ret['result'] = false;
} else {
	$ret['result'] = true;
	$ret['data'] = $manager->getData();
}

exit(Utility::getInstance()->responseJSON($ret));