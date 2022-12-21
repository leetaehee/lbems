//----------------------------------------------------------------------------------------------
// Const
//----------------------------------------------------------------------------------------------
const AutologinCookieLabel = 'bems_autologin_';
const AutologinIdCookieLabel = 'bems_autologin_id_';
const AutologinKeyCookieLabel = 'bems_autologin_key_';
const AutologinDeviceKeyCookieLabel = 'bems_device_key_';
const rootExcelFileName = '장애관리 내역';

//----------------------------------------------------------------------------------------------
// Variable&Const
//----------------------------------------------------------------------------------------------s
let buildingManageType = true;

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const rootFineDustLabel = ['좋음', '보통', '나쁨', '매우나쁨'];
const rootFineDustLevelByFrame = [30, 80, 150, 151];
const rootUltraDustLevelByFrame = [15, 35, 75, 76];
const rootColorClasses = ['dustfc1', 'dustfc2', 'dustfc3', 'dustfc4'];

//----------------------------------------------------------------------------------------------
// Paging
//----------------------------------------------------------------------------------------------
const rootPageCount = 5;
const rootViewPageCount = 20;
const rootStartPage = 1;

let totalPage;
let currentPage = 0;

//----------------------------------------------------------------------------------------------
// Maps
//----------------------------------------------------------------------------------------------
let floorKeyMappings = {};

//----------------------------------------------------------------------------------------------
// Progress
//----------------------------------------------------------------------------------------------
let $loadingWindow;
let loadingWindow;