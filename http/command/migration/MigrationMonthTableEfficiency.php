<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;

/**
 * Class MigrationMonthTableEfficiency 월 통계 효율 마이그레이션
 */
class MigrationMonthTableEfficiency extends Command
{
    /**
     * MigrationMonthTableEfficiency constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MigrationMonthTableEfficiency destructor.
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

            if (empty($option) === true) {
                continue;
            }

            if (in_array($option, [4, 7]) === false) {
                continue;
            }

            // 월통계 데이터 모두 추출
            $rMonthTableAllQ = $migrationQuery->getQueryMonthTableAllData($option);
            $rMonthData = $this->query($rMonthTableAllQ);

            if (count($rMonthData) < 1) {
                continue;
            }

            for ($j = 0; $j < count($rMonthData); $j++) {
                $sensorNo = $rMonthData[$j]['sensor_sn'];
                $startDate = $rMonthData[$j]['st_date'];
                $endDate = $rMonthData[$j]['ed_date'];
                $ym = $rMonthData[$j]['ym'];

                $rEfficiencyQ = $migrationQuery->getQuerySelectEfficiencyMonthData($option,  $sensorNo, $startDate, $endDate);
                $rEfficiencyData = $this->query($rEfficiencyQ);

                $efficiencyStVal = $rEfficiencyData[0]['efficiency_st'];
                $efficiencyEdVal = $rEfficiencyData[0]['efficiency_ed'];
                $efficiencyVal = $rEfficiencyData[0]['efficiency'];

                $uMigrationQ = $migrationQuery->getQueryUpdateEfficiencyMonth($option, $ym, $sensorNo, $efficiencyStVal, $efficiencyEdVal, $efficiencyVal);
                //$this->squery($uMigrationQ);
            }
        }

        $this->data = [];
        return true;
    }
}