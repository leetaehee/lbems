//-----------------------------------------------------------------------------------------------------
// Ajax
//-----------------------------------------------------------------------------------------------------
const requester = "set";
const command = "unit_price";
let costPk = "";

//-----------------------------------------------------------------------------------------------------
// Array
//-----------------------------------------------------------------------------------------------------
const gLevelArray = [1,2,3];
const POPUP_TITLES = ['등록', '수정'];

//-----------------------------------------------------------------------------------------------------
// Page
//-----------------------------------------------------------------------------------------------------
const pageCount = 5;
const viewPageCount = 10;
const startPage = 1;

//-----------------------------------------------------------------------------------------------------
// Labels
//-----------------------------------------------------------------------------------------------------
const $labelPopupTitle = $("#label_popup_title");
const $labelPopupTitleWord = $("#label_popup_title_word");

//-----------------------------------------------------------------------------------------------------
// Inputs / Selects
//-----------------------------------------------------------------------------------------------------
const $energyType = $("#energy_type");
const $startDate = $("#start_date");
const $endDate = $("#end_date");
const $gLevel = $("#g_level");
const $used = $("#used");
const $basePrice = $("#base_price");
const $unitCost = $("#unit_cost");
const $allChecked = $("#all_checked");
const choiceCheck = "input:checkbox[class='choice_checked']";
const choiceChecked = ".choice_checked:checked";

//-----------------------------------------------------------------------------------------------------
// Buttons
//-----------------------------------------------------------------------------------------------------
const $formPopupSetting = $(".form_popup_setting");
const $formSubmitId = $("#form_popup_setting");
const $priceEnroll = $(".price-enroll");
const $priceDelete = $(".price-delete");
const $btnPriceButtonClose = $("#btn_price_button_close");
const $btnPriceButtonSave = $("#btn_price_button_save");
const $btnDongFirstPage = $("#btn_price_first_page");
const $btnDongPrevPage = $("#btn_price_prev_page");
const $btnDongNextPage = $("#btn_price_next_page");
const $btnDongLastPage = $("#btn_price_last_page");

//-----------------------------------------------------------------------------------------------------
// tables
//-----------------------------------------------------------------------------------------------------
const $tbodyEnergyUnitPrice = $("#tbody_energy_unit_price");

//-----------------------------------------------------------------------------------------------------
// POPUP
//-----------------------------------------------------------------------------------------------------
let standardFormPopup;

let popupStandardParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};

standardFormPopup = module.popup(popupStandardParams);