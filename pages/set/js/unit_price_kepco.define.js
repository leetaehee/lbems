//-----------------------------------------------------------------------------------------------------
// Ajax
//-----------------------------------------------------------------------------------------------------
const requester = 'set';
const command = 'unit_price_kepco';
const kepcoCommand = 'unit_price_kepco_info';
const setKepcoCommand = 'unit_price_kepco_set';

//-----------------------------------------------------------------------------------------------------
// Variable & Const
//-----------------------------------------------------------------------------------------------------
const defaultElectricType = 'N';
const defaultIsUseManage = true;
const defaultMenuGroupName = '설정'
const defaultEmptyValue = '';
const defaultZeroValue = 0;
const settingPageIcon = '../res/images/icon/icon_manager.png';

const PRICE_TYPES = {
    select1 : 'select1', select2 : 'select2', select3 : 'select3',
    type1 : 'type1', type2 : 'type2', type3 : 'type3',
    low : 'L', high : 'H', high1 : 'H1', high2 : 'H2', high3 : 'H3',
    normal : 'N', industry : 'S'
};

const SEASON_TYPES = {
    'S' : '여름철(7~8월)',
    'E' : '봄/가을철(1~6월,9~12월)',
    'W' : '겨울철(11~2월)',
};

let tableEmptyColspan = 12;

//-----------------------------------------------------------------------------------------------------
// Label
//-----------------------------------------------------------------------------------------------------
const $labelElectricTypeName = $("#label_electric_type_name");
const $labelTypeGubunName = $("#label_type_gubun_name");
const $labelTypeGubun2Name = $("#label_type_gubun2_name");
const $labelTypeSelectName = $("#label_type_select_name");
const $labelSummerGubunName = $("#label_summer_gubun_name");
const $labelSection = $("#label_section");
const $labelStatusLevel = $("#label_status_level");
const $labelApplyStartDate = $("#label_apply_start_date");
const $labelApplyEndDate = $("#label_apply_end_date");
const $labelEtcPrice1 = $("#label_etc_price1");
const $labelEtcPrice2 = $("#label_etc_price2");
const $labelEtcPrice3 = $("#label_etc_price3");
const $labelEtcPrice4 = $("#label_etc_price4");

//-----------------------------------------------------------------------------------------------------
// Input
//-----------------------------------------------------------------------------------------------------
const $popupDefaultPrice = $("#popup_defaultPrice");
const $popupCost = $("#popup_cost");

const $popupText = ['기본요금', '단가',];
const $popupInput = [$popupDefaultPrice, $popupCost];

//-----------------------------------------------------------------------------------------------------
// Page
//-----------------------------------------------------------------------------------------------------
const pageCount = 5;
const viewPageCount = 10;
const startPage = 1;

//-----------------------------------------------------------------------------------------------------
// Tables
//-----------------------------------------------------------------------------------------------------
const $tbodyEnergyUnitPrice = $("#tbody_energy_unit_price");

//-----------------------------------------------------------------------------------------------------
// Buttons
//-----------------------------------------------------------------------------------------------------
const $formPopupSetting = $(".form_popup_setting");
const $formSubmitId = $("#form_popup_setting");
const $btnPriceButtonClose = $("#btn_price_button_close");
const $btnPriceButtonSave = $("#btn_price_button_save");
const $btnPriceFirstPage = $("#btn_price_first_page");
const $btnPricePrevPage = $("#btn_price_prev_page");
const $btnPriceNextPage = $("#btn_price_next_page");
const $btnPriceLastPage = $("#btn_price_last_page");

//----------------------------------------------------------------------------------------------
// img
//----------------------------------------------------------------------------------------------
const $loginLogPageIcon = $("#login_log_page_icon");

//----------------------------------------------------------------------------------------------
// ETC
//----------------------------------------------------------------------------------------------
const $menuGroupSelector = $(".menu_group_name");

//-----------------------------------------------------------------------------------------------------
// Popup
//-----------------------------------------------------------------------------------------------------
let popupStandardParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};

let standardFormPopup = module.popup(popupStandardParams);