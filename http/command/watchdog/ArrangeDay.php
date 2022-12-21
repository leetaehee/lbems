<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class ArrangeDay 일통계 생성
 */
class ArrangeDay extends Command
{
    /**
     * Arrange Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Arrange Destructor.
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
        //$today = date('Ymd', strtotime('20220601'));

        $yesterday = Utility::getInstance()->addDay($today, -1);
        $tableCount = count(Config::SENSOR_TYPES);

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $i;

            // 에너지원 모든 센서 조회
            $rAllSensorQ = $this->emsQuery->getQueryAllSensorData($option);
            $sensors = $this->query($rAllSensorQ);

            for ($j = 0; $j < count($sensors); $j++) {
                $sensorNo = $sensors[$j]['sensor_sn'];

                // 센서별로 시간데이터 추출
                $query = $this->emsQuery->getQueryMeterDayData($option, $yesterday, $sensorNo);
                $d = $this->query($query);

                // 일통계 생성
                $this->makeDailyData($yesterday, $option, $sensorNo, $d);
            }
        }

        $this->close();

        $this->data = [];
        return true;
    }

    /**
     * 일 통계 추가
     *
     * @param string $yesterday
     * @param int $option
     * @param string $sensorNo
     * @param array $d
     *
     * @throws \Exception
     */
    private function makeDailyData(string $yesterday, int $option, string $sensorNo, array $d) : void
    {
        // 결과
        $result = [
            'sensor_sn' => $sensorNo,
            'val' => 0,
            'total_val' => 0,
        ];

        if (count($d) === 0) {
            return;
        }

        // 시간 컬럼 추출
        for ($fcIndex = 0; $fcIndex < count($d); $fcIndex++) {
            $suffix = (int)$d[$fcIndex]['hour'];
            $hour = 'val_' . $suffix;

            $result[$hour] = empty($d[$fcIndex]['val']) === true ? 0 : $d[$fcIndex]['val'];
        }

        // 일 사용량 구하기
        $complexCodePk = $d[0]['complex_code_pk'];
        $coupleOfDay = date('Ymd',strtotime($yesterday .'-1 day')); // 새벽에 돌기 때문에 전전일로 구함

        // 금일 누적량 조회
        $rDailySumQ = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $yesterday, $yesterday, $sensorNo);
        $dailySums = $this->query($rDailySumQ);

        // 전일 누적량 조회
        $rPreviousSumQ = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $coupleOfDay, $coupleOfDay, $sensorNo);
        $previousSums = $this->query($rPreviousSumQ);

        $dailyStartUsed = $dailySums[0]['val_st'];
        $dailyEndUsed = $dailySums[0]['val_ed'];
        $previousEndUsed = $previousSums[0]['val_ed'];

        $result['val'] = $dailyEndUsed - $previousEndUsed;
        $result['total_val'] = $dailyEndUsed;

        if ($previousSums[0]['val'] == 0 || $previousSums[0]['val'] == '') {
            $result['val'] = $dailySums[0]['val'];
        }

        if ((int)$dailyStartUsed === 0) {
            // 계측기 초기화 된 경우
            $rReplaceQ = $this->emsQuery->getQuerySelectSensorReplaceDate($complexCodePk, $option, $sensorNo, $yesterday, $yesterday);
            $rReplaceData = $this->query($rReplaceQ);

            $replaceDate = $rReplaceData[0]['replace_date'];
            if (empty($replaceDate) === true || is_null($replaceDate) === true) {
                return;
            }

            $replaceDate = date('YmdHis', strtotime($replaceDate . '+1 hours'));

            /*
             * 계측기 초기화 된 경우 날자 조회 후 다음날부터 검색
             * - 변경된 날의 경우 기존이랑 섞여서 데이터 못 찾음
             */
            $query = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $replaceDate, $yesterday, $sensorNo);
            $t = $this->query($query);

            if (count($t) === 0) {
                return;
            }

            $result['val'] = $t[0]['val'];
            $result['total_val'] = $t[0]['val_ed'];
        }

        $result['val'] = ($result['val'] < 0) ? 0 : $result['val'];

        // 누직단계 적용
        // 현재는 사용하지 않아 데이터 넣지 않음 (2022-02-07, 필요시  성능 체크해서 적용 시킬 것
        /*
            $levelData = $this->updateElectricProgressiveLevel($complexCodePk, $option, $sensorNo);
            if (isset($levelData['g_level']) === true) {
                $result['g_level'] = (int)$levelData['g_level'];
            }
        */

        // 추가
        $cDailyDataQ = $this->emsQuery->getQueryUpdateOrInsertMeterDay($option, $yesterday, $result);
        $this->squery($cDailyDataQ);
    }

    /**
     * 전기 누진 단계 적용 - 2022-02-07,  필요시  성능 체크해서 적용 시킬 것
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sensorNo
     *
     * @return int[]
     *
     * @throws \Exception
     */
    private function updateElectricProgressiveLevel(string $complexCodePk, int $option, string $sensorNo) : array
    {
        $fcData = [
            'g_level' => 0,
        ];

        if ($option != 0) {
            return $fcData; // 전기만 적용
        }

        $rClosingDayQ = $this->emsQuery->getDueday($option, $complexCodePk);
        $closingDayData = $this->query($rClosingDayQ);

        $closingDay = $closingDayData[0]['closing_day'];
        $closeDayCmp = date('d');

        if ($closeDayCmp <= $closingDay) {
            $beforeMonth_date = date('Ym',strtotime('-1 month'));
            $closingDayDate = $beforeMonth_date.$closingDay;
        } else {
            $thisMonth_date = date('Ym');
            $closingDayDate = $thisMonth_date.$closingDay;
        }

        // 마감일 다음날 부터
        $closingDayDate = date('Ymd', strtotime($closingDayDate . '+1 day')) .'000000';
        $nowDate = date('Ymd') . '235959';

        $selQuery = $this->emsQuery->getQueryElectricUsedForMonth($nowDate, $closingDayDate, $sensorNo);
        $unitCost = $this->query($selQuery);
        $uniCostKw = $unitCost[0]['val'] / 1000;

        $selQuery = $this->emsQuery->getQueryElecCost($complexCodePk, $nowDate);
        $unitCost = $this->query($selQuery);

        $len2 = count($unitCost);
        for ($i = 0; $i < $len2; $i++) {
            $unitUsage = $unitCost[$i]['USED'];

            if ($unitUsage > $uniCostKw) {
                $fcData['g_level'] = $i+1;
                break;
            }
        }

        return $fcData;
    }
}