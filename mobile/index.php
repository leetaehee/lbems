<?php
include_once '../setting.php';

include_once TOP_MODULES_DIR . 'php/database/Database.php';
include_once TOP_MODULES_DIR . 'php/database/DbModule.php';
include_once TOP_MODULES_DIR . 'php/database/Mariadb.php';
include_once TOP_MODULES_DIR . 'php/Session.php';

include_once TOP_MODULES_EMS_DIR . 'php/CommonQuery.php';
include_once TOP_MODULES_EMS_DIR . 'php/Config.php';
include_once TOP_MODULES_EMS_DIR . 'php/SensorInterface.php';
include_once TOP_MODULES_EMS_DIR . 'php/MobileQuery.php';
include_once TOP_MODULES_EMS_DIR . 'php/EMSQuery.php';
include_once TOP_MODULES_EMS_DIR . 'php/MobileMenu.php';
include_once TOP_MODULES_EMS_DIR . 'php/Utility.php';

include_once TOP_HTTP_DIR . 'SensorManager.php';
include_once TOP_RES_DIR . 'string/string_table.php';

include_once '../libs/php/template.php';
include_once '../http/sensor_include.php';

define('FramePage', './frame/frame.html'); // 프레임
define('LoginPage', './login/login.html'); // 로그인
define('RequestUrl', '../http/index.php');
define('EnvFilePath', '../.env');

$sessionModule = new Module\Session();
$gComplexCodePk = $_SESSION['mb_ss_complex_pk'];

$requestUrl = RequestUrl;

// 환경정보 조회
if (file_exists(EnvFilePath) == false) {
    echo ErrEnvFilePathExist;
    exit;
}
$ENVOptionData = parse_ini_file(EnvFilePath);
if (count($ENVOptionData) === 0) {
    echo ErrEnvFileItemEmpty;
    exit;
}

// setting.php에서 데이터 조회
$complexSettings = $settings[$gComplexCodePk];
$frameFile = $complexSettings['frame_file'];
$gBuildingName = $complexSettings['building_name'];
$gSkinType = $complexSettings['skin_type'];
$gBuildingFeCode = $complexSettings['building_front_code'];

$gIsDev = (int)$ENVOptionData['IS_DEV'];

//-------------------------------------------------------------------------------------
// 옵션
//-------------------------------------------------------------------------------------
$selectedMenu = '';
$selectedMenu = isset($_GET['menu']) === true ? $_GET['menu'] : 'home';

//--------------------------------------------------------------------------------------
// FramePage 를 사용하지 않는 경우 정의
//--------------------------------------------------------------------------------------
$nonFrames = [
];

// 템플릿 사용여부
$isUseTemplate = true;

$noneFramePage = $nonFrames[$selectedMenu];
if (empty($noneFramePage) === false) {
    $isUseTemplate = false;
}

//--------------------------------------------------------------------------------------
// 로그인
//--------------------------------------------------------------------------------------
$isIpCheck = false;
if($sessionModule->isLogin($isIpCheck) === false) {
    if (empty($noneFramePage) === true) {
        $noneFramePage = LoginPage;
    }

    $tpl['frame'] = new template($noneFramePage);
    $tpl['frame']->Set('body, requestUrl');
    $tpl['frame']->Display();
    exit;
}

//-------------------------------------------------------------------------------------
// 페이지 설정
//-------------------------------------------------------------------------------------
$menuModule = new EMS_Modules\MobileMenu();
$menuModule->setSensorObj($gComplexCodePk);

$pages = $menuModule->getMenu();

//-------------------------------------------------------------------------------------
// 페이지 명칭에 따라서 디자인 호출.
//-------------------------------------------------------------------------------------
if (isset($pages[$selectedMenu])) {
	$page = $pages[$selectedMenu];
}

if ($isUseTemplate === false) {
    $tpl['frame'] = new template($noneFramePage);
    $tpl['frame']->Set('requestUrl');
    $tpl['frame']->Display();
    exit;
}

$tpl['frame'] = new template(FramePage);
$tpl['body'] = new template($page);
$tpl['body']->Set('');

if ($tpl['body']) {
    $body = $tpl['body']->Display(true);
    $tpl['frame'] -> Set(
        'body, 
                  selectedMenu, 
                  requestUrl, 
                  gComplexCodePk, 
                  gBuildingName, 
                  gBuildingFeCode, 
                  gSkinType, 
                  gIsDev'
    );
}

$tpl['frame']->Display();