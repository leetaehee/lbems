//------------------------------------------------------------------------------------------------------------
// Ajax
//------------------------------------------------------------------------------------------------------------
const requester = "info";
const command = "info_environment";
const setCommand = "info_popup";

//------------------------------------------------------------------------------------------------------------
// Buttons
//------------------------------------------------------------------------------------------------------------
const $selectYearCo2 = $("#select_year_co2");
const $selectYearPM25 = $("#select_year_pm25");
const $formPopupSetting = $(".form_popup_setting");
const $btnSettingFinedust = $("#btn_setting_finedust");
const $btnButtonClose = $("#btn_button_close");
const $btnButtonSave = $("#btn_button_save");

//------------------------------------------------------------------------------------------------------------
// Array
//------------------------------------------------------------------------------------------------------------
const monthType	= ["co2", "pm25"];
const standardAlias = ["co2_standard", "pm25_standard"];
const colorData = CONFIGS['info']['chart_color'];

//------------------------------------------------------------------------------------------------------------
// Variable & Const
//------------------------------------------------------------------------------------------------------------
const defaultDateTypeCo2 = 2;
const defaultDateTypePM25 = 2;

const chartOption = 0;
const menuType = 'environment';

const CO2_BAR_COLOR = colorData['co2_color']['bar'];
const CO2_LINE_COLOR = colorData['co2_color']['line'];
const FINEDUST_BAR_COLOR = colorData['finedust_color']['bar'];
const FINEDUST_LINE_COLOR = colorData['finedust_color']['line'];
const CHART_MEASURE_LABEL = '실측값';
const CHART_STANDARD_LABEL = '기준값';
const CO2_UNIT = 'ppm';
const FINEDUST_UNIT = '㎍/m³';

const CHART_LABELS = [CHART_MEASURE_LABEL, CHART_STANDARD_LABEL];
const CHART_UNITS = [CO2_UNIT, FINEDUST_UNIT];

//------------------------------------------------------------------------------------------------------------
// Input
//------------------------------------------------------------------------------------------------------------
const $inputPopupCo2 = $("#input_popup_co2");
const $inputPopupPM25 = $("#input_popup_pm25");
const $popupStandard = $(".popup-standard");

//------------------------------------------------------------------------------------------------------------
// Labels
//------------------------------------------------------------------------------------------------------------
const $dustCo2 = $("#dust_co2");
const $dustPM25 = $("#dust_pm25");
const $dustCo2Imoticon	= $("#dust_co2_imoticon");
const $dustPM25Imoticon	= $("#dust_pm25_imoticon");
const $totalCo2 = $("#total_co2");
const $totalPM25 = $("#total_pm25");
const labels = [$totalCo2, $totalPM25];

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendCo2 = $("#div_chart_legend_co2");
const $divChartLegendFinedust = $("#div_chart_legend_finedust");

//------------------------------------------------------------------------------------------------------------
// Chart
//------------------------------------------------------------------------------------------------------------
const canvasChartPM10Id = 'canvas_chart_co2';
const co2BarColor = CO2_BAR_COLOR;
const co2LineColor= CO2_LINE_COLOR;

let co2Chart = createBarLineChart(canvasChartPM10Id, co2BarColor, co2LineColor, CHART_MEASURE_LABEL, CHART_STANDARD_LABEL, 0);
co2Chart.update();

const canvasChartPM25Id = 'canvas_chart_pm25';
const pm25BarColor = FINEDUST_BAR_COLOR;
const pm25LineColor = FINEDUST_LINE_COLOR;

let pm25Chart = createBarLineChart(canvasChartPM25Id, pm25BarColor, pm25LineColor, CHART_MEASURE_LABEL, CHART_STANDARD_LABEL, 0);
pm25Chart.update();

// 화면에 표시할 그래프를 배열에 추가
let charts = [co2Chart, pm25Chart];

//------------------------------------------------------------------------------------------------------------
// 팝업
//------------------------------------------------------------------------------------------------------------
let popupStandardParams = {
    beforeCallback: null,
    openCallback: null,
    closeCallback: null,
    $link: $formPopupSetting
};

let standardFormPopup = module.popup(popupStandardParams);