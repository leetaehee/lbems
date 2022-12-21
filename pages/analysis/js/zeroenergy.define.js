//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "analysis";
const command = "analysis_zero";

//----------------------------------------------------------------------------------------------
// Arrays
//----------------------------------------------------------------------------------------------
const $gradeColors = ['dust6', 'dust1', 'dust5', 'dust3', 'dust4'];
const $gradeBgColors = ['dustb6', 'dustb1', 'dustb5', 'dustb3', 'dustb4'];
const $gradeBcColors = ['dustbc6', 'dustbc1', 'dustbc5', 'dustbc3', 'dustbc4'];
const $gradeFcColors = ['dustfc6', 'dustfc1', 'dustfc5', 'dustfc3', 'dustfc4'];
const $colorList = [$gradeColors, $gradeBgColors, $gradeBcColors, $gradeFcColors];

//----------------------------------------------------------------------------------------------
// MODE
//----------------------------------------------------------------------------------------------
const MONTH_TYPE = "";

//----------------------------------------------------------------------------------------------
// CSS
//----------------------------------------------------------------------------------------------
const $monthClickColors = ['on1', 'on2', 'on3', 'on4', 'on5'];
const $monthHoverColors = ['hv1', 'hv2', 'hv3', 'hv4', 'hv5'];

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $currentGrade = $("#current_grade");
const $dustDom = $("#dust_dom");
const $dustbDom = $("#dustb_dom");
const $dustbcDom = $("#dustbc_dom");
const $dustfcDom = $("#dustfc_dom");
const $dusts = [$dustDom, $dustbDom, $dustbcDom, $dustfcDom];

const $liZero1 = $("#li_zero_1");
const $liZero2 = $("#li_zero_2");
const $liZero3 = $("#li_zero_3");
const $liZero4 = $("#li_zero_4");
const $liZero5 = $("#li_zero_5");
const $liZero6 = $("#li_zero_6");
const $liZero7 = $("#li_zero_7");
const $liZero8 = $("#li_zero_8");
const $liZero9 = $("#li_zero_9");
const $liZero10 = $("#li_zero_10");
const $liZero11 = $("#li_zero_11");
const $liZero12 = $("#li_zero_12");
const $liZeroMonths	= [$liZero1, $liZero2, $liZero3, $liZero4, $liZero5, $liZero6, $liZero7, $liZero8, $liZero9, $liZero10, $liZero11, $liZero12];

const $btnZero1	= $("#btn_zero_1");
const $btnZero2	= $("#btn_zero_2");
const $btnZero3 = $("#btn_zero_3");
const $btnZero4 = $("#btn_zero_4");
const $btnZero5 = $("#btn_zero_5");
const $btnZero6 = $("#btn_zero_6");
const $btnZero7 = $("#btn_zero_7");
const $btnZero8 = $("#btn_zero_8");
const $btnZero9 = $("#btn_zero_9");
const $btnZero10 = $("#btn_zero_10");
const $btnZero11 = $("#btn_zero_11");
const $btnZero12 = $("#btn_zero_12");
const $btnZeroMonths = [$btnZero1, $btnZero2, $btnZero3, $btnZero4, $btnZero5, $btnZero6, $btnZero7, $btnZero8, $btnZero9, $btnZero10, $btnZero11, $btnZero12];

const $spanZero1 = $("#span_zero_1");
const $spanZero2 = $("#span_zero_2");
const $spanZero3 = $("#span_zero_3");
const $spanZero4 = $("#span_zero_4");
const $spanZero5 = $("#span_zero_5");
const $spanZero6 = $("#span_zero_6");
const $spanZero7 = $("#span_zero_7");
const $spanZero8 = $("#span_zero_8");
const $spanZero9 = $("#span_zero_9");
const $spanZero10 = $("#span_zero_10");
const $spanZero11 = $("#span_zero_11");
const $spanZero12 = $("#span_zero_12");
const $spanZeroMonths = [$spanZero1, $spanZero2, $spanZero3, $spanZero4, $spanZero5, $spanZero6, $spanZero7, $spanZero8, $spanZero9, $spanZero10, $spanZero11, $spanZero12];

const $dailyGraphMonth = $("#daily_graph_month");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnMonth = $(".btn_month");

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectYear = $("#select_year");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartYearId = "canvas_chart_month";
const chartMonthProductionColor = "211, 226, 232";
const chartMonthConsumptionColor = "243, 169, 120";
const chartMonthLineColor = "64, 69, 86";

let chartMonth = createBarLineChart(canvasChartYearId, chartMonthProductionColor, chartMonthConsumptionColor, chartMonthLineColor, "생산량 및 소비량", "일자", 0);
chartMonth.update();

const canvasChartMonthId = "canvas_chart_daily";
const chartDailyProductionColor = "211, 226, 232";
const chartDailyConsumptionColor = "243, 169, 120";
const chartDailyLineColor = "64, 69, 86";

let chartDaily = createBarLineChart(canvasChartMonthId, chartDailyProductionColor, chartDailyConsumptionColor, chartDailyLineColor, "생산량 및 소비량", "일자", 1);
chartDaily.update();

let charts = [chartMonth, chartDaily];