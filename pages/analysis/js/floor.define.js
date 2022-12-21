//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "analysis";
const command = "analysis_floor";

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const radioCheckedName = "input[name=radio_date]:checked";
const $dateSelect = $("#date_select");

const $btnUsedLineGraph = $("#btn_used_line_graph");
const $btnUsedBarStackGraph = $("#btn_used_bar_stack_graph");
const $btnPriceLineGraph = $("#btn_price_line_graph");
const $btnPriceBarStackGraph = $("#btn_price_bar_stack_graph");

//----------------------------------------------------------------------------------------------
// Variables & Const
//----------------------------------------------------------------------------------------------
const defaultOption = 0;

//----------------------------------------------------------------------------------------------
// Arrays
//----------------------------------------------------------------------------------------------
const FLOOR_DATA = CONFIGS['floor_name'];

//----------------------------------------------------------------------------------------------
// table
//----------------------------------------------------------------------------------------------
const $tbodyUsed = $("#tbody_used");
const $tbodyPrice = $("#tbody_price");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartUsedLabel = $("#div_chart_used_label");
const $divChartPriceLabel = $("#div_chart_price_label");

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelUnitNow = $(".label_unit_now");

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const charLineUsedId = 'canvas_chart_middle';
let chartUsedLine = createBarLineChart(charLineUsedId);

const charStackUsedId  = 'canvas_chart_middle';
let chartBarStackUsed = createFloorBarStackChart(charStackUsedId);

const charLinePriceId = 'canvas_chart_bottom';
let chartPriceLine = createBarLineChart(charLinePriceId);

const charStackPriceId  = 'canvas_chart_bottom';
let chartBarStackPrice = createFloorBarStackChart(charStackPriceId);

const usedCharts = [chartUsedLine, chartBarStackUsed];
const priceCharts = [chartPriceLine, chartBarStackPrice];

const colors = Object.values(CONFIGS['analysis']['floor_menu']['floor_color']);