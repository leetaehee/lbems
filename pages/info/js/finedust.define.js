//------------------------------------------------------------------------------------------------------------
// Ajax
//------------------------------------------------------------------------------------------------------------
const requester = 'info';
const command = 'info_finedust';

//------------------------------------------------------------------------------------------------------------
// Buttons
//------------------------------------------------------------------------------------------------------------
const $selectYearPM10 = $("#select_year_pm10");
const $selectYearPM25 = $("#select_year_pm25");
const $selectYearPM1 = $("#select_year_pm1_0");
const $formPopupSetting = $(".form_popup_setting");
const $btnSettingFinedust = $("#btn_setting_finedust");
const $btnButtonClose = $("#btn_button_close");
const $btnButtonSave = $("#btn_button_save");

//------------------------------------------------------------------------------------------------------------
// Array
//------------------------------------------------------------------------------------------------------------
const monthType	= ['pm10', 'pm25', 'pm1_0'];
const standardAlias = ['fs', 'fsu', 'fsu1'];
const colorData = CONFIGS['info']['chart_color'];

//------------------------------------------------------------------------------------------------------------
// Variable & Const
//------------------------------------------------------------------------------------------------------------
const periodStatusPM10 = 'daily';
const periodStatusPM25 = 'daily';
const periodStatusPM1 = 'daily';

const chartOption = 0;
const menuType = 'finedust';

const FINEDUST_BAR_COLOR = colorData['finedust_color']['bar'];
const FINEDUST_LINE_COLOR = colorData['finedust_color']['line'];
const CHART_MEASURE_LABEL = '실측값';
const CHART_STANDARD_LABEL = '기준값';
const FINEDUST_UNIT = '㎍/m³';

const CHART_LABELS = [CHART_MEASURE_LABEL, CHART_STANDARD_LABEL];

//------------------------------------------------------------------------------------------------------------
// Input
//------------------------------------------------------------------------------------------------------------
const $inputPopupPM10 = $("#input_popup_pm10");
const $inputPopupPM25 = $("#input_popup_pm25");
const $inputPopupPM1 = $("#input_popup_pm1");
const $popupStandard = $(".popup-standard");

//------------------------------------------------------------------------------------------------------------
// Labels
//------------------------------------------------------------------------------------------------------------
const $dustPM10 = $("#dust_pm10");
const $dustPM25 = $("#dust_pm25");
const $dustPM10Imoticon	= $("#dust_pm10_imoticon");
const $dustPM25Imoticon	= $("#dust_pm25_imoticon");
const $totalPM10 = $("#total_pm10");
const $totalPM25 = $("#total_pm25");
const $totalPM1 = $("#total_pm1_0");
const labels = [$totalPM10, $totalPM25, $totalPM1];

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendFinedust = $(".div_chart_legend_finedust");

//------------------------------------------------------------------------------------------------------------
// Chart
//------------------------------------------------------------------------------------------------------------
const canvasChartPM10Id = 'canvas_chart_pm10';
const pm10BarColor = FINEDUST_BAR_COLOR;
const pm10LineColor = FINEDUST_LINE_COLOR;

let pm10Chart = createBarLineChart(canvasChartPM10Id, pm10BarColor, pm10LineColor, CHART_MEASURE_LABEL, CHART_STANDARD_LABEL, 0);
pm10Chart.update();

const canvasChartPM25Id = 'canvas_chart_pm25';
const pm25BarColor = FINEDUST_BAR_COLOR;
const pm25LineColor = FINEDUST_LINE_COLOR;

let pm25Chart = createBarLineChart(canvasChartPM25Id, pm25BarColor, pm25LineColor, CHART_MEASURE_LABEL, CHART_STANDARD_LABEL, 0);
pm25Chart.update();

// 화면에 표시할 그래프를 배열에 추가
let charts = [pm10Chart, pm25Chart];

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