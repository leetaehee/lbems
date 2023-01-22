<?php
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
setcookie('samesite_test', '1', 0, '/; samesite=strict');

include_once '../setting.php';
include_once '../libs/php/template.php';

include_once '../modules/php/database/Database.php';
include_once '../modules/php/database/DbModule.php';
include_once '../modules/php/database/Mariadb.php';

include_once '../modules/php/Session.php';

include_once '../EMS_Modules/php/CommonQuery.php';
include_once '../EMS_Modules/php/Config.php';
include_once '../EMS_Modules/php/EMSQuery.php';
include_once '../EMS_Modules/php/Menu.php';
include_once '../EMS_Modules/php/Utility.php';

include_once '../res/string/string_table.php';

use Module\Session;
use EMS_Module\Utility;
use EMS_Module\Menu;

define('DefaultSelected', 0);
define('FrameDirectory', 'frame/');
define('UnLoginPage', 'frame_unlogin.html');
define('LoginPage', 'login/login.html');
define('RequestUrl', '../http/index.php');

$sessionModule = new Session();
$gComplexCodePk = $_SESSION['ss_complex_pk'];
$gLevel = $_SESSION['ss_level'];

// 환경정보 조회
if (file_exists($EnvFilePath) == false) {
    echo ErrEnvFilePathExist;
    exit;
}
$ENVOptionData = parse_ini_file($EnvFilePath);
if (count($ENVOptionData) === 0) {
    echo ErrEnvFileItemEmpty;
    exit;
}

$page = null;
$selected = null;
$group = null;

if (isset($_GET['page']) === true) {
    $page = Utility::getInstance()->removeXSS($_GET['page']);
}

if (isset($_GET['menu']) === true) {
    $selected = Utility::getInstance()->removeXSS($_GET['menu']);
}

if (isset($_GET['group']) === true) {
    $group = Utility::getInstance()->removeXSS($_GET['group']);
}

$gDBName = $ENVOptionData['DB_SID'];
$gIsDev = (int)$ENVOptionData['IS_DEV'];

$menuModule = new Menu();

$requestUrl = RequestUrl;
$unLoginPage = $unLoginPages[$page];

if ($sessionModule->isLogin(false) === false && empty($unLoginPage) === false) {
    if (in_array($unLoginPage, $authPage) === true
        && count($_SESSION['tmp_session']) === 0) {
        header('location: ./');
    }
    $tpl['frame'] = new template(FrameDirectory . UnLoginPage);
    $tpl['body'] = new template($unLoginPage);
    $body = $tpl['body']->Display(true);

    $tpl['frame']->set('body, requestUrl');
    $tpl['frame']->Display();
    exit;
}

if ($sessionModule->isLogin(false) == false) {
	$tpl['frame'] = new template(LoginPage);
	$tpl['frame']->Set('requestUrl');
	$tpl['frame']->Display();
	exit;
}

// 개발서버에서 다크모드 테스트 할 때 사용
/*
    if ($gIsDev === 1
        && $ENVOptionData['DB_HOST'] === $dH
        && $gComplexCodePk === $templateSite) {
        $settings[$templateSite]['skin_type'] = 'dark';
        $settings[$templateSite]['frame_file'] = 'frame_dark.html';
    }
*/

$complexSettings = $settings[$gComplexCodePk];

$gBuildingName = $complexSettings['building_name'];
$gBuildingFeCode = $complexSettings['building_front_code'];
$frameFile = $complexSettings['frame_file'];
$gSkinType = $complexSettings['skin_type'];

if (empty($gBuildingFeCode) === true) {
    echo ErrEnvFileItemDefine;
    exit;
}

if (empty($frameFile) === true) {
    // 프레임 분기처리
    $frameFile = $settings[$defaultComplexCodePk]['frame_file'];
}
if (empty($frameFile) === false) {
    $frameFile = FrameDirectory . $frameFile;
}

$isSuper = true;

$tpl['frame'] = new template($frameFile);

if ($selected == null || $selected < 0) {
	$selected = DefaultSelected;
}

if ($gLevel <= 70) {
	$isSuper = false;
}

$menuData = $menuModule->getMenu($gComplexCodePk, $gDBName, $gSkinType, $selected, $gLevel);
$menu = $menuData['menu_tag'];

// db에서 조회 한 업체명으로 최신화
$gBuildingName = $menuData['complex_name'];

// 사이트 오픈일자, 년도 추출
$gServiceStartDate = $menuData['service_start_date'];
$gServiceStartYm = date('Y', strtotime($gServiceStartDate));

define('DefaultPage', $menuData['default_page']);
define('DefaultSuperPage', $menuData['default_super_page']);

if ($page == null || $page == '') {
	if ($isSuper == true) {
		$page = DefaultSuperPage;
	} else {
		$page = DefaultPage;
	}
}

$tpl['body'] = new template($page);

if ($tpl['body']) {
	$body = $tpl['body']->Display(true);

	$tpl['frame']->Set('
	    body, 
	    page, 
	    requestUrl, 
	    group, 
	    menu, 
	    selected, 
	    gComplexCodePk, 
	    gIsDev, 
	    gBuildingFeCode, 
	    gBuildingName, 
	    gSkinType,
	    gServiceStartDate,
	    gServiceStartYm
	');
}

$tpl['frame']->Display();