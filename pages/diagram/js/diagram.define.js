//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'diagram';
const command = CONFIGS['diagram']['command'];
const keyCommand = 'diagram_key';

//----------------------------------------------------------------------------------------------
// Variables
//----------------------------------------------------------------------------------------------
const DEFAULT_DATE_TYPE = 2;
const DEFAULT_FLOOR = 'all';
const YEAR_DATE_TYPE = 0;
const MONTH_DATE_TYPE = 1;
const DAILY_DATE_TYPE = 2;
const LABEL_PREFIX = 'label_';
const USED_SUFFIX = '_used';
const FLOOR_SUFFIX = '_floor_used';
const DISTRIBUTION_SUFFIX = '_distribution';
const IS_SHOW_DETAIL = CONFIGS['diagram']['is_show_detail'];
const FLOOR_DATA = CONFIGS['floor'];

//----------------------------------------------------------------------------------------------
// Buttons
//----------------------------------------------------------------------------------------------
const $btnDaily = $("#btn_daily");
const $btnMonth = $("#btn_month");
const $btnYear = $("#btn_year");
const buttons = [$btnYear, $btnMonth, $btnDaily];

//----------------------------------------------------------------------------------------------
// Labels
//----------------------------------------------------------------------------------------------
const $labelIndependenceGrade = $("#label_independence_grade");
const $labelIndependenceRate = $("#label_independence_rate");
const $labelAllFloorUsed = $("#label_all_floor_used");
const $labelBuildingName = $("#label_building_name");

//----------------------------------------------------------------------------------------------
// Array
//----------------------------------------------------------------------------------------------
const $labelFloors = [];
const $labelUseds = [];
const $labelDistributions = [];