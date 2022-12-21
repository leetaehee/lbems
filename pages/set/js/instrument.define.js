//-----------------------------------------------------------------------------------------------------
// Ajax
//-----------------------------------------------------------------------------------------------------
const requester = 'set';
const command = 'instrument';

//-----------------------------------------------------------------------------------------------------
// Variable & Const
//-----------------------------------------------------------------------------------------------------
const defaultFloor = '';
const floorKeyData = CONFIGS['floor_key_data'];
const MONITORING_ERROR_CLASS = 'report_error';
const MONITORING_NORMAL_CLASS = 'report_normal';
const MONITORING_BTN_PREFIX = 'btn_monitoring_floor_';
const EMPTY_TD_CONTENTS = '- 계측기 정보가 존재하지 않습니다. -';
const WARN_BG_SELECTOR = 'warn-bg-monitoring';
const WARN_COLOR_SELECTOR = 'bcO';

//-----------------------------------------------------------------------------------------------------
// Label
//-----------------------------------------------------------------------------------------------------
const $labelTotalInstrumentCount = $("#label_total_instrument_count");
const $labelInstrumentNomalCount = $("#label_instrument_nomal_count");
const $labelInstrumentDefectCount = $("#label_instrument_defect_count");

//-----------------------------------------------------------------------------------------------------
// Button
//-----------------------------------------------------------------------------------------------------
const $btnInstrumentFirstPage = $("#btn_instrument_first_page");
const $btnInstrumentPrevPage = $("#btn_instrument_prev_page");
const $btnInstrumentNextPage = $("#btn_instrument_next_page");
const $btnInstrumentLastPage = $("#btn_instrument_last_page");

//-----------------------------------------------------------------------------------------------------
// Page
//-----------------------------------------------------------------------------------------------------
const pageCount = 5;
const viewPageCount = 13;
const startPage = 1;

//-----------------------------------------------------------------------------------------------------
// Tables
//-----------------------------------------------------------------------------------------------------
const $tbodyInstrument = $("#tbody_instrument");

//-----------------------------------------------------------------------------------------------------
// DIV
//-----------------------------------------------------------------------------------------------------
const $divInstrumentConnectionGroup = $("#div_instrument_connection_group");