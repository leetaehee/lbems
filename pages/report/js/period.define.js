//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'report';
const command = 'report_period';

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const periods = ['year', 'month', 'daily', 'hour'];

//----------------------------------------------------------------------------------------------
// Const & Variables
//----------------------------------------------------------------------------------------------
const DEFAULT_DATE_TYPE = 2;
const defaultDong = 'all';
const defaultFloor = 'all';
const defaultRoom = 'all';
const defaultEmpty = '';
const isDisplayPrice = CONFIGS['report']['is_display_price'] != undefined ? CONFIGS['report']['is_display_price'] : true;
const solarObj = { solar : { option: 11, label: '태양광' }};
const excelFileName = '기간별 사용 현황';

//----------------------------------------------------------------------------------------------
// Select
//----------------------------------------------------------------------------------------------
const $selectEnergyType = $("#select_energy_type");
const $selectBuildingDong = $("#select_building_dong");
const $selectBuildingFloor = $("#select_building_floor");
const $startMonthYm = $("#start_month_ym");
const $startMonth = $("#start_month");
const $endMonthYm = $("#end_month_ym");
const $endMonth = $("#end_month");
const $startYearYm = $("#start_year_ym");

//----------------------------------------------------------------------------------------------
// Div
//----------------------------------------------------------------------------------------------
const $divChartLegend = $("#div_chart_legend");

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const $btnRadioPeriod = $(".radio_period");
const $btnExcelPeriod = $("#btn_excel_period");

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
// Chart
//----------------------------------------------------------------------------------------------
const chartData = CONFIGS['report']['usage_menu']['chart_color'];
const chartLabels = ['사용량', '발전량'];
const chartPriceLabels = ['사용 요금','발전 요금'];

const canvasChartYearId = 'canvas_period_bar';
const chartYearBarColor = chartData['bar']['year'];
const chartYearLineColor = chartData['line']['year'];
const chartYearClickColor = chartData['click_color']['year'];

let chartPeriod = createBarLineChart(canvasChartYearId, chartYearBarColor, chartYearLineColor, chartYearClickColor, chartLabels[0], chartPriceLabels[0], isDisplayPrice, 0);
chartPeriod.update();