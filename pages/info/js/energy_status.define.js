//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'info';
const command = 'info_status';

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const colorData = CONFIGS['info']['chart_color'];
const labelStatus = ['최대부하', '중부하', '경부하', '정상', '기준값'];

//----------------------------------------------------------------------------------------------
// Variables & const
//----------------------------------------------------------------------------------------------
const PERIOD_STATUS = 2;
const defaultDong = 'all';
const defaultFloor = 'all';
const defaultRoom = 'all';
const btnStartIndex = CONFIGS['energy_start_index'];
const defaultEnergyKey = CONFIGS['energy_start_key'];

const STATUS_NORMAL_COLOR = colorData['status']['normal'];

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectBuildingDong = $("#select_building_dong");
const $selectBuildingFloor = $("#select_building_floor");
const $selectBuildingRoom = $("#select_building_room");
const $dateSelect = $("#date_select");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const $radioPeriod = $(".radio_period");

//----------------------------------------------------------------------------------------------
// CSS
//----------------------------------------------------------------------------------------------
const $percentCSS = ['percent_up', 'percent_down', 'percent_zero'];
const $percentColorCSS = ['fcRed', 'fcprimary', 'fcGray'];

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelUnit = $(".label_unit");
const $labelUsageNow = $("#label_usage_now");
const $labelUsageLast = $("#label_usage_last");
const $labelDiff = $("#label_diff");

const $labelPercentColorLowTop = $("#label_percent_color_low_top");
const $labelPercentValueLowTop = $("#label_percent_value_low_top");
const $labelPercentLowTop = $("#label_percent_low_top");

const $labelPercentColorMidTop = $("#label_percent_color_mid_top");
const $labelPercentValueMidTop = $("#label_percent_value_mid_top");
const $labelPercentMidTop = $("#label_percent_mid_top");

const $labelPercentColorMaxTop = $("#label_percent_color_max_top");
const $labelPercentValueMaxTop = $("#label_percent_value_max_top");
const $labelPercentMaxTop = $("#label_percent_max_top");

const $labelPrevLowSum = $("#label_prev_low_sum");
const $labelPrevMidSum = $("#label_prev_mid_sum");
const $labelPrevMaxSum = $("#label_prev_max_sum");

//----------------------------------------------------------------------------------------------
// VALIDATE MESSAGE
//----------------------------------------------------------------------------------------------
const VALIDATE_SEARCH_TODAY = '일 검색은 전일부터 조회 가능합니다.';

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegend = $("#div_chart_legend");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartId = 'canvas_chart';

// 부하현황 그래프
let statusChart = createBarChart(canvasChartId, STATUS_NORMAL_COLOR, '사용량', '시간', 0);
statusChart.update();