//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "info";
const command = "info_energy";

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const colorData = CONFIGS['info']['chart_color'];
const labelStatus = ['최대부하', '중부하', '경부하', '정상', '기준값'];

//----------------------------------------------------------------------------------------------
// Variables & const
//----------------------------------------------------------------------------------------------
const PERIOD_STATUS = 1;
const chartOption = 0;
const defaultDong = 'all';
const defaultFloor = 'all';
const defaultRoom = 'all';
const btnStartIndex = CONFIGS['energy_start_index'];
const defaultEnergyKey = CONFIGS['energy_start_key'];

const STATUS_MAX_COLOR = colorData['status']['max'];
const STATUS_MID_COLOR = colorData['status']['mid'];
const STATUS_MIN_COLOR = colorData['status']['min'];
const STATUS_NORMAL_COLOR = colorData['status']['normal'];
const STATUS_STANDARD_COLOR = colorData['status']['standard'];

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectBuildingDong = $("#select_building_dong");
const $selectBuildingFloor = $("#select_building_floor");
//const $selectBuildingRoom = $("#select_building_room");
const $dateSelect = $("#date_select");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");

const $radioPeriod = $(".radio_period");

const $formPopupSetting = $(".form_popup_setting");
const $standardSetting = $("#standard-setting");
const $btnButtonClose = $("#btn_button_close");
const $btnButtonSave = $("#btn_button_save");

//----------------------------------------------------------------------------------------------
// CSS
//----------------------------------------------------------------------------------------------
const $percentCSS = ['percent_up', 'percent_down', 'percent_zero'];
const $percentColorCSS = ['fcRed', 'fcprimary', 'fcGray'];

//----------------------------------------------------------------------------------------------
// Input
//----------------------------------------------------------------------------------------------
const $inputPopupHour = $("#input_popup_hour");
const $inputPopupDay = $("#input_popup_day");
const $inputPopupMonth = $("#input_popup_month");
const $popupStandard = $(".popup-standard");

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

const $labelAverage = $("#label_average");
const $labelPercent = $("#label_percent");

//----------------------------------------------------------------------------------------------
// color
//----------------------------------------------------------------------------------------------
const statusChartColor = [STATUS_MAX_COLOR, STATUS_MID_COLOR, STATUS_MIN_COLOR, STATUS_NORMAL_COLOR]; // 최대부하, 중부하, 경부하, 정상 순서

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendStatus = $("#div_chart_legend_status");
const $divChartLegendStatusRate = $("#div_chart_legend_status_rate");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartStatusId = "canvas_chart_status";
const statusBarColor = "255,193,7";
const statusLineColor = STATUS_STANDARD_COLOR;

// 부하현황 그래프
let statusChart = createBarLineChart(canvasChartStatusId, statusBarColor, statusLineColor, "부하량", "시간", 0);
statusChart.update();

const chartPieStatusId = "chart_pie_status";
const defaultUseColor = CONFIGS['default_color'];
const maxStatusColor = STATUS_MAX_COLOR;
const midStatusColor = STATUS_MID_COLOR;
const minStatusColor = STATUS_MIN_COLOR;
const labelsUsage = ["데이터없음", "최대부하", "중부하", "경부하"];

// 부하별비율 그래프
let chartPieStatus = createPieChart(chartPieStatusId, labelsUsage, defaultUseColor, maxStatusColor, midStatusColor, minStatusColor);
chartPieStatus.update([100, 0, 0, 0]);

const canvasPieAverage = "chart_pie_average";
const averageColor1 = colorData['standard_average']['used'];
const averageColor2 = colorData['standard_average']['zero'];
const labelsAverage = ["사용량",""];

// 기준값 대비 평균 사용량
let chartPieAverage = createPieChart2(canvasPieAverage, labelsAverage, averageColor1, averageColor2);
chartPieAverage.update([0,200]);

// 화면에 표시할 그래프를 배열에 추가
let charts = [statusChart, chartPieStatus, chartPieAverage];

//----------------------------------------------------------------------------------------------
// 팝업
//----------------------------------------------------------------------------------------------
let standardFormPopup;

let popupStandardParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};

standardFormPopup = module.popup(popupStandardParams);