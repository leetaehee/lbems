<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ArrangeTime 금일 시간 데이터 생성
 */
class ArrangeTime extends Command
{
    /**
     * ArrangeTime constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeTime destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     *
     * @param $params
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $tableCount = count(Config::SENSOR_TYPES);

        $currentTime = date('YmdHis');
        //$currentTime = date('YmdHis', strtotime('20220624101500'));
        $beforeOneHourDate = date('YmdHis',  strtotime($currentTime . '-1 hours'));  // 한시간전
        $beforeOneHour = date('YmdH',  strtotime($beforeOneHourDate));

        $hour = (int)date('G', strtotime($beforeOneHourDate));

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $i;

            // 에너지원 모든 센서 조회
            $rAllSensorQ = $this->emsQuery->getQueryAllSensorData($option);
            $sensors = $this->query($rAllSensorQ);

            for ($j = 0; $j < count($sensors); $j++) {
                $complexCodePk = $sensors[$j]['complex_code_pk'];
                $sensorNo = $sensors[$j]['sensor_sn'];

                // 시간 데이터 추가
                $result = $this->setTimeData($complexCodePk, $option, $beforeOneHour, $hour, $sensorNo);
            }
        }

        $this->close();

        $this->data = $data;
        return true;
    }

    /**
     * 시간 데이터 추가
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $dateHour
     * @param string $hour
     * @param string $sensorNo
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function setTimeData(string $complexCodePk, int $option, string $dateHour, string $hour, string $sensorNo) : bool
    {
        $fcColumnInfo = [];

        $valColumn = "val_{$hour}";
        $fcDate = substr($dateHour, 0, 8);

        // 금일 사용량 조회
        // 일통계와 시간통계 `bems_stat_daily_`.`val` 컬럼 update 문제 확인으로 인한 일시적으로 막음
        /*
            $dailySumData = $this->getDailyUsageSum($complexCodePk, $option, $fcDate, $sensorNo);
            if (count($dailySumData) === 0) {
                return false;
            }

            $fcColumnInfo['val'] = $dailySumData['val'];
        */

        $rCurrentTimeQ = $this->emsQuery->getQuerySelectCurrentTime($complexCodePk, $option, $dateHour, $sensorNo);
        $currentTimes = $this->query($rCurrentTimeQ);

        if (count($currentTimes) === 0) {
            return false;
        }

        $fcSensorNo = $currentTimes[0]['sensor_sn'];

        $fcColumnInfo[$valColumn] = $currentTimes[0]['val'];
        //$fcColumnInfo['total_val'] = $currentTimes[0]['total_val'];

        $query = $this->emsQuery->getQueryInsertMeterTime($option, $fcDate, $fcSensorNo, $fcColumnInfo);;
        $this->squery($query);

        return true;
    }

    /**
     * 금일 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param string $sensorNo
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
    private function getDailyUsageSum(string $complexCodePk, int $option, string $date, string $sensorNo) : array
    {
        $fcData = [
            'val' => 0,
            'total_val' => 0,
        ];

        // 금일 조회
        $rDailySumQ = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $date, $date, $sensorNo);
        $dailySums = $this->query($rDailySumQ);

        // 전일 조회
        $previousDate = date('Ymd', strtotime($date . '-1 day'));
        $rPreviousSumQ = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $previousDate, $previousDate, $sensorNo);
        $previousSums = $this->query($rPreviousSumQ);

        $fcTodayVal = $dailySums[0]['val_ed'] - $previousSums[0]['val_ed'];
        $fcTotalVal = $dailySums[0]['val_ed'];

        if ($previousSums[0]['val'] == 0 || $previousSums[0]['val'] == '') {
            // 전일 데이터가 존재하지 않다면..
            $fcTodayVal = $dailySums[0]['val'];
        }

        if ((int)$dailySums[0]['val_st'] === 0) {
            // 계측기 초기화 되었다면..
            $rReplaceQ = $this->emsQuery->getQuerySelectSensorReplaceDate($complexCodePk, $option, $sensorNo, $date, $date);
            $rReplaceData = $this->query($rReplaceQ);

            $replaceDate = $rReplaceData[0]['replace_date'];
            if (empty($replaceDate) === true || is_null($replaceDate) === true) {
                return [];
            }

            $replaceDate = date('YmdHis', strtotime($replaceDate . '+1 hours'));

            /*
             * 계측기 초기화 된 경우 날자 조회 후 다음날부터 검색
             * - 변경된 날의 경우 기존이랑 섞여서 데이터 못 찾음
             */
            $query = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $replaceDate, $date, $sensorNo);
            $t = $this->query($query);

            $fcTodayVal = $t[0]['val'];
            $fcTotalVal = $t[0]['val_ed'];
        }

        $fcTodayVal = ($fcTodayVal < 0) ? 0 : $fcTodayVal;

        $fcData['val'] = $fcTodayVal;
        $fcData['total_val'] = $fcTotalVal; // 한시간 전 누적값

        return $fcData;
    }
}