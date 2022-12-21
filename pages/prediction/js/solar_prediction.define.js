//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "prediction";
const command = "solar_prediction";

//----------------------------------------------------------------------------------------------
// Const
//----------------------------------------------------------------------------------------------
const DAILY_USED_RATE = 10;
const WEEKLY_USED_RATE = 70;
const MONTH_USED_RATE = 300;

const BTN_START_INDEX = 11;
const DEFAULT_PERIOD = 2;
const BASE_VAL = 1.2;

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelDailyPeriod = $("#label_daily_period");
const $labelWeeklyPeriod = $("#label_weekly_period");
const $labelMonthPeriod = $("#label_month_period");

const $labelGraphDailyUsed = $("#label_graph_daily_used");
const $labelGraphDailyPredict = $("#label_graph_daily_predict");
const $labelGraphWeeklyUsed = $("#label_graph_weekly_used");
const $labelGraphWeeklyPredict = $("#label_graph_weekly_predict");
const $labelGraphMonthUsed = $("#label_graph_month_used");
const $labelGraphMonthPredict = $("#label_graph_month_predict");

const $labelGraphDailyUsedText = $("#label_graph_daily_used_text");
const $labelGraphDailyPredictText = $("#label_graph_daily_predict_text");
const $labelGraphWeeklyUsedText = $("#label_graph_weekly_used_text");
const $labelGraphWeeklyPredictText = $("#label_graph_weekly_predict_text");
const $labelGraphMonthUsedText = $("#label_graph_month_used_text");
const $labelGraphMonthPredictText = $("#label_graph_month_predict_text");

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const unit = 'kWh';
const currentNames = ['금일 현재 발전량', '금주 현재 발전량', '금월 현재 발전량'];
const expectNames = ['금일 예상 발전량', '금주 예상 발전량', '금월 예상 발전량'];
const homeTypes = ['메인'];
const PERIOD_KEYS = ['daily', 'weekly', 'month'];
const LEGEND_KEYS = ['current', 'predict']
const DEFAULT_LEGEND = ['금일 현재 발전량', '금일 예상 발전량'];
const DEAULT_EMPTY_ARRAY = [];

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnDaily = $("#btn_daily");
const $btnWeekly = $("#btn_weekly");
const $btnMonth = $("#btn_month");

//----------------------------------------------------------------------------------------------
// DIV
//----------------------------------------------------------------------------------------------
const $divChartLegendRoom = $("#div_chart_legend_room");
const $divChartLegendOffice = $("#div_chart_legend_office");

//----------------------------------------------------------------------------------------------
// Chart  
//----------------------------------------------------------------------------------------------
const CHART_COLORS = CONFIGS['predict']['chart_color'];

const canvasFloorChartId = 'canvas_chart_middle1';
const chartCurrentByFloorColor = CHART_COLORS['floor_type']['current'];
const chartPredictByFloorColor = CHART_COLORS['floor_type']['predict'];

let floorChart = createTypeBarChart(canvasFloorChartId, chartCurrentByFloorColor, chartPredictByFloorColor, '과거 사용량', '예상 사용량');
floorChart.update();

const chartOfficeChartId = 'canvas_chart_middle2';
const chartCurrentByOfficeColor = CHART_COLORS['total_type']['current'];
const chartPredictByOfficeColor = CHART_COLORS['total_type']['predict'];

let officeChart = createBuildingBarChart(chartOfficeChartId, chartCurrentByOfficeColor, chartPredictByOfficeColor, '과거 사용량','예상 사용량');
officeChart.update();

let charts = [floorChart, officeChart];