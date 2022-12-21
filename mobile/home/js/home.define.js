//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'home';
const command = 'm_home';

//----------------------------------------------------------------------------------------------
// li
//----------------------------------------------------------------------------------------------
const $liUsage = $("#li_usage");

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelIndependenceGrade = $("#label_independence_grade");
const $labelIndependencePercent = $("#label_independence_percent");

const $labelElectricTarget = $("#label_electric_target");
const $labelNowMonthElectricUsed = $("#label_now_month_electric_used");
const $labelLastMonthElectricUsed = $("#label_last_month_electric_used");
const $labelNowMonthElectricPrice = $("#label_now_month_electric_price");
const $labelLastMonthElectricPrice = $("#label_last_month_electric_price");
const $labelUnit = $(".label_unit");

const $labelPredayConsumptionUsed = $("#label_preday_consumption_used");
const $labelPredayProductionUsed = $("#label_preday_production_used");
const $labelMonthConsumptionUsed = $("#label_month_consumption_used");
const $labelMonthProductionUsed = $("#label_month_production_used");
const $labelYearConsumptionUsed = $("#label_year_consumption_used");
const $labelYearProductionUsed = $("#label_year_production_used");

//----------------------------------------------------------------------------------------------
// Graph
//----------------------------------------------------------------------------------------------
const $progressGraphPredayProduction = $("#progress_graph_preday_production");
const $progressGraphMonthProduction = $("#progress_graph_month_production");
const $progressGraphYearProduction = $("#progress_graph_year_production");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasIndependence = 'canvas_independence';
const pieIndependenceColor1 = '66,110,90';
const pieIndependenceColor2 = '221,221,221';
const labelsIndependence = ['온실가스', ''];

let chartIndependence = createPieChart(canvasIndependence, labelsIndependence, pieIndependenceColor1, pieIndependenceColor2,'','');
chartIndependence.update([0,100]);

// 데이터없음 항목도 처리 해야하므로 +1을 넣음
const USAGE_DEFAULT_ITEM_COUNT = (CONFIGS['mobile']['usage']['label'].length) + 1;

const USAGE_DEFAULT_ARRAY = Array.from({length:USAGE_DEFAULT_ITEM_COUNT}, () => 0);
USAGE_DEFAULT_ARRAY[0] = 100;

const canvasUsePieEnergy = 'canvas_distribution';

const LABEL_USAGES = CONFIGS['mobile']['usage']['label'];
const COLOR_USAGES = CONFIGS['mobile']['usage']['color'];

let chartPieUse = createUsePieChart(canvasUsePieEnergy);
chartPieUse.update(LABEL_USAGES, COLOR_USAGES, USAGE_DEFAULT_ARRAY);