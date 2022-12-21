//----------------------------------------------------------------------------------------------
// Ajax
//----------------------------------------------------------------------------------------------
const requester = 'prediction';
const command = 'm_prediction';

//----------------------------------------------------------------------------------------------
// Variables & Const
//----------------------------------------------------------------------------------------------
const DEFAULT_OPTION = 0;

const DAILY_USED_RATE = 10;
const WEEKLY_USED_RATE = 70;
const MONTH_USED_RATE = 300;
const DATE_TYPE_USED_RATES = {
    'daily' : DAILY_USED_RATE,
    'weekly' : WEEKLY_USED_RATE,
    'month' : MONTH_USED_RATE,
};

const BASE_VAL = 1.2;

//----------------------------------------------------------------------------------------------
// ProgressBars
//----------------------------------------------------------------------------------------------
const $labelDailyCurrentUsedProgressbar = $("#label_daily_current_used_progressbar");
const $labelDailyExceptUsedProgressbar = $("#label_daily_except_used_progressbar");
const $labelWeeklyCurrentUsedProgressbar = $("#label_weekly_current_used_progressbar");
const $labelWeeklyExceptUsedProgressbar = $("#label_weekly_except_used_progressbar");
const $labelMonthCurrentUsedProgressbar = $("#label_month_current_used_progressbar");
const $labelMonthExceptUsedProgressbar = $("#label_month_except_used_progressbar");