//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "dashboard";
const command = "dashboard";

//----------------------------------------------------------------------------------------------
// Variable & Const
//----------------------------------------------------------------------------------------------
const defaultOption = CONFIGS['dashboard']['option'];
const defaultDateType = CONFIGS['dashboard']['date_type'];
const isUseFinedustSensor = CONFIGS['is_use_finedust_sensor'];

const CHART_ENERGY_COLORS = CONFIGS['dashboard']['energy_graph_color'];
const currentColor = CHART_ENERGY_COLORS['current_line'];
const previousColor = CHART_ENERGY_COLORS['previous_line'];
const standardLineColor = CHART_ENERGY_COLORS['standard_line'];

const CHART_LEGENDS = {'현재' : currentColor, '이전' : previousColor, '기준값' : standardLineColor};

//----------------------------------------------------------------------------------------------
// Object
//----------------------------------------------------------------------------------------------
const ENERGY_CHART_PERCENTS  = {
    'graph_daily_independence' : 0,
    'graph_month_independence' : 0,
    'graph_year_independence' : 0,
};

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
// Button
//----------------------------------------------------------------------------------------------
const $btnSetElec = $("#btn_set_elec");
const $btnZero = $("#btn_zero");
const $btnDashboardDetail = $("#btn_dashboard_detail");

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectEnergy = $("#select_energy");

//----------------------------------------------------------------------------------------------
// table
//----------------------------------------------------------------------------------------------
const $tbodyFacility = $("#tbody_facility");

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelCo2Emission = $("#label_co2_emission");
const $labelIndPercent = $("#label_independent_percent");
const $labelIndGrade = $("#label_independent_grade");
const $labelProduce = $("#label_produce");
const $labelConsume = $("#label_consume");

const $labelTargetElec = $("#label_target_elec");
const $labelUsageElec = $("#label_usage_elec");
const $labelBeforeElec = $("#label_before_elec");
const $labelPriceElec = $("#label_price_elec");

const $labelAirPm10 = $("#label_air_pm10");
const $labelAirPm25 = $("#label_air_pm25");

const $labelCenterUse = $("#label_center_use");
const $labelCenterGen = $("#label_center_gen");
const $labelCenterDiff = $("#label_center_diff");

const $labelDailyConsumptionUsed = $("#label_daily_consumption_used");
const $labelDailyProductionUsed = $("#label_daily_production_used");
const $labelMonthConsumptionUsed = $("#label_month_consumption_used");
const $labelMonthProductionUsed = $("#label_month_production_used");
const $labelYearConsumptionUsed = $("#label_year_consumption_used");
const $labelYearProductionUsed = $("#label_year_production_used");

const $labelUnit = $(".label_unit");
const $labelFacilityTitle = $("#label_facility_title");
const $labelCo2Value = $("#label_co2_value");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendEnergy = $("#div_chart_legend_energy");
const $divChartLegendUsage = $("#div_chart_legend_usage");

//----------------------------------------------------------------------------------------------
// Graph
//----------------------------------------------------------------------------------------------
const $graphDailyIndependence = $("#graph_daily_independence");
const $graphMonthIndependence = $("#graph_month_independence");
const $graphYearIndependence = $("#graph_year_independence");

//----------------------------------------------------------------------------------------------
// Finedust
//----------------------------------------------------------------------------------------------
const $divDust = $("#div_dust");
const $spanDust = $("#span_dust");
const $labelDust = $("#label_dust");
const $divUltraDust = $("#div_ultra_dust");
const $spanUltraDust = $("#span_ultra_dust");
const $labelUltraDust = $("#label_ultra_dust");
const $divCo2 = $("#div_co2");
const $labelCo2 = $("#label_co2");
const $spanCo2 = $("#span_co2");
const $divUltraByCo2Dust = $("#div_ultra_by_co2_dust");
const $labelUltraByCo2Dust = $("#label_ultra_by_co2_dust");
const $spanUltraByCo2Dust = $("#span_ultra_by_co2_dust");

const fineDustLabel = ['좋음', '보통', '나쁨', '매우나쁨'];
const fineDustLevel = [30, 80, 150, 151];
const ultraDustLevel = [15, 50, 100, 101];
const finedustCo2Level = [450, 1000, 2000, 5000, 5001];
const finedustCo2Label = ['매우좋음', '좋음', '보통', '나쁨', '매우나쁨'];
const colorClasses = ['dust1', 'dust2', 'dust3', 'dust4'];
const colorTailClasses = ['dustb1', 'dustb2', 'dustb3', 'dustb4'];
const colorCo2Classes = ['dust6', 'dust1', 'dust2', 'dust3', 'dust4'];
const colorCo2TailClasses = ['dustb6', 'dustb1', 'dustb2', 'dustb3', 'dustb4'];
const fineDustColors = ['#80a4c3', '#7fc694', '#fab88c', '#ffa8a8'];
const defaultColor = '#32a1ff';
const fineDustMax = 151;

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasEnergyGraph = 'canvas_elec';
let chartEnergy = createLineY2Chart(canvasEnergyGraph, currentColor, previousColor, standardLineColor,  `현재`, `이전`, `기준값`, null);
chartEnergy.update();

const canvasPieEmission = 'canvas_pie_emission';
const pieEmissionColor1 = CONFIGS['dashboard']['zero_graph_color'];
const pieEmissionColor2 = CONFIGS['default_color'];
const labelsEmission = ['온실가스', ''];

let chartPieEmission = createPieChart(canvasPieEmission, labelsEmission, pieEmissionColor1, pieEmissionColor2,'','');
chartPieEmission.update([0, 100]);

// 데이터없음 항목도 처리 해야하므로 +1을 넣음
const DASHBOARD_DEFAULT_ITEM_COUNT = (CONFIGS['usage_labels'].length) + 1;

const DASHBOARD_DEFAULT_ARRAY = Array.from({length:DASHBOARD_DEFAULT_ITEM_COUNT}, () => 0);
DASHBOARD_DEFAULT_ARRAY[0] = 100;

const canvasUsePieEnergy = 'chart_pie_usage';

const LABEL_USAGES = CONFIGS['usage_labels'];
const COLOR_USAGES = CONFIGS['usage_colors'];
const CUTOUT_PERCENTAGE = CONFIGS['dashboard']['cutout_percentage'];

let chartPieUse = createUsePieChart(canvasUsePieEnergy, CUTOUT_PERCENTAGE);
chartPieUse.update(LABEL_USAGES, COLOR_USAGES, DASHBOARD_DEFAULT_ARRAY);

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