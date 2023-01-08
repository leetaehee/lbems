//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'control';
const command = 'control';
const setCommand = command + '_set';
const company = CONFIGS['control']['company'];

const FLOOR_REQUESTER = 'building';
const FLOOR_REQUEST = 'm_floor_info';

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const DEFAULT_EMPTY_ARRAY = [];

//----------------------------------------------------------------------------------------------
// Const
//----------------------------------------------------------------------------------------------
const SET_MAX_TEMPERATURE = 30;
const SET_MIN_TEMPERATURE = 18;
const SET_CONTROL_TIME_OUT = CONFIGS['control']['timeout'];
const isReady = CONFIGS['control']['is_ready'];

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelTemperature = $("#label_temperature");
const $labelDeviceLocation = $("#label_device_location");

//----------------------------------------------------------------------------------------------
// Button
//----------------------------------------------------------------------------------------------
const $btnMode4 = $("#btn_mode_4");
const $btnMode1 = $("#btn_mode_1");
const $btnMode2 = $("#btn_mode_2");
const $btnMode5 = $("#btn_mode_5");

const $btnFan4 = $("#btn_fan_4");
const $btnFan1 = $("#btn_fan_1");
const $btnFan2 = $("#btn_fan_2");
const $btnFan3 = $("#btn_fan_3");

const $btnTemperatureUp = $("#btn_temperature_up");
const $btnTemperatureDown = $("#btn_temperature_down");

const $btnModeGroup = $(".btn_mode_group");
const $btnFanGroup = $(".btn_fan_group");

const $modes = [$btnMode1, $btnMode2, $btnMode4, $btnMode5];
const $fans = [$btnFan1, $btnFan2, $btnFan3, $btnFan4];

//----------------------------------------------------------------------------------------------
// Checkbox
//----------------------------------------------------------------------------------------------
const $checkboxPowerOnOff = $("#checkbox_power_on_off");

//----------------------------------------------------------------------------------------------
// SELECT
//----------------------------------------------------------------------------------------------
const $selectFloorType = $("#floor_type");
const $selectRoomType = $("#room_type");