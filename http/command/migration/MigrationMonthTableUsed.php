<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;
use EMS_Module\Utility;

/**
 * Class MigrationMonthTableUsed 월 통계  1개월 누적량  마이그레이션  (계산식 = 현재 누적량 - 전월 누적량)
 * 마이그레이션 시 오류 체크해볼것 (2021년 1월 이전 데이터의 경우 오류가 있음)
 */
class MigrationMonthTableUsed extends Command
{
    /**
     * MigrationMonthTableUsed constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MigrationMonthTableUsed destructor.
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
        $migrationQuery = new MigrationQuery();
        $sensorTables = Config::SENSOR_TABLES;

        for ($i = 0; $i < count($sensorTables); $i++) {
            $option = $i;

            // 월통계 데이터 모두 추출
            $rMonthTableAllQ = $migrationQuery->getQueryMonthTableAllData($option);
            $rMonthData = $this->query($rMonthTableAllQ);

            if (count($rMonthData) < 1) {
                continue;
            }

            for ($j = 0; $j < count($rMonthData); $j++) {
                $complexCodePk = $rMonthData[$j]['complex_code_pk'];
                $sensorSn = $rMonthData[$j]['sensor_sn'];
                $startDate = $rMonthData[$j]['st_date'];
                $endDate = $rMonthData[$j]['ed_date'];
                $ym = $rMonthData[$j]['ym'];
                $closingDay = $rMonthData[$i]['closing_day'];

                if ($complexCodePk === '2001' && $ym < '202012') {
                    // 무등산 정상적인 데이터 들어온 시점이 12월이므로 그 이전 데이터는 제외
                    continue;
                }

                if (is_null($closingDay) === true) {
                    continue;
                }

                $basmDate = date('Ymd', strtotime($ym . '01'));
                $previousDate = date('Ymd', strtotime($basmDate . '-1 month'));

                $temp = Utility::getInstance()->getDateFromDueday($closingDay, $previousDate);
                $previousStartDate = $temp['start'];
                $previousEndDate = $temp['end'];

                // 이번달에 대한 최대 누적량 조회
                $rCurrentMaxQ = $migrationQuery->getQueryUsedByDate($option, $sensorSn, $startDate, $endDate);
                $currentMaxData = $this->query($rCurrentMaxQ);
                $currentUsed = $currentMaxData[0]['max'];

                // 지난달에 대한 최대 누적량 조회
                $rPreviousMaxQ = $migrationQuery->getQueryUsedByDate($option, $sensorSn, $previousStartDate, $previousEndDate);
                $previousMaxData = $this->query($rPreviousMaxQ);
                $previousUsed = $previousMaxData[0]['max'];

                $dbSensorNo = $sensorSn;

                if (count($previousUsed) === 0) {
                    // 전월 데이터가 없는 경우 현재월에서 사용량을 구한다.
                    $dbStartVal = $currentMaxData[0]['min'];
                    $dbEndVal = $currentMaxData[0]['max'];
                    $dbVal = $currentMaxData[0]['val'];
                } else {
                    // 전월 데이터가 있다면 전월 최근 사용량 - 금월 현재사용량으로 구한다.
                    $dbStartVal = $previousMaxData[0]['max'];
                    $dbEndVal = $currentMaxData[0]['max'];
                    $dbVal = $currentUsed-$previousUsed;
                }

                // 월통계 테이블 마이그레이션
                $uMonthTableQ = $migrationQuery->getQueryUpdateMonthTable($option, $dbStartVal, $dbEndVal, $dbVal, $dbSensorNo, $ym);
                //$this->squery($uMonthTableQ);
            }
        }

        $this->data = [];
        return true;
    }
}