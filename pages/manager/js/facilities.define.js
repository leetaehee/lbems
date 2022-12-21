//-----------------------------------------------------------------------------------------------------
// Ajax
//-----------------------------------------------------------------------------------------------------
const REQUESTER = 'manager';
const LIST_COMMAND = 'equipment';
const SET_COMMAND = 'equipment_set';
const INFO_COMMAND = 'equipment_info';

//-----------------------------------------------------------------------------------------------------
// Variables & Const
//-----------------------------------------------------------------------------------------------------
const energyType = 'facility';
const defaultEmptyValue = '';

const defaultMenuGroupName = '관리자 설정'
const settingPageIcon = '../res/images/icon/icon_set2.png';
let isDisabledBuildingSelectBox = true;
let isDisabledAnomalyColumn = false;

const date = new Date();

//-----------------------------------------------------------------------------------------------------
// page
//-----------------------------------------------------------------------------------------------------
const pageCount = 5;
const viewPageCount = 10;
const startPage = 1;

//-----------------------------------------------------------------------------------------------------
// inputs/selects
//-----------------------------------------------------------------------------------------------------
const $formEnergyManage = $("#form_energy_manage");
const $formSubmitId	= $("#form_popup_setting");
const $buildingType = $("#building_type");
const $startDate = $("#start_date");
const $endDate = $("#end_date");
const $fgUse = $("#fg_use");
const $energyType = $("#energy_type");
const $allChecked = $("#all_checked");
const $popupBuildingType = $("#popup_building_type");
const $popupEnergyType = $("#popup_energy_type");
const $popupSelectAptFloor = $("#popup_select_apt_floor");
const $popupSelectAptType = $("#popup_select_apt_type");
const $popupSelectAptDong = $("#popup_select_apt_dong");
const $popupSelectAptHome = $("#popup_select_apt_home");
const $popupSensorSn = $("#popup_sensor_sn");
const $popupInstalledDate = $("#popup_installed_date");
const $popupDetailSpec = $("#popup_detail_spec");
const $popupManageLevel = $("#popup_manage_level");
const $popupCheckPeriod = $("#popup_check_period");
const $popupLastestCheckDate = $("#popup_lastest_check_date");
const $popupReplaceDate = $("#popup_replace_date");
const $popupFgUse = $("#popup_fg_use");

//----------------------------------------------------------------------------------------------
// div
//----------------------------------------------------------------------------------------------
const $divBuildingSelectBox = $("#div_building_select_box");

//----------------------------------------------------------------------------------------------
// img
//----------------------------------------------------------------------------------------------
const $loginLogPageIcon = $("#login_log_page_icon");

//----------------------------------------------------------------------------------------------
// ETC
//----------------------------------------------------------------------------------------------
const $menuGroupSelector = $(".menu_group_name");

//----------------------------------------------------------------------------------------------
// Validate
//----------------------------------------------------------------------------------------------
const VALIDATE_BUILDING_SELECT_EMPTY = '건물을 선택하세요.';
const VALIDATE_DATE_INPUT_EMPTY = '날짜를 선택하세요.';
const VALIDATE_ENERGY_TYPE_SELECT_EMPTY = '에너지원을 선택하세요.';
const VALIDATE_SENSOR_NO_RULE_VIOLATE = 'S/N은 영소문자, 숫자, 하이픈(_) 으로 구성되어야 합니다.';
const VALIDATE_SENSOR_NO_INPUT_EMPTY= 'S/N을 입력하세요.';
const VALIDATE_FLOOR_ALL_SELECT = '층 선택 시 전체는 케빈랩에 문의하세요.';
const VALIDATE_APT_TYPE_SELECT_EMPTY = '타입을 선택하세요.';
const VALIDATE_APT_DONG_SELECT_EMPTY = '동을 선택하세요.';
const VALIDATE_APT_HO_SELECT_EMPTY = '호를 선택하세요';
//const VALIDATE_INSTALL_DATE_DATE_EMPTY = '구입일을 입력하세요.';
//const VALIDATE_DETAIL_SPEC_INPUT_EMPTY = '상세스펙을 입력하세요.';
//const VALIDATE_MANAGE_LEVEL_INPUT_EMPTY = '관리 등급을 입력하세요.';
//const VALIDATE_CHECK_PERIOD_DATE_EMPTY = '검교정 주기를 입력하세요.';
//const VALIDATE_LATEST_CHECK_DATE_EMPTY = '최신 검교정 일자를 입력하세요.';
//const VALIDATE_REPLACE_DATE_EMPTY = '교체 일자를 입력하세요.';
const VALIDATE_HOME_INFO_NOT_RIGHT = '동, 호, 층 정보를 확인하세요.';
const VALIDATE_EQUIPMENT_EXIST = '장비가 현재 등록 되어 있습니다.';
const VALIDATE_SENSOR_NO_EXIST = '센서 번호가 이미 등록 되었습니다.';

//-----------------------------------------------------------------------------------------------------
// Buttons
//-----------------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const $btnEnergyEnroll = $("#btnEnergyEnroll");
const $btnPopupSave = $("#btn_popup_save");
const $btnPopupClose = $("#btn_popup_close");
const $btnEquipmentFirstPage = $("#btn_equipment_first_page");
const $btnEquipmentPrevPage = $("#btn_equipment_prev_page");
const $btnEquipmentNextPage = $("#btn_equipment_next_page");
const $btnEquipmentLastPage = $("#btn_equipment_last_page");

//-----------------------------------------------------------------------------------------------------
// tables
//-----------------------------------------------------------------------------------------------------
const $tbodyEquipment = $("#tbody_equipment");
const $aptFloorName = $("#apt_floor_name");
const $aptTypeName = $("#apt_type_name")
const $aptDongName = $("#apt_dong_name");
const $aptHoName = $("#apt_ho_name");
const $trSuperSection = $("#tr-super-section");

//-----------------------------------------------------------------------------------------------------
// Popup
//-----------------------------------------------------------------------------------------------------
const $formPopupSetting = $(".form_popup_setting");

let popupStandardParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};

let standardFormPopup = module.popup(popupStandardParams);