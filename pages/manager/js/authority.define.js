//-----------------------------------------------------------------------------------------------------
// Arrays.
//-----------------------------------------------------------------------------------------------------
const $levels = ['게스트', '단지관리자', '업체관리자', '최고관리자'];
const POPUP_TITLES = ['등록', '수정'];

//-----------------------------------------------------------------------------------------------------
// Labels
//-----------------------------------------------------------------------------------------------------
const $labelPopupTitle = $("#label_popup_title");
const $labelPopupTitleWord = $("#label_popup_title_word");

//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester	= 'manager';
const command = 'manager_authority';
const pwInitCommand = 'password_initialize';

//----------------------------------------------------------------------------------------------
// page 
//----------------------------------------------------------------------------------------------
const pageCount	= 5;
const viewPageCount	= 10;
const startPage	= 1;

//----------------------------------------------------------------------------------------------
// inputs
//----------------------------------------------------------------------------------------------
const $formPopupSetting = $(".form_popup_setting");
const $formSubmitId = $("#form_popup_setting");
const $formAuthority = $("#form_authority");
const $rorenType = $("#roren_type");
const $authorityType = $("#authority_type");
const $selectboxRorenType = $(".roren_type");
const $allChecked = $("#all_checked");
const choiceCheck = "input:checkbox[class='choice_checked']";
const choiceChecked = ".choice_checked:checked";

const $popupRorenType = $("#popup_roren_type");
const $adminId = $("#admin_id");
const $name	= $("#name");
const $loginLevel = $("#login_level");
const $hp = $("#hp");
const $email = $("#email");
const $fgConnect = $(".fg_connect");
const $fgDel = $(".fg_del");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnAuthorityEnroll = $(".btnAuthorityEnroll");
const $btnAtuthorityDelete = $(".btnAuthorityDelete");
const $btnPopupSave = $("#btn_popup_save");
const $btnButtonClose = $("#btn_popup_close");
const $btnSearch = $("#btn_search");
const $btnAuthorityFirstPage = $("#btn_authority_first_page");
const $btnAuthorityPrevPage = $("#btn_authority_prev_page");
const $btnAuthorityNextPage = $("#btn_authority_next_page");
const $btnAuthorityLastPage = $("#btn_authority_last_page");

//----------------------------------------------------------------------------------------------
// Message
//----------------------------------------------------------------------------------------------
const CONFIRM_MESSAGE = '비밀번호를 초기화 하시겠습니까?';

//----------------------------------------------------------------------------------------------
// tables
//----------------------------------------------------------------------------------------------
const $tbodyAuthority = $("#tbody_authority");
const $popupTrPasswordInitialize = $("#popup_tr_password_initialize");

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