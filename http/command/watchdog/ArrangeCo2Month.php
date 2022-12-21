<?php
namespace Http\Command;

/**
 * Class ArrangeCo2Month Co2 월 데이터 생성
 */
class ArrangeCo2Month extends Command
{
    /**
     * ArrangeCo2Day constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeCo2Day destructor.
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
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $today = date('Ymd');

        // 말일 구하기 위한 전달 변경
        $preDate = date('Ymd', strtotime('-1 month', strtotime($today)));

        $preEndDay = date('t', strtotime($preDate));
        if ($preEndDay < 10) {
            $preEndDay = '0' . $preEndDay;
        }

        // date('Ymd') -> date('Ym') 형식 변경
        $preMonth = date('Ym', strtotime($preDate));
        $startDate = $preMonth . '01';
        $endDate = $preMonth . $preEndDay;

        if ($this->getCheckStatisticsDataSaved($preMonth) === true) {
            // 통계 데이터가 등록 되었는지 확인
            $this->data = $data;
            return true;
        }

        // 통계데이터 추가
        $this->setStatisticsData($preMonth, $preEndDay, $startDate, $endDate);

        $this->data = [];
        return true;
    }

    /**
     * 통계 데이터가 등록되었는지 확인
     *
     * @param string $date
     *
     * @return bool $isSaved
     *
     * @throws \Exception
     */
    private function getCheckStatisticsDataSaved(string $date) : bool
    {
        $isSaved = false;

        $rStatisticsQ = $this->emsQuery->getCheckStatisticsDataSaved(0, $date);
        $statisticsData = $this->query($rStatisticsQ);

        if ((int)$statisticsData[0]['sensor_count'] > 0) {
            // 데이터 없는 경우에만 추가할 수 있도록 함.
            $isSaved = true;
        }

        return $isSaved;
    }

    /**
     * 월 통계 추가
     *
     * @param string $ym
     * @param string $endDay
     * @param string $startDate
     * @param string $endDate
     *
     * @throws \Exception
     */
    private function setStatisticsData(string $ym, string $endDay, string $startDate, string $endDate) : void
    {
        // 건물정보 조회
        $rComplexQ = $this->emsQuery->getQuerySelectComplex();
        $complexData = $this->query($rComplexQ);

        // bems_meter_finedust 테이블에서 날짜는 2020-01-01 18:11:11 이므로 변경이 되어야함.
        $tempYm = $ym;
        $ym = date('Y-m', strtotime($ym . '01'));

        for ($i = 0; $i < count($complexData); $i++) {
            $complexCodePk = $complexData[$i]['complex_code_pk'];
            if ($complexCodePk === '2001' || $complexCodePk === '9999') {
                // 무등산은 co2 안받음
                // 9999는 케빈랩 테스트 계정
                continue;
            }

            // 건물별 센서 번호 조회
            $rSensorQ = $this->emsQuery->getQuerySensorData($complexCodePk, false);
            $sensorData = $this->query($rSensorQ);

            for ($j = 0; $j < count($sensorData); $j++) {
                $sensor = $sensorData[$j]['sensor'];

                // 미터테이블에서 금일 총 사용량 추출
                $rMeterTotalQ = $this->emsQuery->getQueryFinedustMeterTotalData($complexCodePk, $ym, $sensor);
                $totalData = $this->query($rMeterTotalQ);

                $minVal = (int)$totalData[0]['min_val'];
                $maxVal = (int)$totalData[0]['max_val'];
                $total = (int)$totalData[0]['val'];

                // 월 데이터 추가
                $cFinedustMonthQ = $this->emsQuery->getQueryInsertFinedustMonth($sensor, $tempYm, $endDay, $startDate, $endDate, $minVal, $maxVal, $total);
                $this->squery($cFinedustMonthQ);
            }
        }
    }
}