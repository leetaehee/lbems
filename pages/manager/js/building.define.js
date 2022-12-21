//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester	= 'manager';
const command = 'manager_building';

//----------------------------------------------------------------------------------------------
// Page
//----------------------------------------------------------------------------------------------
const pageCount = 5;
const viewPageCount = 10;
const startPage = 1;

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const CLOSING_DAY_DATA = {'99' : '말일'};
const POPUP_TITLES = ['등록', '수정'];

//-----------------------------------------------------------------------------------------------------
// Labels
//-----------------------------------------------------------------------------------------------------
const $labelPopupTitle = $("#label_popup_title");
const $labelPopupTitleWord = $("#label_popup_title_word");

//----------------------------------------------------------------------------------------------
// Variables / Const
//----------------------------------------------------------------------------------------------
const CLOSING_DAY_START_DATE_DAY_VALUE = 1;
const CLOSING_DAY_END_DATE_DAY_VALUE = 28;
const CLOSING_DAY_END_DAY_VALUE = 99;

//----------------------------------------------------------------------------------------------
// Inputs / Selects
//----------------------------------------------------------------------------------------------
const $name	= $("#name");
const $complexCodePk = $("#complex_code_pk");
const $homeDongCnt = $("#home_dong_cnt");
const $homeCnt = $("#home_cnt");
const $addr = $("#addr");
const $tel = $("#tel");
const $fax = $("#fax");
const $email = $("#email");
const $lat = $("#lat");
const $lon = $("#lon");
const $closingDayElectric = $("#closing_day_electric");
const $allChecked = $("#all_checked");

const choiceCheck = "input:checkbox[class='choice_checked']";
const choiceChecked = ".choice_checked:checked";

//----------------------------------------------------------------------------------------------
// Tables
//----------------------------------------------------------------------------------------------
const $tbodyRorean = $("#tbody_rorean");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $formPopupSetting	= $(".form_popup_setting");
const $formSubmitId = $("#form_popup_setting");
const $btnPopupSave = $("#btn_popup_save");
const $btnPopupClose = $("#btn_popup_close");
const $btnRorenModify = $(".btnRorenModify");
const $btnRorenEnroll = $(".btnRorenEnroll");
const $btnRorenDelete = $(".btnRorenDelete");

const $btnRorenFirstPage = $("#btn_roren_first_page");
const $btnRorenPrevPage = $("#btn_roren_prev_page");
const $btnRorenNextPage = $("#btn_roren_next_page");
const $btnRorenLastPage = $("#btn_roren_last_page");

//----------------------------------------------------------------------------------------------
// 팝업
//----------------------------------------------------------------------------------------------
let standardFormPopup;
let popupStandardParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};
standardFormPopup = module.popup(popupStandardParams);