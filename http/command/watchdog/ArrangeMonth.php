<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ArrangeMonth 월통계 생성
 */
class ArrangeMonth extends Command
{
    /**
     * ArrangeMonth Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeMonth Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $today = date('Ymd');
        //$today = date('Ymd', strtotime('20220901'));

        $yesterday = Utility::getInstance()->addDay($today, -1);
        $tableCount = count(Config::SENSOR_TYPES);

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $i;

            // 에너지원 모든 센서 조회
            $rAllSensorQ = $this->emsQuery->getQueryAllSensorData($option);
            $sensors = $this->query($rAllSensorQ);

            for ($j = 0; $j < count($sensors); $j++) {
                $complexCodePk = $sensors[$j]['complex_code_pk'];
                $sensorNo = $sensors[$j]['sensor_sn'];

                // 마감일 정보 조회
                $dueDayInfo = $this->getEndDayData($complexCodePk, $option, $yesterday);

                $dueDay = $dueDayInfo['due_day'];
                $isEndDay = $dueDayInfo['is_end_day'];

                $temp = Utility::getInstance()->getDateFromDueday($dueDayInfo['db_due_day'], $yesterday);
                $checkDueDate = date('Ymd', strtotime($temp['end'] . '+1 day'));
                if ($checkDueDate !== $today) {
                    // 마감일 하루 뒤에만 실행
                    continue;
                }

                $startDate = $temp['start'];
                $endDate = $temp['end'];
                $dueDate = $temp['due_date'];

                // 월통계 생성
                $this->makeMonthData($complexCodePk, $option, $sensorNo, $startDate, $endDate, $dueDate, $dueDay, $isEndDay);
            }
        }

        $this->close();

        $this->data = [];
        return true;
    }

    /**
     * 마감일 정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $yesterday
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
    private function getEndDayData(string $complexCodePk, int $option, string $yesterday) : array
    {
        $fcData = [
            'due_day' => date('t'),
            'is_end_day' => true,
        ];

        $fcDueDayQ  = $this->emsQuery->getDueday($option, $complexCodePk);
        $d = $this->query($fcDueDayQ);
        $dueDay = $d[0]['closing_day'];

        $fcData['db_due_day'] = $dueDay;

        if ($dueDay === 99) {
            $fcData['due_day'] = date('t', strtotime($yesterday)); // 말일인 경우 예외처리
            $fcData['is_end_day'] = true;

            return $fcData;
        }

        $fcData['due_day'] = $dueDay;
        $fcData['is_end_day'] = false;

        return $fcData;
    }

    /**
     * 월 통계 추가
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sensorNo
     * @param string $startDate
     * @param string $endDate
     * @param string $dueDate
     * @param string $dueDay
     * @param bool $isEndDay
     *
     * @throws \Exception
     */
    private function makeMonthData(string $complexCodePk, int $option, string $sensorNo, string $startDate, string $endDate, string $dueDate, string $dueDay, bool $isEndDay) : void
    {
        $fcResult = [
            'val_st' => 0,
            'val_ed' => 0,
            'val' => 0,
        ];

        $previousMonth = $this->getPreviousMonth($isEndDay, $dueDay, $dueDate, $startDate);

        // 전월 데이터가 있는 경우 현재 누적량 - 전월 사용량으로 계산..
        $query = $this->emsQuery->getQueryMonthDataBySensorTable($complexCodePk, $option, $previousMonth, $startDate, $endDate, $sensorNo);
        $d = $this->query($query);

        if (count($d) === 0) {
            return;
        }

        $fcResult['val_st'] = $d[0]['val_st'];
        $fcResult['val_ed'] = $d[0]['val_ed'];
        $fcResult['val'] = $d[0]['val'];

        if ($d[0]['val'] == 0 || $d[0]['val_st'] == '') {
            $fcResult['val_st'] = $d[0]['min_val'];
            $fcResult['val'] = $d[0]['val_ed'] - $d[0]['min_val'];
        }

        $monthStartUsed = $d[0]['min_val'];

        if ((int)$monthStartUsed === 0) {
            // 계측기 초기화되 경우
            $rReplaceQ = $this->emsQuery->getQuerySelectSensorReplaceDate($complexCodePk, $option, $sensorNo, $startDate, $endDate);
            $rReplaceData = $this->query($rReplaceQ);

            $replaceDate = $rReplaceData[0]['replace_date'];
            if (empty($replaceDate) === true || is_null($replaceDate) === true) {
                return;
            }

            $replaceDate = date('YmdHis', strtotime($replaceDate. '+1 hours'));

            /*
             * 계측기 초기화 된 경우 날자 조회 후 다음날부터 검색
             * - 변경된 날의 경우 기존이랑 섞여서 데이터 못 찾음
             */
            $query = $this->emsQuery->getQueryMeterMonthData($complexCodePk, $option, $replaceDate, $endDate, $sensorNo);
            $t = $this->query($query);

            if (count($t) === 0) {
                return;
            }

            $fcResult['val_st'] = $t[0]['val_st'];
            $fcResult['val_ed'] = $t[0]['val_ed'];
            $fcResult['val'] = ($t[0]['val'] < 0) ? 0 : $t[0]['val'];
        }

        $fcResult['val'] = ($fcResult['val'] < 0) ? 0 : $fcResult['val'];

        // 추가
        $query = $this->emsQuery->getQueryUpdateOrInsertMeterMonth($option, $startDate, $endDate, $sensorNo, $dueDay, $fcResult);
        $this->squery($query);
    }

    /**
     * 이전 월 조회
     *
     * @param bool $isEndDay
     * @param int $dueDay
     * @param string $dueDate
     * @param string $date
     *
     * @return string
     */
    private function getPreviousMonth(bool $isEndDay, int $dueDay, string $dueDate, string $date) : string
    {
        $tempStDt = $date;

        $lDay = date('j');

        if ($isEndDay === false) {
            $yearDueDay = (int)date('t', strtotime($dueDate));
            if ($yearDueDay === 28
                && $lDay < $dueDay
                && (date('Ym', strtotime($dueDate)) === date('Ym', strtotime($tempStDt)))) {
                // 마감일이 28일이면서 2월달처럼 경우, 시작일이 다음달로 이월되는경우
                $tempStDt = date('Ymd', strtotime($tempStDt . '-1 month'));
            } else {
                $tempStDt = date('Ymd', strtotime($tempStDt . '-1 day'));
            }
        }

        $previousMonth = date('Ym', strtotime($tempStDt));

        return $previousMonth;
    }
}