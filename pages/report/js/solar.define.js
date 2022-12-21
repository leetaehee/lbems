//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = "report";
const command = "report_energy";

//----------------------------------------------------------------------------------------------
// Variable & Const
//----------------------------------------------------------------------------------------------
const defaultOption = 11;
const isDisplayPrice = CONFIGS['report']['is_display_price'] != undefined ? CONFIGS['report']['is_display_price'] : true;

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const btnStartIndex = 11;
const $btnSearch = $("#btn_search");
const $selectYear = $("#select_year");

//----------------------------------------------------------------------------------------------
// Excel
//----------------------------------------------------------------------------------------------
const excelFileName = "태양광 발전 현황";
const $btnExcelYear = $("#btn_excel_year");
const $btnExcelMonth = $("#btn_excel_month");
const $btnExcelDay = $("#btn_excel_day");
const $btnExcelRaw = $("#btn_excel_raw");
const excelButtons = [$btnExcelYear, $btnExcelMonth, $btnExcelDay, $btnExcelRaw];

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendYear = $("#div_chart_legend_year");
const $divChartLegendMonth = $("#div_chart_legend_month");
const $divChartLegendDay = $("#div_chart_legend_day");
const $divChartLegendRaw = $("#div_chart_legend_raw");
const divChartLegends = [$divChartLegendYear, $divChartLegendMonth, $divChartLegendDay, $divChartLegendRaw];

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const chartData = CONFIGS['report']['usage_menu']['chart_color'];
const chartSolarUsedLabel = '발전량';
const chartSolarPriceLabel = '절감 비용';

const canvasChartYearId = 'canvas_chart_year';
const chartYearBarColor = chartData['bar']['year'];
const chartYearLineColor = chartData['line']['year'];
const chartYearClickColor = chartData['click_color']['year'];

let chartYear = createBarLineChart(canvasChartYearId, chartYearBarColor, chartYearLineColor, chartYearClickColor, chartSolarUsedLabel, chartSolarPriceLabel, isDisplayPrice, 0);
chartYear.update();

const canvasChartMonthId = 'canvas_chart_month';
const chartMonthBarColor = chartData['bar']['month'];
const chartMonthLineColor = chartData['line']['month'];
const chartMonthClickColor = chartData['click_color']['month'];

let chartMonth = createBarLineChart(canvasChartMonthId, chartMonthBarColor, chartMonthLineColor, chartMonthClickColor, chartSolarUsedLabel, chartSolarPriceLabel, isDisplayPrice, 1);
chartMonth.update();

const canvasChartDayId = 'canvas_chart_day';
const chartDayBarColor = chartData['bar']['day'];
const chartDayLineColor = chartData['line']['day'];
const chartDayClickColor = chartData['click_color']['day'];

let chartDay = createBarLineChart(canvasChartDayId, chartDayBarColor, chartDayLineColor, chartDayClickColor, chartSolarUsedLabel, chartSolarPriceLabel, isDisplayPrice, 2);
chartDay.update();

const canvasChartRawId = 'canvas_chart_raw';
const chartRawBarColor = chartData['bar']['raw'];
const chartRawLineColor = chartData['line']['raw'];
const chartRawClickColor = chartData['click_color']['raw'];

let chartRaw = createBarLineChart(canvasChartRawId, chartRawBarColor, chartRawLineColor, chartRawClickColor, chartSolarUsedLabel, chartSolarPriceLabel, isDisplayPrice, 3);
chartRaw.update();

let charts = [chartYear, chartMonth, chartDay, chartRaw];