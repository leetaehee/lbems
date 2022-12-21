<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;

/**
 * Class MigrationMonthTableStatus 경부하, 중부하, 최대부하 월통계 마이그레이션
 */
class MigrationMonthTableStatus extends Command
{
    /**
     * MigrationMonthTableStatus Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MigrationMonthTableStatus Destructor
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

        // 마이그레이션 대상 에너지원 (bems, lbems 다름 확인할 것)
        $sensorTypes = Config::STATUS_ENERGY_TYPES;

        for ($i = 0; $i < count($sensorTypes); $i++) {
            $option = $sensorTypes[$i];

            $rMonthDataQ = $migrationQuery->getQueryMonthTableAllStatusData($option);
            $rMonthData = $this->query($rMonthDataQ);

            if (count($rMonthData) === 0) {
                continue;
            }

            // 부하별 월 통계 마이그레이션
            $this->makeStatusData($option, $rMonthData);
        }

        $this->data = [];
        return true;
    }

    /**
     * 부바별 월 통계데이터 추가
     *
     * @param int $option
     * @param array $data
     *
     * @throws \Exception
     */
    private function makeStatusData(int $option, array $data) : void
    {
        $dataCount = count($data);
        if ($dataCount === 0) {
            return;
        }

        for ($fcI = 0; $fcI < $dataCount; $fcI++) {
            $fcSensorNo = $data[$fcI]['sensor_sn'];
            $fcStartDate = $data[$fcI]['st_date'];
            $fcEndDate = $data[$fcI]['ed_date'];
            $fcYm = $data[$fcI]['ym'];

            $rDayStatusSumQ = $this->emsQuery->getQuerySelectDayStatusSumData($option, $fcSensorNo, $fcStartDate, $fcEndDate);
            $dayStatusSumData = $this->query($rDayStatusSumQ);

            if (count($dayStatusSumData) === 0) {
                continue;
            }

            $fcLowStatus = $dayStatusSumData[0]['low_status'];
            $fcMidStatus = $dayStatusSumData[0]['mid_status'];
            $fcMaxStatus = $dayStatusSumData[0]['max_status'];

            $uStatusQ = $this->emsQuery->getQueryUpdateMonthStatusType($option, $fcSensorNo, $fcYm, $fcLowStatus, $fcMidStatus, $fcMaxStatus);
            $this->squery($uStatusQ);
        }
    }
}