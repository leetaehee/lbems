//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'control';
const command = 'control';
const onOffDisplay = CONFIGS['control']['on_off_display'];
const company = CONFIGS['control']['company'];
const setCommand = command + '_set';
const currentFloor = '3F';

//----------------------------------------------------------------------------------------------
// Const & Variable
//----------------------------------------------------------------------------------------------
const DEFAULT_ROOM_NAME = CONFIGS['control']['default_floor'][currentFloor];
const isReady = CONFIGS['control']['is_ready'];

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const BTN_AIR_CONT_INFO = CONFIGS['control']['air_con_id'][currentFloor];
const FLOOR_KEYS = Object.keys(CONFIGS['control']['air_con_id']);

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $roomTemp = $("#roomTemp");
const $labelDeviceLocation = $("#label_device_location");

//----------------------------------------------------------------------------------------------
// Button
//----------------------------------------------------------------------------------------------
const $floorDevices = [];

const $btnPower = $("#btn_power");
const $btnFan1 = $("#btn_fan_1");
const $btnFan2 = $("#btn_fan_2");
const $btnFan3 = $("#btn_fan_3");
const $btnFan4 = $("#btn_fan_4");

const $btnMode4 = $("#btn_mode_4");
const $btnMode1 = $("#btn_mode_1");
const $btnMode5 = $("#btn_mode_5");
const $btnMode2 = $("#btn_mode_2");
const $btnMode3 = $("#btn_mode_3");

const $btnFanGroup = $(".btn_fan_group");
const $btnModeGroup = $(".btn_mode_group");

const $btnTemperatureDown = $("#btn_temperature_down");
const $btnTemperatureUp = $("#btn_temperature_up");

const $fans = [$btnFan1, $btnFan2, $btnFan3, $btnFan4];
const $modes = [$btnMode1, $btnMode2, $btnMode3, $btnMode4, $btnMode5];

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartId = 'canvas-chart-status';
const canvasChartColor = '41,112,184';
const loadingChart = createLoadingChart(canvasChartId, canvasChartColor);
loadingChart.update([100]);