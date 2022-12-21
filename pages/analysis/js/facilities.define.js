//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'analysis';
const command = 'analysis_energy';

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
//const $selectBuildingFloor = $("#select_building_floor");
//const $selectBuildingRoom = $("#select_building_room");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const radioCheckedName = "input[name=radio_date]:checked";
const $dateSelect = $("#date_select");

const $btnUsedTransitionGraph = $("#btn_used_transition_graph");
const $btnUsedBarGraph = $("#btn_used_bar_graph");
const $btnPriceTransitionGraph = $("#btn_price_transition_graph");
const $btnPriceBarGraph = $("#btn_price_bar_graph");

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const colorData = CONFIGS['analysis']['area_menu']['chart_color'];

//----------------------------------------------------------------------------------------------
// Variables & Const
//----------------------------------------------------------------------------------------------
const defaultFloor = 'all';
const defaultRoom = 'all';
const defaultOption = CONFIGS['facility_start_index'];
const defaultEnergyKey = CONFIGS['facility_start_key'];
const PREVIOUS_USED_COLOR = colorData['used']['previous'];
const CURRENT_USED_COLOR = colorData['used']['current'];
const PREVIOUS_PRICE_COLOR = colorData['price']['previous'];
const CURRENT_PRICE_COLOR = colorData['price']['current'];

const CHART_USED_LABEL = ['이전기간 사용량', '선택기간 사용량'];
const CHART_USED_LEGEND = '사용량';
const CHART_PRICE_LABEL = ['이전기간 사용요금', '선택기간 사용요금'];
const CHART_PRICE_LEGEND = '사용요금';
const USED_UNIT = 'Wh';
const PRICE_UNIT = '원';

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelUsageNow = $("#label_usage_now");
const $labelUnitNow = $(".label_unit_now");
const $labelUsageLast = $("#label_usage_last");
const $labelUnitLast = $("#label_unit_last");
const $labelDiff = $("#label_diff");
const $labelUnitDiff = $("#label_unit_diff");
const $labelPercentValueTop = $("#label_percent_value_top");

//middle
const $canvasChartMiddle = $("#canvas_chart_middle");
const $labelDiffUsageLast = $("#label_diff_usage_last");
const $labelDiffUnitLastMiddle = $("#label_diff_unit_last_middle");
const $labelDiffUsageNow = $("#label_diff_usage_now");
const $labelDiffUnitNowMiddle = $("#label_diff_unit_now_middle");
const $labelDiffMiddle = $("#label_diff_middle");
const $labelDiffUnitMiddle = $("#label_diff_unit_middle");
const $labelPercentColorMiddle = $("#label_percent_color_middle");
const $labelPercentMiddle = $("#label_percent_middle");
const $labelPercentValueMiddle = $("#label_percent_value_middle");

//Bottom
//const $canvasChartBottom = $("#canvas_chart_bottom");
//const $labelDiffPriceLast = $("#label_diff_price_last");
//const $labelDiffPriceNow = $("#label_diff_price_now");
//const $labelDiffBottom = $("#label_diff_bottom");
//const $labelPercentColorBottom = $("#label_percent_color_bottom");
//const $labelPercentBottom = $("#label_percent_bottom");
//const $labelPercentValueBottom = $("#label_percent_value_bottom");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendUsed = $("#div_chart_legend_used");
const $divChartLegendPrice = $("#div_chart_legend_price");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const $barChartUsageLast = $("#bar_chart_usage_last");
const $barChartUsageNow = $("#bar_chart_usage_now");
const $barChartPriceLast = $("#bar_chart_price_last");
const $barChartPriceNow = $("#bar_chart_price_now");

const canvasChartMiddleId = "canvas_chart_middle";
const chartMiddleBar1Color = PREVIOUS_USED_COLOR;
const chartMiddleBar2Color = CURRENT_USED_COLOR;

let chartMiddle = createBarTwoChart(canvasChartMiddleId, chartMiddleBar1Color, chartMiddleBar2Color, CHART_USED_LEGEND, CHART_USED_LEGEND);
chartMiddle.update();

/*
const canvasChartBottomId = "canvas_chart_bottom";
const chartBottomBar1Color = PREVIOUS_PRICE_COLOR;
const chartBottomBar2Color = CURRENT_PRICE_COLOR;

let chartBottom = createBarTwoChart(canvasChartBottomId, chartBottomBar1Color, chartBottomBar2Color, CHART_PRICE_LEGEND, CHART_PRICE_LEGEND);
chartBottom.update();

let charts = [chartMiddle, chartBottom];
*/
let charts = [chartMiddle];

const canvasTransitionId1 = "canvas_chart_middle";
const transition1Color1 = PREVIOUS_USED_COLOR;
const transition1Color2 = CURRENT_USED_COLOR;
let chartTransition1 = createAnalysisLineChart(canvasTransitionId1, transition1Color1, transition1Color2, CHART_USED_LEGEND, CHART_USED_LEGEND);

/*
const canvasTransitionId2 = "canvas_chart_bottom";
const transition2Color1 = PREVIOUS_PRICE_COLOR;
const transition2Color2 = CURRENT_PRICE_COLOR;
let chartTransition2 = createAnalysisLineChart(canvasTransitionId2, transition2Color1, transition2Color2, CHART_PRICE_LEGEND, CHART_PRICE_LEGEND);

let transitionCharts = [chartTransition1, chartTransition2];
*/
let transitionCharts = [chartTransition1];