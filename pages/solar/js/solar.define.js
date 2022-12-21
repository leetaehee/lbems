//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'solar';
const command = 'solar';

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const periods = ['year', 'month', 'daily', 'hour'];
const colorData = CONFIGS['solar']['chart_color'];

//-----------------------------------------------------------------------------------------------------
// Forms
//-----------------------------------------------------------------------------------------------------
const $formExcel = $("#form-excel");
const $formRequester = $("#form-requester");
const $formRequest = $("#form-request");
const $formParam = $("#form-param");

//----------------------------------------------------------------------------------------------
// Variables & Const
//----------------------------------------------------------------------------------------------
const DEFAULT_PERIOD = 2;
const DEFAUT_LOADING = 1;
const SOLAR_PRODUCTION_COLOR = colorData['solar_compare_color']['production'];
const SOLAR_CONSUMPTION_COLOR = colorData['solar_compare_color']['consumption'];
const SOLAR_EFFICIENCY_COLOR = colorData['efficiency_color'];
const SOLAR_PRODUCTION_LABEL = '생산량';
const SOLAR_CONSUMPTION_LABEL = '소비량';
const SOLAR_LABELS = [SOLAR_PRODUCTION_LABEL, SOLAR_CONSUMPTION_LABEL];

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelSolarDailyProductionUsed = $("#label_solar_daily_production_used");
const $labelSolarDailyConsumptionUsed = $("#label_solar_daily_consumption_used");
const $labelSolarMonthProductionUsed  = $("#label_solar_month_production_used");
const $labelSolarMonthConsumptionUsed = $("#label_solar_month_consumption_used");
const $labelSolarYearProductionUsed  = $("#label_solar_year_production_used");
const $labelSolarYearConsumptionUsed = $("#label_solar_year_consumption_used");

const $labelEfficiencyPercent = $("#label_efficiency_percent");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const $btnRadioPeriod = $(".radio_period");
const $btnSolarExcel = $("#btn_solar_excel");

//----------------------------------------------------------------------------------------------
// Radio
//----------------------------------------------------------------------------------------------
const $btnPeriodDaily = $("#btn_period_daily");
const $btnPeriodMonth = $("#btn_period_month");
const $btnPeriodYear = $("#btn_period_year");

//----------------------------------------------------------------------------------------------
// Input
//----------------------------------------------------------------------------------------------
const $startDate = $("#start_date");
const $endDate = $("#end_date");

//----------------------------------------------------------------------------------------------
// SelectBox
//----------------------------------------------------------------------------------------------
const $startMonthYm = $("#start_month_ym");
const $startMonth = $("#start_month");
const $endMonthYm = $("#end_month_ym");
const $endMonth = $("#end_month");
const $startYearYm = $("#start_year_ym");

//----------------------------------------------------------------------------------------------
// DIV
//----------------------------------------------------------------------------------------------
const $divChartLegendSolar = $("#div_chart_legend_solar");

//----------------------------------------------------------------------------------------------
// Chart  
//----------------------------------------------------------------------------------------------
const defaultColor = CONFIGS['default_color'];

// 태양광 소비 생산 그래프(월)
const canvasSolarPieId = 'canvas_pie_solar';
const productionColor = SOLAR_PRODUCTION_COLOR;
const consumptionColor = SOLAR_CONSUMPTION_COLOR;
const solarLables = ['미설정', SOLAR_LABELS[0], SOLAR_LABELS[1]];
let solarGraph = createPieChart(canvasSolarPieId, solarLables, defaultColor, productionColor, consumptionColor);
solarGraph.update([100, 0, 0]);

// 실시간 발전 효율 그래프 
const canvasEfficiencyPieId = 'canvas_half_pie_efficiency';
const efficiencyColor = SOLAR_EFFICIENCY_COLOR;
const efficiencyLabels = ['효율', '미설정'];
let efficiencyGraph = createHalfPieChart(canvasEfficiencyPieId, efficiencyLabels, efficiencyColor, defaultColor);
efficiencyGraph.update([0, 100]);

// 상세그래프
const canvasDetailStackBarId = 'canvas_detail_stack_bar';
let detailGraph = createBarStackLineChart(canvasDetailStackBarId, productionColor, consumptionColor);
detailGraph.update();