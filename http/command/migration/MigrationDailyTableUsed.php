<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;

/**
 * Class MigrationDailyTableUsed 일 통계   1일 사용량  마이그레이션  (계산식 = 현재 누적량 - 전일 누적량)
 * 진행 전에 반드시 테스트 해볼 것, 로직도 확인 꼭 해볼 것, 백업 필수
 */
class MigrationDailyTableUsed extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

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
        $migrationQuery = new MigrationQuery();

        $sensorTables = Config::SENSOR_TABLES;

        for ($i = 0; $i < count($sensorTables); $i++) {
            $option = $i;

            if (in_array($option, [4, 7]) === false) {
                continue;
            }

            // 일통계 데이터 모두 추출
            $rDailyTableAllQ = $migrationQuery->getQueryDailyTableAllData($option);
            $rDailyData = $this->query($rDailyTableAllQ);

            for ($j = 0; $j < count($rDailyData); $j++) {
                $complexCodePk = $rDailyData[$j]['complex_code_pk'];
                $sensorSn = $rDailyData[$j]['sensor_sn'];
                $valDate = $rDailyData[$j]['val_date'];
                $previousDate = date('Ymd', strtotime($valDate . '-1 days'));

                // 이전일에 대한 최대 누적량
                $rPreviousMaxQ = $migrationQuery->getQueryUsedByDate($option, $sensorSn, $previousDate, $previousDate);
                $previousMaxData = $this->query($rPreviousMaxQ);

                // 현재일자에 대한 최대 누적량
                $rCurrentMaxQ = $migrationQuery->getQueryUsedByDate($option, $sensorSn, $valDate, $valDate);
                $currentMaxData = $this->query($rCurrentMaxQ);

                $dailyStartUsed = $currentMaxData[0]['min'];
                $dailyEndUsed = $currentMaxData[0]['max'];
                $previousEndUsed = $previousMaxData[0]['max'];

                $fcVal = $dailyEndUsed - $previousEndUsed;
                $totalVal = $dailyEndUsed;

                if ($previousMaxData[0]['val'] == 0 || $previousMaxData[0]['val'] == '') {
                    $fcVal = $currentMaxData[0]['val'];
                }

                if ((int)$dailyStartUsed === 0) {
                    // 계측기 초기화 된 경우
                    $rReplaceQ = $this->emsQuery->getQuerySelectSensorReplaceDate($complexCodePk, $option, $sensorSn, $valDate, $valDate);
                    $rReplaceData = $this->query($rReplaceQ);

                    $replaceDate = $rReplaceData[0]['replace_date'];

                    if (empty($replaceDate) === true || is_null($replaceDate) === true) {
                        continue;
                    }

                    $replaceDate = date('YmdHis', strtotime($replaceDate . '+1 hours'));

                    //계측기 초기화 된 경우 날자 조회 후 다음날부터 검색
                    // - 변경된 날의 경우 기존이랑 섞여서 데이터 못 찾음
                    $query = $this->emsQuery->getQueryMeterDaySumData($complexCodePk, $option, $replaceDate, $valDate, $sensorSn);
                    $t = $this->query($query);

                    if (count($t) === 0) {
                        continue;
                    }

                    $fcVal = $t[0]['val'];
                    $totalVal = $t[0]['val_ed'];
                }

                // 일통계 테이블 마이그레이션
                $uDailyTableQ = $migrationQuery->getQueryUpdateDailyTable($option, $fcVal, $totalVal, $sensorSn, $valDate);
                //$this->squery($uDailyTableQ);
            }
        }

        $this->data = [];
        return true;
    }
}