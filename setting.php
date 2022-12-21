<?php
/**
 * 주의사항)
 * DB 정보, 비밀번호, api 비밀번호 등 고유정보는 setting.php 에서 정의 금지
 */

// 로그 표현 방식
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECAT);

// define 정리
define('SERVER_DIR', $_SERVER['DOCUMENT_ROOT'] . '/');

define('TOP_MODULES_DIR', SERVER_DIR . 'modules/');
define('TOP_MODULES_EMS_DIR', SERVER_DIR . 'EMS_Modules/');
define('TOP_RES_DIR', SERVER_DIR . 'res/');
define('TOP_HTTP_DIR', SERVER_DIR . 'http/');
define('TOP_LIB_DIR', SERVER_DIR . 'libs/');
define('MaxTimeout', 60000);

// .env 파일 경로
$EnvFilePath = '../.env';

// 기본 건물 코드
$defaultComplexCodePk = '2001';

// 개발 정보
$dH = '211.251.237.14';

// 템플릿 테스트 사이트
$templateSite = '2002';

// 건물별 설정 정보
$settings = [
    '9999' => [
        'building_front_code' => 'B_9999',
        'building_name' => '테스트건물',
        'skin_type' => 'dark',
        'frame_file' => 'frame_dark.html',
    ],
    '2001' => [
        'building_front_code' => 'B_2001',
        'building_name' => '무등산국립공원',
        'skin_type' => 'default',
        'frame_file' => 'frame_mdmt.html',
    ],
    '2002' => [
        'building_front_code' => 'B_2002',
        'building_name' => '태백산국립공원',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2003' => [
        'building_front_code' => 'B_2003',
        'building_name' => '빛사랑 어린이집',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2004' => [
        'building_front_code' => 'B_2004',
        'building_name' => '삼원빌딩',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2005' => [
        'building_front_code' => 'B_2005',
        'building_name' => '다도해해상국립공원',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2006' => [
        'building_front_code' => 'B_2006',
        'building_name' => '전주 우전초등학교',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2007' => [
        'building_front_code' => 'B_2007',
        'building_name' => '방배동 근린생활시설',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2008' => [
        'building_front_code' => 'B_2008',
        'building_name' => '전주 서일초등학교',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2010' => [
        'building_front_code' => 'B_2010',
        'building_name' => '김해시 행정복지센터',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2011' => [
        'building_front_code' => 'B_2011',
        'building_name' => '국방전진교육원',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2012' => [
        'building_front_code' => 'B_2012',
        'building_name' => '김해시 소상공인 물류센터',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2013' => [
        'building_front_code' => 'B_2013',
        'building_name' => '장애인 내일키움 직업교육센터',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2014' => [
        'building_front_code' => 'B_2014',
        'building_name' => '새마을중앙연수원',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2017' => [
        'building_front_code' => 'B_2017',
        'building_name' => '북한산국립공원',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2018' => [
        'building_front_code' => 'B_2018',
        'building_name' => '무등산원효분소',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '2019' => [
        'building_front_code' => 'B_2019',
        'building_name' => '한국식품연구원',
        'skin_type' => 'default',
        'frame_file' => 'frame.html',
    ],
    '3001' => [
        'building_front_code' => 'B_3001',
        'building_name' => '에스디아이',
        'skin_type' => 'default',
        'frame_file' => 'frame_factory.html',
    ],
];

// 로그인을 하지 않아도 되는 페이지
$unLoginPages = [
    'login/authorization.html' => './login/account_authorization.html',
    'login/password_change.html' => './login/password_change.html',
    'login/request_change_password.html' => './login/request_change_password.html',
];

// 인증(이메일,휴대폰, 로그인)을 통과 후 보여지는 페이지
$authPage = [
    './login/password_change.html',
    './login/request_change_password.html',
];

// 데이터 연계 API 정보
$KBET_API_INFO = [
    '/kbet/login.php' => [
        'type' => 'login',
        'method' => 'POST',
        'content-type' => 'application/x-www-form-urlencoded',
        'is_menu_open' => true,
    ],
    '/kbet/electric.php' => [
        'type' => 'electric',
        'method' => 'GET',
        'content-type' => 'application/json',
        'is_menu_open' => true,
    ],
    '/kbet/solar.php' => [
        'type' => 'solar',
        'method' => 'GET',
        'content-type' => 'application/json',
        'is_menu_open' => true,
    ],
    '/kbet/gas.php' => [
        'type' => 'gas',
        'method' => 'GET',
        'content-type' => 'application/json',
        'is_menu_open' => true,
    ],
    '/kbet/environment.php' => [
        'type' => 'environment',
        'method' => 'GET',
        'content-type' => 'application/json',
        'is_menu_open' => true,
    ],
    '/kbet/sync_data.php' => [
        'type' => 'collect',
        'method' => 'POST',
        'content-type' => 'application/json',
        'is_menu_open' => true,
    ],
    '/kbet/control/ehp/ehp_status.php' => [
        'type' => 'control',
        'method' => 'GET',
        'content-type' => 'application/json',
        'is_menu_open' => true,
    ],
    '/kbet/control/ehp/ehp_control.php' => [
        'type' => 'control',
        'method' => 'POST',
        'content-type' => 'application/x-www-form-urlencoded',
        'is_menu_open' => true,
    ],
];