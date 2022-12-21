//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'report';
const command = 'report_energy';

//----------------------------------------------------------------------------------------------
// Const & Variables
//----------------------------------------------------------------------------------------------
const btnStartIndex = CONFIGS['energy_start_index'];
const defaultEnergyKey = CONFIGS['energy_start_key'];
const defaultDong = 'all';
const defaultFloor = 'all';
const defaultRoom = 'all';
const isDisplayPrice = CONFIGS['report']['is_display_price'] != undefined ? CONFIGS['report']['is_display_price'] : true;

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectBuildingDong = $("#select_building_dong");
const $selectBuildingFloor = $("#select_building_floor");
//const $selectBuildingRoom = $("#select_building_room");
const $selectYear = $("#select_year");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegendYear = $("#div_chart_legend_year");
const $divChartLegendMonth = $("#div_chart_legend_month");
const $divChartLegendDay = $("#div_chart_legend_day");
const $divChartLegendRaw = $("#div_chart_legend_raw");
const divChartLegends = [$divChartLegendYear, $divChartLegendMonth, $divChartLegendDay, $divChartLegendRaw];

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");

//----------------------------------------------------------------------------------------------
// Excel
//----------------------------------------------------------------------------------------------
const excelFileName = '에너지원별 사용 현황';
const $btnExcelYear = $("#btn_excel_year");
const $btnExcelMonth = $("#btn_excel_month");
const $btnExcelDay = $("#btn_excel_day");
const $btnExcelRaw = $("#btn_excel_raw");
const excelButtons = [$btnExcelYear, $btnExcelMonth, $btnExcelDay, $btnExcelRaw];

//----------------------------------------------------------------------------------------------
// Chart
//----------------------------------------------------------------------------------------------
const chartData = CONFIGS['report']['usage_menu']['chart_color'];
const chartUsedLabel = '사용량';
const chartPriceLabel = '사용 요금';

const canvasChartYearId = 'canvas_chart_year';
const chartYearBarColor = chartData['bar']['year'];
const chartYearLineColor = chartData['line']['year'];
const chartYearClickColor = chartData['click_color']['year'];

let chartYear = createBarLineChart(canvasChartYearId, chartYearBarColor, chartYearLineColor, chartYearClickColor, chartUsedLabel, chartPriceLabel, isDisplayPrice, 0);
chartYear.update();

const canvasChartMonthId = 'canvas_chart_month';
const chartMonthBarColor = chartData['bar']['month'];
const chartMonthLineColor = chartData['line']['month'];
const chartMonthClickColor = chartData['click_color']['month'];

let chartMonth = createBarLineChart(canvasChartMonthId, chartMonthBarColor, chartMonthLineColor, chartMonthClickColor, chartUsedLabel, chartPriceLabel, isDisplayPrice, 1);
chartMonth.update();

const canvasChartDayId = 'canvas_chart_day';
const chartDayBarColor = chartData['bar']['day'];
const chartDayLineColor = chartData['line']['day'];
const chartDayClickColor = chartData['click_color']['day'];

let chartDay = createBarLineChart(canvasChartDayId, chartDayBarColor, chartDayLineColor, chartDayClickColor, chartUsedLabel, chartPriceLabel, isDisplayPrice, 2);
chartDay.update();

const canvasChartRawId = 'canvas_chart_raw';
const chartRawBarColor = chartData['bar']['raw'];
const chartRawLineColor = chartData['line']['raw'];
const chartRawClickColor = chartData['click_color']['raw'];

let chartRaw = createBarLineChart(canvasChartRawId, chartRawBarColor, chartRawLineColor, chartRawClickColor, chartUsedLabel, chartPriceLabel, isDisplayPrice, 3);
chartRaw.update();

let charts = [chartYear, chartMonth, chartDay, chartRaw];