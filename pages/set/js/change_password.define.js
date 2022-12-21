//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'account'
const command = 'change_password';

//----------------------------------------------------------------------------------------------
// Variables & Const
//----------------------------------------------------------------------------------------------
const EMPTY_VALUE = '';


//----------------------------------------------------------------------------------------------
// Object
//----------------------------------------------------------------------------------------------
const passwordRules = CONFIGS['password_rule'];

//----------------------------------------------------------------------------------------------
// Input
//----------------------------------------------------------------------------------------------
const $oldPassword = $("#old-password");
const $newPassword = $("#new-password");
const $rePassword = $("#re-password");

//----------------------------------------------------------------------------------------------
// Validate
//----------------------------------------------------------------------------------------------
const VALIDATE_PASSWORD_OLD_VALUE_EMPTY = "현재 비밀번호를 입력하세요.";
const VALIDATE_PASSWORD_NEW_VALUE_EMPTY = "새로운 비밀번호를 입력하세요.";
const VALIDATE_PASSWORD_CONFIRM_VALUE_EMPTY = "비밀번호 확인을 입력하세요.";
const VALIDATE_PASSWORD_BOTH_VALUE_SAME = "새로운 비밀번호와 확인이 일치하지 않습니다.";
const VALIDATE_PASSWORD_RULE_VALUE_VIOLATION = "패스워드는 영문 대소문자, 숫자, 특수문자를 <br> 3개 조합으로 8 ~ 15 자리여야 합니다.";
const VALIDATE_PASSWORD_RULE_VALUE_VIOLATION_LABEL = "패스워드는 영문 대소문자, 숫자, 특수문자를 3개 조합으로 8 ~ 15 자리여야 합니다.";
const VALIDATE_PASSWORD_OLD_VALUE_SAME = "기존 패스워드와 동일 합니다. <br> 다른 비밀번호를 사용하세요.";
const VALIDATE_PASSWORD_CURRENT_VALUE_SAME = "현재 비밀번호가 다릅니다. 다시 입력해주세요.";
const VALIDATE_PASSWORD_CHANGE_MESSAGE = "비밀번호가 변경 되었습니다. <br> 다시 로그인 하여 주시길 바랍니다.";

//----------------------------------------------------------------------------------------------
// Label
//----------------------------------------------------------------------------------------------
const $labelValidMessage = $("#label_valid_message");
const $labelPasswordError = $("#label_password_error");

//----------------------------------------------------------------------------------------------
// Color
//----------------------------------------------------------------------------------------------
const PASSWORD_CHANGE_DISABLED = '#999999';
const PASSWORD_CHANGE_ENABLED = '#578fac';

//----------------------------------------------------------------------------------------------
// Button
//----------------------------------------------------------------------------------------------
const $btnPasswordChange = $("#btn_password_change");
const $btnPasswordReset = $("#btn_password_reset");
const $btnPopupConfirm = $("#btn_popup_confirm");
const $btnPopupSuccessConfirm = $("#btn_popup_success_confirm");

//----------------------------------------------------------------------------------------------
// Popup
//----------------------------------------------------------------------------------------------
const $formPopupChangePassword = $(".form_popup_change_password");
let changePasswordParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupChangePassword,
};
let changePasswordPopup = module.popup(changePasswordParams);

const $formPopupChangePwdSuccess = $(".form_popup_change_pwd_success");
let successPasswordParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupChangePwdSuccess,
};
let successPasswordPopup = module.popup(successPasswordParams);