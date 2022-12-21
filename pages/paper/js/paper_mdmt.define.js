//-----------------------------------------------------------------------------------------------------
// Ajax
//-----------------------------------------------------------------------------------------------------
const requester = 'paper';
const command = 'paper';

//-----------------------------------------------------------------------------------------------------
// Const 
//-----------------------------------------------------------------------------------------------------
const grp = 'all';
const period = 2;
const timelineFlag = 1;

//-----------------------------------------------------------------------------------------------------
// Calendar
//-----------------------------------------------------------------------------------------------------
// 단지별 bems를 처음 시작한 날짜- 전월에 마지막 일로 설정할 것
const systemOpenStartDate = new Date('2020-06-30');

//-----------------------------------------------------------------------------------------------------
// Array 
//-----------------------------------------------------------------------------------------------------
const floors = ['1f', '2f', '3f', 'ph'];
const periods = ['year', 'month', 'daily', 'hour'];
const floorNames = ['1층', '2층', '3층', '옥탑'];
const periodTimeUnits = ['month', 'day', 'day'];

//-----------------------------------------------------------------------------------------------------
// Labels
//-----------------------------------------------------------------------------------------------------
const $labelTotalUsed = $("#label_total_used");
const $labelTotalPrice = $("#label_total_price");
const $labelAverageUsed = $("#label_average_used");
const $labelAveragePrice = $("#label_average_price");
const $labelMinimumUsed = $("#label_minimum_used");
const $labelMinimumPrice = $("#label_minimum_price");
const $labelMaximumUsed = $("#label_maximum_used");
const $labelMaximumPrice = $("#label_maximum_price");
const $labelPeriodTimeUnit = $("#label_period_time_unit");

//-----------------------------------------------------------------------------------------------------
// Forms
//-----------------------------------------------------------------------------------------------------
const $formExcel = $("#form-excel");
const $formRequester = $("#form-requester");
const $formRequest = $("#form-request");
const $formParam = $("#form-param");

//-----------------------------------------------------------------------------------------------------
// Buttons
//-----------------------------------------------------------------------------------------------------
const $btnSearch = $("#btn_search");
const $btnPaperExcel = $("#btn_paper_excel");
const $btnRadioPeriod = $(".radio_period");

const $btnSearchToday = $("#btn_search_today");
const $btnSearchYesterday = $("#btn_search_yesterday");
const $btnSearchLastWeek = $("#btn_search_last_week");
const $btnSearchLastMonth = $("#btn_search_last_month");
const $btnSearch1Week = $("#btn_search_1_week");
const $btnSearch30Day = $("#btn_search_30_day");

const $btnFoldFloorGroup = $(".pLnb > ul > li > ul#btn_items_fold > li.dept > a");

//-----------------------------------------------------------------------------------------------------
// Radio
//-----------------------------------------------------------------------------------------------------
const $btnPeriodMinute = $("#btn_period_minute");
const $btnPeriodDaily = $("#btn_period_daily");
const $btnPeriodMonth = $("#btn_period_month");
const $btnPeriodYear = $("#btn_period_year");

//-----------------------------------------------------------------------------------------------------
// Input
//-----------------------------------------------------------------------------------------------------
const $startDate = $("#start_date");
const $endDate = $("#end_date");

//-----------------------------------------------------------------------------------------------------
// div, li,  span 등 
//-----------------------------------------------------------------------------------------------------
const $periodEnjoyBox = $("#period_enjoy_box");

//-----------------------------------------------------------------------------------------------------
// SelectBox
//-----------------------------------------------------------------------------------------------------
const $startHour = $("#start_hour");
const $startMonthYm = $("#start_month_ym");
const $startMonth = $("#start_month");
const $endMonthYm = $("#end_month_ym");
const $endMonth = $("#end_month");
const $startYearYm = $("#start_year_ym");

//-----------------------------------------------------------------------------------------------------
// CheckBox
//-----------------------------------------------------------------------------------------------------
/* 세부 체크박스 항목 */
const $itemCheckbox = $(".checkbox_item");

/* 전체 */
const $checkboxElectric = $("#checkbox_electric");
const $checkboxReport = $(".checkbox_report");

/* 층별 */
const $checkboxFloor = $(".checkbox_floor");
const $checkbox1f = $("#checkbox_1f_group");
const $checkbox2f = $("#checkbox_2f_group");
const $checkbox3f = $("#checkbox_3f_group");
const $checkboxPh = $("#checkbox_ph_group");
const $floors = [$checkbox1f, $checkbox2f, $checkbox3f, $checkboxPh];

//-----------------------------------------------------------------------------------------------------
// Graph Colors
//-----------------------------------------------------------------------------------------------------

/**
 * 네이밍 생성 기준
 *
 * g : graph 
 * 층 식별이름  
 * c : color 
 *
 * example) gElectricLightC 조명 그래프에 대한 색상 
 */ 

/* 전체 선택 했을 때 그래프 색상 */
const g1fC = "246,170,121"; // 1층 
const g2fC = "231,234,143"; // 2층
const g3fC = "143,234,222"; // 3층 
const gphC = "234,143,188"; // 옥탑

/* 세부 선택 했을 때 그래프 색상 */
const energyTypeColors = {
	"electric_light" : {
		"label_name" : "조명",
		"color" : "232,114,105", 
	},
	"electric_elechot" : {
		"label_name" : "전열",
		"color" : "246,170,121",
	},
	"electric_cold" : {
		"label_name" : "냉난방", 
		"color" : "212,234,143",
	},
	"electric_vent" : {
		"label_name" : "환기",
		"color" : "154,234,143", 
	},
	"electric_elevator" : {
		"label_name" : "운송(승강기)",
		"color" : "143,234,222",
	},
	"electric_ehp" : {
		"label_name" : "EHP",
		"color" : "143,177,234", 
	},
	"electric_water_heater" : {
		"label_name" : "전기온수기",
		"color" : "156,143,234",
	},
	"electric_heating" : {
		"label_name" : "난방",
		"color" : "234,143,188", 
	},
};

//-----------------------------------------------------------------------------------------------------
// Graphs
//-----------------------------------------------------------------------------------------------------
/* chart js 에서 제공되는 범주 사용여부-  사용 true, 사용안함 false */
const isUseLegend = true; 

/* 전체 */
const canvasChartAllReport = "canvas_chart_report";

// bar stack 그래프
let chartAllReport = createPaperBarStackAllChart(canvasChartAllReport, floorNames, g1fC, g2fC, g3fC, gphC, isUseLegend);
chartAllReport.update();

const canvasChartFloorReport = "canvas_chart_report";

// line 그래프 (전체)
let chartFloorReport = createPaperBarStackFloorChart(canvasChartFloorReport, isUseLegend);