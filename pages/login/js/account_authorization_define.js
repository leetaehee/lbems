//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'auth'
const command = 'receive_auth_num';
const confirmCommand = 'confirm_auth_info';

//----------------------------------------------------------------------------------------------
// Input
//----------------------------------------------------------------------------------------------
const $name = $("#name");
const $email = $("#email");
const $authNum = $("#auth_num");

//----------------------------------------------------------------------------------------------
// Label
//----------------------------------------------------------------------------------------------
const $labelVaildName = $("#label_vaild_name");
const $labelVaildEmail = $("#label_vaild_email");
const $labelAuthTime = $("#label_auth_time");

//----------------------------------------------------------------------------------------------
// Color
//----------------------------------------------------------------------------------------------
const PASSWORD_RESET_DISABLED = '#999999';
const PASSWORD_RESET_ENABLED = '#578fac';

//----------------------------------------------------------------------------------------------
// Variable & Const
//----------------------------------------------------------------------------------------------
const EMPTY_VALUE = '';
const DEFAULT_TIME_SECOND = 300;
let AUTH_WAIT_SECOND = 0;

//----------------------------------------------------------------------------------------------
// Validate
//----------------------------------------------------------------------------------------------
const VALIDATE_FIND_EMAIL_VALUE_EMPTY = '*이메일을 입력하세요.';
const VALIDATE_FIND_NAME_VALUE_EMPTY = '*이름을 입력하세요';
const VALIDATE_FIND_EMAIL_VALUE_NOT_RIGHT = '*등록되지 않은 이메일 주소입니다.';
const VALIDATE_FIND_NAME_VALUE_NOT_RIGHT = '*등록되지 않은 이름입니다.';
const VALIDATE_FIRST_LOGIN_FAIL = '*기본 비밀번호입니다. 로그인 후 변경하세요.';

//----------------------------------------------------------------------------------------------
// Button
//----------------------------------------------------------------------------------------------
const $btnReceiveAuth = $("#btn_receive_auth");
const $btnPasswordResetting = $("#btn_password_resetting");