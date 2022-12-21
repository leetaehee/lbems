//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'dashboard';
const command = 'dashboard_floor';

//----------------------------------------------------------------------------------------------
// Variable & Const
//----------------------------------------------------------------------------------------------
const defaultOption = CONFIGS['dashboard']['option'];
const defaultDateType = CONFIGS['dashboard']['date_type'];
const defaultPredictDateType = CONFIGS['dashboard']['predict_date_type'];
const defaultFloorType = 'all';
const defaultRoomType = 'all';
const defaultPredictLoading = false;
const UPPER_DIR = "../res/images/";

const CHART_ENERGY_COLORS = CONFIGS['dashboard']['energy_graph_color'];
const currentColor = CHART_ENERGY_COLORS['current_line'];
const previousColor = CHART_ENERGY_COLORS['previous_line'];
const standardLineColor = CHART_ENERGY_COLORS['standard_line'];

const CHART_LEGENDS = {'현재' : currentColor, '이전' : previousColor, '기준값' : standardLineColor};

//----------------------------------------------------------------------------------------------
// Validate
//----------------------------------------------------------------------------------------------
const VALIDATE_HOUR_VALUE_EMPTY = '한시간 목표 사용량을 입력하세요.';
const VALIDATE_HOUR_VALUE_ONLY_INTEGER = '한시간 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_HOUR_VALUE_OVER = '한시간 목표 사용량은 하루 목표 사용량을 초과 할 수 없습니다.';
const VALIDATE_DAY_VALUE_EMPTY = '하루 목표 사용량을 입력하세요.';
const VALIDATE_DAY_VALUE_ONLY_INTEGER = '하루 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_DAY_VALUE_OVER = '하루 목표 사용량은 한달 목표 사용량을 초과 할 수 없습니다.';
const VALIDATE_MONTH_VALUE_EMPTY = '한달 목표 사용량을 입력하세요.';
const VALIDATE_MONTH_VALUE_ONLY_INTEGER = '한달 목표 사용량에는 숫자만 입력할 수 있습니다.';
const VALIDATE_MONTH_VALUE_OVER = '한달 목표 사용량은 일년 목표 사용량을 초과 할 수 없습니다.';
const VALIDATE_YEAR_VALUE_EMPTY = '일년 목표 사용량을 입력하세요.';
const VALIDATE_YEAR_VALUE_ONLY_INTEGER = '일년 목표 사용량에는 숫자만 입력할 수 있습니다.';

//----------------------------------------------------------------------------------------------
// array
//----------------------------------------------------------------------------------------------
const currentNames = ['금일 현재 사용량', '금주 현재 사용량', '금월 현재 사용량'];
const expectNames = ['금일 예상 사용량', '금주 예상 사용량', '금월 예상 사용량'];
const periodTimeUnits = ['month', 'day', 'hour'];
const floors = CONFIGS['floor'];

//----------------------------------------------------------------------------------------------
// Button
//----------------------------------------------------------------------------------------------
const $btnSetElec = $("#btn_set_elec");
const $btnDashboardAll = $("#btn_dashboard_all");

const $btnDaily = $("#btn_daily");
const $btnWeekly = $("#btn_weekly");
const $btnMonth = $("#btn_month");
const $buttons = [$btnDaily, $btnWeekly, $btnMonth];

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectEnergy = $("#select_energy");

//----------------------------------------------------------------------------------------------
// table
//----------------------------------------------------------------------------------------------
const $tbodyFloor = $("#tbody_floor");
const $tbodyUsage = $("#tbody_usage");

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelTargetElec = $("#label_target_elec");
const $labelUsageElec = $("#label_usage_elec");
const $labelBeforeElec = $("#label_before_elec");
const $labelPriceElec = $("#label_price_elec");

const $labelPrevAreaUsed = $("#label_prev_area_used");
const $labelCurrentAreaUsed = $("#label_current_area_used");
const $labelPrevAreaPrice = $("#label_prev_area_price");
const $labelCurrentAreaPrice = $("#label_current_area_price");

const $labelPredictionPeriod = $("#label_prediction_period");

const $labelUnit = $(".label_unit");
const $labelPeriodTimeUnit = $("#label_period_time_unit");
const $labelCurrentFloorName = $("#label_current_floor_name");

//----------------------------------------------------------------------------------------------
// CSS
//----------------------------------------------------------------------------------------------
const $birdeye = $(".birdeye");

//----------------------------------------------------------------------------------------------
// Color
//----------------------------------------------------------------------------------------------
const floorBackgroundCSS = CONFIGS['dashboard']['floor_background_color'];

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendEnergy = $("#div_chart_legend_energy");
const $divChartLegendUsage = $("#div_chart_legend_usage");

//----------------------------------------------------------------------------------------------
// Graph
//----------------------------------------------------------------------------------------------
const $graphPrevAreaUsed = $("#graph_prev_area_used");
const $graphCurrentAreaUsed = $("#graph_current_area_used");
const $graphPrevAreaPrice = $("#graph_prev_area_price");
const $graphCurrentAreaPrice = $("#graph_current_area_price");

//----------------------------------------------------------------------------------------------
// domain
//----------------------------------------------------------------------------------------------
const $window = window.location.origin;

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const CHART_PREDICT_COLORS = CONFIGS['dashboard']['prediction_graph_color'];

// 사용량 및 사용요금
const canvasEnergyGraph = 'canvas_elec';
let chartEnergy = createLineY2Chart(canvasEnergyGraph, currentColor, previousColor, standardLineColor,  `현재(kWh)`, `이전(kWh)`, `기준값(kWh)`, null);
chartEnergy.update();

// 데이터없음 항목도 처리 해야하므로 +1을 넣음
const DASHBOARD_DEFAULT_ITEM_COUNT = (CONFIGS['usage_labels'].length) + 1;

const USAGE_DEFAULT_ARRAY = Array.from({length:DASHBOARD_DEFAULT_ITEM_COUNT}, () => 0);
const DISTRIBUTION_DEFAULT_ARRAY = Array.from({length:DASHBOARD_DEFAULT_ITEM_COUNT}, () => 0);
USAGE_DEFAULT_ARRAY[0] = DISTRIBUTION_DEFAULT_ARRAY[0] = 100;

const canvasUsePieEnergy = "chart_pie_usage";

const LABEL_USAGES = CONFIGS['usage_labels'];
const COLOR_USAGES = CONFIGS['usage_colors'];
const CUTOUT_PERCENTAGE = 83;

let chartPieUse = createUsePieChart(canvasUsePieEnergy, CUTOUT_PERCENTAGE);
chartPieUse.update(LABEL_USAGES, COLOR_USAGES, USAGE_DEFAULT_ARRAY, DISTRIBUTION_DEFAULT_ARRAY);

// 예측사용량
const canvasPredictBarChart = 'canvas_predict_bar_chart';
const CURRENT_COLOR = CHART_PREDICT_COLORS['current'];
const PREDICT_COLOR = CHART_PREDICT_COLORS['predict'];
const canvasColors = new Array(CURRENT_COLOR, PREDICT_COLOR);
const canvasLabels = new Array('금일 현재 사용량', '금일 예상 사용량');

let chartPredict = createPredictChart(canvasPredictBarChart, canvasColors, canvasLabels);
chartPredict.update();

//----------------------------------------------------------------------------------------------
// Popup
//----------------------------------------------------------------------------------------------
const $formPopupSetting = $(".form_popup_setting");
const $btnButtonClose = $("#btn_button_close");
const $btnButtonSave = $("#btn_button_save");
const $inputPopupHour = $("#input_popup_hour");
const $inputPopupDay = $("#input_popup_day");
const $inputPopupMonth = $("#input_popup_month");
const $inputPopupYear = $("#input_popup_year");
const $popupUnit1 = $("#popup_unit1");
const $popupUnit2 = $("#popup_unit2");
const $popupUnit3 = $("#popup_unit3");
const $popupUnit4 = $("#popup_unit4");
const $popupEnergyName = $("#popup_energy_name");

let popupParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};
let formPopup = module.popup(popupParams);