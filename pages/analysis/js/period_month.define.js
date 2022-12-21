//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "analysis";
const command = "analysis_period";
const dateType = 1;

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const floorKeyData = CONFIGS['floor_key_data'];
const colorData = CONFIGS['analysis']['period_menu']['chart_color'];
const CHART_LEGEND_LABELS = ['전월', '금월'];

//----------------------------------------------------------------------------------------------
// Variables & Const
//----------------------------------------------------------------------------------------------
const defaultDong = 'all';
const defaultFloor = 'all';
const defaultRoom = 'all';
const ENERGY_SOME_TIME_AGO_COLOR = colorData['energy']['previous'];
const ENERGY_YESTERDAY_COLOR = colorData['energy']['current'];
const USAGE_SOME_TIME_AGO_COLOR = colorData['usage']['previous'];
const USAGE_YESTERDAY_COLOR = colorData['usage']['current'];
const FACILITY_SOME_TIME_AGO_COLOR = colorData['facility']['previous'];
const FACILITY_YESTERDAY_COLOR = colorData['facility']['current'];
const CHART_USED_LEGEND_LABEL = '사용량';

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectBuildingDong = $("#select_building_dong");
const $selectBuildingFloor = $("#select_building_floor");
//const $selectBuildingRoom = $("#select_building_room");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const $selectEnergy = $("#select_energy");
const $selectUsage = $("#select_usage");
const $selectFacility = $("#select_facility");
const $btnEnergyTransitionGraph = $("#btn_energy_transition_graph");
const $btnEnergyBarGraph = $("#btn_energy_bar_graph");
const $btnUsageTransitionGraph = $("#btn_usage_transition_graph");
const $btnUsageBarGraph = $("#btn_usage_bar_graph");
const $btnFacilityTransitionGraph = $("#btn_facility_transition_graph");
const $btnFacilityBarGraph = $("#btn_facility_bar_graph");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divEnergyGroup = $("#div_energy_group");
const $divUsageGroup = $("#div_usage_group");
const $divFacilityGroup = $("#div_facility_group")

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartPeriod1Id= 'canvas_chart_period1';
const chartPeriod1Color = ENERGY_SOME_TIME_AGO_COLOR;
const chartPeriod1Color2 = ENERGY_YESTERDAY_COLOR;
let chartPeriod1 = createBarTwoChart(canvasChartPeriod1Id, chartPeriod1Color, chartPeriod1Color2, CHART_USED_LEGEND_LABEL , CHART_USED_LEGEND_LABEL);
chartPeriod1.update();

const canvasChartPeriod2Id = 'canvas_chart_period2';
const chartPeriod2Color = USAGE_SOME_TIME_AGO_COLOR;
const chartPeriod2Color2 = USAGE_YESTERDAY_COLOR;
let chartPeriod2 = createBarTwoChart(canvasChartPeriod2Id, chartPeriod2Color, chartPeriod2Color2, CHART_USED_LEGEND_LABEL , CHART_USED_LEGEND_LABEL);
chartPeriod2.update();

const canvasChartPeriod3Id = 'canvas_chart_period3';
const chartPeriod3Color = FACILITY_SOME_TIME_AGO_COLOR;
const chartPeriod3Color2 = FACILITY_YESTERDAY_COLOR;
let chartPeriod3 = createBarTwoChart(canvasChartPeriod3Id, chartPeriod3Color, chartPeriod3Color2, CHART_USED_LEGEND_LABEL , CHART_USED_LEGEND_LABEL);
chartPeriod3.update();

let charts = [chartPeriod1, chartPeriod2, chartPeriod3];

const canvasTransitionId1 = 'canvas_chart_period1';
const transition1Color1 = ENERGY_SOME_TIME_AGO_COLOR;
const transition1Color2 = ENERGY_YESTERDAY_COLOR;
let chartTransition1 = createAnalysisLineChart(canvasTransitionId1, transition1Color1, transition1Color2, CHART_USED_LEGEND_LABEL , CHART_USED_LEGEND_LABEL);

const canvasTransitionId2 = 'canvas_chart_period2';
const transition2Color1 = USAGE_SOME_TIME_AGO_COLOR;
const transition2Color2 = USAGE_YESTERDAY_COLOR;
let chartTransition2 = createAnalysisLineChart(canvasTransitionId2, transition2Color1, transition2Color2, CHART_USED_LEGEND_LABEL , CHART_USED_LEGEND_LABEL);

const canvasTransitionId3 = 'canvas_chart_period3';
const transition3Color1 = FACILITY_SOME_TIME_AGO_COLOR;
const transition3Color2 = FACILITY_YESTERDAY_COLOR;
let chartTransition3 = createAnalysisLineChart(canvasTransitionId3, transition3Color1, transition3Color2, CHART_USED_LEGEND_LABEL , CHART_USED_LEGEND_LABEL);

let transitionChart = [chartTransition1, chartTransition2, chartTransition3];
