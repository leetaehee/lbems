<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ArrangeEfficiencyTime 금일 효율 시간데이터 생성
 */
class ArrangeEfficiencyTime extends Command
{
    /**
     * ArrangeEfficiencyTime constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeEfficiencyTime destructor.
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
        $data = [];

        $efficiencyTables = Config::EFFICIENCY_TABLES;

        $currentTime = date('YmdHis');
        //$currentTime = date('YmdHis', strtotime('20220419041000'));
        $beforeOneHourDate = date('YmdHis',  strtotime($currentTime . '-1 hours'));  // 한시간전
        $beforeOneHour = date('YmdH',  strtotime($beforeOneHourDate));

        $hour = (int)date('G', strtotime($beforeOneHourDate));

        for ($i = 0; $i < count($efficiencyTables); $i++) {
            $option = $i;

            if (empty($efficiencyTables[$option]) === true) {
                continue; // 효율정보가 있는 것만 조회..
            }

            $this->setTimeData($option, $beforeOneHour, $hour);
        }

        $this->close();

        $this->data = $data;
        return true;
    }

    /**
     * 시간 데이터 추가
     *
     * @param int $option
     * @param string $dateHour
     * @param string $hour
     *
     * @return void
     *
     * @throws \Exception
     */
    private function setTimeData(int $option, string $dateHour, string $hour) : void
    {
        $fcColumnInfo = [];

        // 오늘날짜에 해당하는 데이터가 bems_stat_daily_ 테이블에 존재하는지 확인 후 true,false 저장
        // 센서별 전시간 데이터를 insert or update 처리
        $fcToday = date('Ymd');

        $rExistCountQ = $this->emsQuery->getQuerySelectEfficiencyCountDailyData($option, $fcToday);
        $existData = $this->query($rExistCountQ);

        $isExist = (int)$existData[0]['count'] === 0 ? false : true;
        $valColumn = "efficiency_{$hour}";

        $rCurrentTimeQ = $this->emsQuery->getQuerySelectEfficiencyCurrentTime($option, $dateHour);
        $currentTimes = $this->query($rCurrentTimeQ);

        for ($fcIndex = 0; $fcIndex < count($currentTimes); $fcIndex++) {
            $fcDate = $currentTimes[$fcIndex]['date'];
            $fcSensorNo = $currentTimes[$fcIndex]['sensor_sn'];
            $fcComplexCodePk = $currentTimes[$fcIndex]['complex_code_pk'];

            // 센서 검색 조건
            $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $fcSensorNo);

            // 금일 사용량에 대해서 조회
            $rDailySumQ = $this->emsQuery->getQueryEfficiencyAvgSumData($fcComplexCodePk, $option, $fcDate, $sensorQuery);
            $dailySums = $this->query($rDailySumQ);
            $todayVal = $dailySums[0]['val'];

            $fcColumnInfo['efficiency'] = $todayVal;
            $fcColumnInfo[$valColumn] = $currentTimes[$fcIndex]['val'];

            if ($isExist === false) {
                // 일 통계 데이터가 생성되지 않은 경우 insert 를 실행...
                $query = $this->emsQuery->getQueryInsertEfficiencyTime($option, $fcDate, $fcSensorNo, $fcColumnInfo);
            } else {
                // 그렇지 않으면 update 실행...
                $query = $this->emsQuery->getQueryUpdateEfficiencyTime($option, $fcDate, $fcSensorNo, $fcColumnInfo);
            }

            $this->squery($query);
        }
    }
}