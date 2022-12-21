//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'report';
const command = 'report_floor';

//----------------------------------------------------------------------------------------------
// Variable & Const
//----------------------------------------------------------------------------------------------
const defaultOption = CONFIGS['report']['floor_menu']['option'];

//----------------------------------------------------------------------------------------------
// Arrays
//----------------------------------------------------------------------------------------------
const FLOOR_DATA = CONFIGS['floor_name'];

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLabel = $(".div_chart_label");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $selectYear = $("#select_year");

const $labelUnitYear = $("#label_unit_year");
const $labelUnitMonth = $("#label_unit_month");
const $labelUnitDay = $("#label_unit_day");
const $labelUnitRaw = $("#label_unit_raw");
const unitLabels = [$labelUnitYear, $labelUnitMonth, $labelUnitDay, $labelUnitRaw];

//----------------------------------------------------------------------------------------------
// Excel
//----------------------------------------------------------------------------------------------
const excelFileName = "층별 사용 현황";
const $btnExcelYear = $("#btn_excel_year");
const $btnExcelMonth = $("#btn_excel_month");
const $btnExcelDay = $("#btn_excel_day");
const $btnExcelRaw = $("#btn_excel_raw");
const excelButtons = [$btnExcelYear, $btnExcelMonth, $btnExcelDay, $btnExcelRaw];

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const canvasChartYearId = 'canvas_chart_year';
let chartYear = createReportBarStackFloorChart(canvasChartYearId, 0);

const canvasChartMonthId = 'canvas_chart_month';
let chartMonth = createReportBarStackFloorChart(canvasChartMonthId, 1);

const canvasChartDayId = 'canvas_chart_day';
let chartToday = createReportBarStackFloorChart(canvasChartDayId, 2);

const canvasChartRawId = 'canvas_chart_raw';
let chartHour = createReportBarStackFloorChart(canvasChartRawId, 3);

const charts = [chartYear, chartMonth, chartToday, chartHour];
const colors = Object.values(CONFIGS['report']['floor_menu']['floor_color']);


