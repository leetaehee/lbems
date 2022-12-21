<?php
namespace Http\Command;

/**
 * Class ArrangeCo2Day Co2 일 데이터 생성
 */
class ArrangeCo2Day extends Command
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

        $today = date('Y-m-d');
        $preDate = date('Y-m-d', strtotime('-1 day', strtotime($today)));

        if ($this->getCheckStatisticsDataSaved($preDate) === true) {
            // 통계 데이터가 등록 되었는지 확인
            $this->data = $data;
            return true;
        }

        // 통계데이터 추가
        $this->setStatisticsData($preDate);

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
        $date = date('Ymd', strtotime($date));

        $rStatisticsQ = $this->emsQuery->getCheckStatisticsDataSaved(1, $date);
        $statisticsData = $this->query($rStatisticsQ);

        if ((int)$statisticsData[0]['sensor_count'] > 0) {
            // 데이터 없는 경우에만 추가할 수 있도록 함.
            $isSaved = true;
        }

        return $isSaved;
    }

    /**
     * 일통계 추가
     *
     * @param string $date
     *
     * @throws \Exception
     */
    private function setStatisticsData(string $date) : void
    {
        // 건물정보 조회
        $rComplexQ = $this->emsQuery->getQuerySelectComplex();
        $complexData = $this->query($rComplexQ);

        $tempDate = date('Ymd', strtotime($date));

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

                // 미터테이블에서 금일 데이터 조회
                $rMeterHourQ = $this->emsQuery->getQueryFinedustMeterHourData($complexCodePk, $date, $sensor);
                $co2Data = $this->query($rMeterHourQ);

                // 미터테이블에서 금일 총 사용량 추출
                $rMeterTotalQ = $this->emsQuery->getQueryFinedustMeterTotalData($complexCodePk, $date, $sensor);
                $totalData = $this->query($rMeterTotalQ);
                $total = (int)$totalData[0]['val'];

                // 일 데이터 추가
                $cFinedustDailyQ = $this->emsQuery->getQueryInsertFinedustDaily($sensor, $tempDate, $co2Data, $total);
                $this->squery($cFinedustDailyQ);
            }
        }
    }
}