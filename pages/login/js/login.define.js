//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'login';
const command = 'login';

//----------------------------------------------------------------------------------------------
// Const & Variables
//----------------------------------------------------------------------------------------------
const url = '';
const AutologinCookieLabel = 'bems_autologin_';
const AutologinIdCookieLabel = 'bems_autologin_id_';
const AutologinKeyCookieLabel = 'bems_autologin_key_';
const AutologinDeviceKeyCookieLabel = 'bems_device_key_';

let $loadingWindow;
let loadingWindow;

const FIND_ACCOUNT_URL = 'login/authorization.html';
const REQUEST_PASSWORD_CHANGE_URL = 'login/request_change_password.html';

//----------------------------------------------------------------------------------------------
// Label
//----------------------------------------------------------------------------------------------
const $labelIdErrorMessage = $("#label_id_error_message");
const $labelPasswordErrorMessage = $("#label_password_error_message");
const $labelIdErrorTitle = $("#label_id_error_title");

//----------------------------------------------------------------------------------------------
// Validate
//----------------------------------------------------------------------------------------------
const CALL_INFORMATION = "<br> 케빈랩에 문의하세요. <br> (031-400-3794 로 연락주세요.)";

const ID_ERROR_TITLE = '로그인 실패';
const VALIDATE_ID_CHECK_EXIST = `등록되지 않은 계정입니다. ${CALL_INFORMATION}`;
const VALIDATE_PASSWORD_CHECK_NOT_RIGHT = `비밀번호 오류 <br> 비밀번호를 다시 한 번 확인 후, 로그인해주세요. <br> (5회 이상 로그인 실패 시, 비밀번호가 초기화됩니다.)`;
const VALIDATE_ACCOUNT_NOT_RIGHT = `해당 계정의 사용자 정보가 올바르지 않습니다.  ${CALL_INFORMATION}`;

const FIND_ID_NOTICE_TITLE = `아이디 찾기`;
const FIND_ID_NOTICE_CONTENT = `아이디가 기억나지 않으시다면 ${CALL_INFORMATION}`;

const MANAGER_INQUIRY_MESSAGE = `관리자에게 문의하세요.`;

//----------------------------------------------------------------------------------------------
// Checkbox
//----------------------------------------------------------------------------------------------
const $checkBoxAutoLogin = $("#checkbox_autologin");

//----------------------------------------------------------------------------------------------
// Form
//----------------------------------------------------------------------------------------------
const $formLogin = $("#form_login");

//----------------------------------------------------------------------------------------------
// Button
//----------------------------------------------------------------------------------------------
const $btnLogin = $("#btn_login");
const $btnFindAccount = $("#btn_find_account");
const $btnPlayLogin = $("#btn_play_login");
const $btnPasswordResetting = $("#btn_password_resetting");
const $btnFindIdPopupClose = $("#btn_find_id_popup_close");
const $btnPopupFindId = $("#btn_popup_find_id");

//----------------------------------------------------------------------------------------------
// Input
//----------------------------------------------------------------------------------------------
const $inputId = $("#input_id");
const $inputPasswd = $("#input_passwd");

//----------------------------------------------------------------------------------------------
// Popup
//----------------------------------------------------------------------------------------------
const $formPopupFindId = $(".form_popup_find_id");
let findIdPopupParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupFindId
};
let findIdPopup = module.popup(findIdPopupParams);

const $formPopupFindAccount = $(".form_popup_find_account");
let findPopupParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupFindAccount
};
let findAccountPopup = module.popup(findPopupParams);

const $formPopupPasswordInitialize = $(".form_popup_password_initialize");
let initializePopupParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupPasswordInitialize
};
let initializePasswordPopup = module.popup(initializePopupParams);