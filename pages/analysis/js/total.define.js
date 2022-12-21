//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'analysis';
const command = 'analysis_total';

//----------------------------------------------------------------------------------------------
// Const & Variables
//----------------------------------------------------------------------------------------------
const defaultDong = 'all';
const defaultFloor = 'all';
const defaultRoom = 'all';
const isUseFinedustSensor = CONFIGS['is_use_finedust_sensor'];
const isDisPlayEnergyPercent = CONFIGS['analysis']['total_menu']['is_display_energy_percent'];
const selectorGasItemName = CONFIGS['set']['info']['energy']['items']['가스'];

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const CHART_DATA = CONFIGS['analysis']['total_menu']['chart_color'];
const LABELS_SELECTORS = [];
const GAS_TYPES = ['gas', 'electric_ghp'];

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const radioCheckedName = "input[name=radio_date]:checked";
const $dateSelect = $("#date_select");

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectBuildingDong = $("#select_building_dong");
const $selectBuildingFloor = $("#select_building_floor");
//const $selectBuildingRoom = $("#select_building_room");

//----------------------------------------------------------------------------------------------
// table
//----------------------------------------------------------------------------------------------
const $tbodyUsage = $("#tbody_usage");
const $tbodyFacility = $("#tbody_facility");

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelUnit = $(".label_unit");
const $labelSolarUsed = $("#label_solar_used");
const $labelSolarPrice = $("#label_solar_price");
const $labelElectricUsed = $("#label_electric_used");
const $labelElectricPrice = $("#label_electric_price");
const $labelGasUsed = $("#label_gas_used");
const $labelGasPrice = $("#label_gas_price");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divFindustGraphSection = $("#div_findust_graph_section");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartLine = 'canvas_chart_line';
const chartLineColor = CHART_DATA['humidity'];
const chartLineColor2 = CHART_DATA['temperature'];
let chartLine = createLineDoubleYChart(canvasChartLine, chartLineColor, chartLineColor2, '습도(%)', '온도(℃)');
chartLine.update();

// 데이터없음 항목도 처리 해야하므로 +1을 넣음
const USAGE_DEFAULT_ITEM_COUNT = (CONFIGS['usage_labels'].length) + 1;
const FACILITY_DEFAULT_ITEM_COUNT = (CONFIGS['facility_labels'].length) + 1;;

const USAGE_DEFAULT_ARRAY = Array.from({length:USAGE_DEFAULT_ITEM_COUNT}, () => 0);
const FACILITY_DEFAULT_ARRAY = Array.from({length:FACILITY_DEFAULT_ITEM_COUNT}, () => 0);

USAGE_DEFAULT_ARRAY[0] = 100;
FACILITY_DEFAULT_ARRAY[0] = 100;

const chartPieUsageId = 'chart_pie_usage';
const LABEL_USAGES = CONFIGS['usage_labels'];
const COLOR_USAGES = CONFIGS['usage_colors']

let chartPieUsage = createPieChart(chartPieUsageId);
chartPieUsage.update(LABEL_USAGES, COLOR_USAGES, USAGE_DEFAULT_ARRAY);

const chartPieFacilitiesId = 'chart_pie_facilities';
const LABEL_FACILITIES = CONFIGS['facility_labels'];
const COLOR_FACILITIES = CONFIGS['facility_colors'];

let chartPieFacilities = createPieChart(chartPieFacilitiesId);
chartPieFacilities.update(LABEL_FACILITIES, COLOR_FACILITIES, FACILITY_DEFAULT_ARRAY);