//-----------------------------------------------------------------------------------------------------
// Ajax
//-----------------------------------------------------------------------------------------------------
const requester = 'set';
const command = 'set_standard';
const setCommand = 'set_standard_code';

//-----------------------------------------------------------------------------------------------------
// Variables & Const
//-----------------------------------------------------------------------------------------------------
const SET_MENU_BUILDING_TEXT = CONFIGS['building_name'] + ' 사무소의 정보를 관리합니다.';
const BTN_DISPLAY_SAVE_NAME = '저장';
const BTN_FINEDUST_KEY_NAME = 'finedust';
const COLSPAN_STANDARD_VALUE = 2;
const EQUIPMENT_NAME = 'electric_equipment';
const STANDARD_STRING_TOTAL_LENGTH = 60;

//-----------------------------------------------------------------------------------------------------
// Array
//-----------------------------------------------------------------------------------------------------
const settingData = CONFIGS['set']['info'];
const equipmentTypes = ['freeze', 'geothermal'];

//-----------------------------------------------------------------------------------------------------
// Map
//-----------------------------------------------------------------------------------------------------
const replaceKeyMaps = {
    'oil_dyu' : 'heating',
};

//-----------------------------------------------------------------------------------------------------
// prefix, suffix
//-----------------------------------------------------------------------------------------------------
const HOUR_SUFFIX = '_hour';
const DAY_SUFFIX = '_day';
const MONTH_SUFFIX = '_month';
const YEAR_SUFFIX = '_year';
const LABEL_TIME_SUFFIX = [HOUR_SUFFIX, DAY_SUFFIX, MONTH_SUFFIX, YEAR_SUFFIX];

const FORM_PREFIX = 'form_time_';
const BTN_PREFIX = 'btn_';

//-----------------------------------------------------------------------------------------------------
// labels
//-----------------------------------------------------------------------------------------------------
const $buildingText = $("#building_text");

//-----------------------------------------------------------------------------------------------------
// Sections
//-----------------------------------------------------------------------------------------------------
const $sectionGroup = $("#section_group");

//-----------------------------------------------------------------------------------------------------
// cols
//-----------------------------------------------------------------------------------------------------
const TIME_COLS = [10, 10, 8, 10, 8, 10, 8, 10];
const FINEDUST_COLS = [8, 10, 8, 10, 8, 10, 8, 10];

//-----------------------------------------------------------------------------------------------------
// CSS , Image
//-----------------------------------------------------------------------------------------------------
const thTagIconClass = 'th_icon';
const w100Class = 'w100';
const pClasses = 'blt01 diIb';
const divClasses = 'setTable ac';
const btnClasses = 'Btn saveBtn mt40';
const colspanColor = CONFIGS['set']['colspan_color'];

//-----------------------------------------------------------------------------------------------------
// Validate
//-----------------------------------------------------------------------------------------------------
const VALIDATE_TIME_VALUE_EMPTY = '한시간 목표 사용량을 입력하세요.';
const VALIDATE_TIME_VALUE_ONLY_INTEGER = '한시간 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_TIME_VALUE_OVER = '한시간 목표 사용량은 하루 목표 사용량을 초과 할 수 없습니다.';
const VALIDATE_DAY_VALUE_EMPTY = '하루 목표 사용량을 입력하세요.';
const VALIDATE_DAY_VALUE_ONLY_INTEGER = '하루 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_DAY_VALUE_OVER = '하루 목표 사용량은 한달 목표 사용량을 초과 할 수 없습니다.';
const VALIDATE_MONTH_VALUE_EMPTY = '한달 목표 사용량을 입력하세요.';
const VALIDATE_MONTH_VALUE_ONLY_INTEGER = '한달 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_MONTH_VALUE_OVER = '한달 목표 사용량은 일년 목표 사용량을 초과 할 수 없습니다.';
const VALIDATE_YEAR_VALUE_EMPTY = '일년 목표 사용량을 입력하세요.';
const VALIDATE_YEAR_VALUE_ONLY_INTEGER = '일년 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_CO2_VALUE_EMPTY = 'CO2 값을 입력하세요.';
const VALIDATE_CO2_VALUE_ONLY_INTEGER = 'CO2 값은 숫자만 입력할 수 있습니다.';
const VALIDATE_PM10_VALUE_EMPTY = '미세먼지 값을 입력하세요.';
const VALIDATE_PM10_VALUE_ONLY_INTEGER = '미세먼지 값은 숫자만 입력할 수 있습니다.';
const VALIDATE_PM25_VALUE_EMPTY = '초미세먼지 값을 입력하세요';
const VALIDATE_PM25_VALUE_ONLY_INTEGER = '초미세먼지 값은 숫자만 입력할 수 있습니다.';
const VALIDATE_VALUE_LENGTH_CHECK = '입력 글자수를 초과하였습니다.';