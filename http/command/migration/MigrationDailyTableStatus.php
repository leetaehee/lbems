<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;
use EMS_Module\Usage;

/**
 * Class MigrationDailyTableStatus 경부하, 중부하, 최대부하 일통계 마이그레이션
 */
class MigrationDailyTableStatus extends Command
{
    /** @var Usage|null  $usage */
    private ?Usage $usage = null;

    /**
     * MigrationDailyTableStatus Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * MigrationDailyTableStatus Destructor
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
        $statusEnergyTypes = Config::STATUS_ENERGY_TYPES;

        $today = date('Ymd');

        for ($i = 0; $i < count($statusEnergyTypes); $i++) {
            $option = $statusEnergyTypes[$i];

            $rDailyDataQ = $migrationQuery->getQueryDailyTableAllStatusData($option, $today);
            $rDailyData = $this->query($rDailyDataQ);

            if (count($rDailyData) === 0) {
                continue;
            }

            // 부하별 일통계 마이그레이션
            $this->makeStatusData($option, $rDailyData);
        }

        $this->data = [];
        return true;
    }

    /**
     * 부하별 일 통계 생성
     *
     * @param int $option
     * @param array $data
     *
     * @throws \Exception
     */
    private function makeStatusData(int $option, array $data) : void
    {
        $usage = $this->usage;
        $seasonTypes = Config::MONTH_SEASON_TYPES;

        $dataCount = count($data);

        if ($dataCount === 0) {
            return;
        }

        for ($fcI = 0; $fcI < $dataCount; $fcI++) {
            // 초기화
            // low_status : 경부하, mid_status: 중부하, max_status: 최대부하
            $data[$fcI]['low_status'] = 0;
            $data[$fcI]['mid_status'] = 0;
            $data[$fcI]['max_status'] = 0;

            $lSensorNo = $data[$fcI]['sensor_sn'];
            $lValDate = $data[$fcI]['val_date'];
            $lComplexCodePk = $data[$fcI]['complex_code_pk'];

            $month = date('n', strtotime($lValDate));
            $lSeasonType = $seasonTypes[$month];

            $lowStatusType = $usage->getStatusType($this, $lValDate, 'low_status');
            $midStatusType = $usage->getStatusType($this, $lValDate, 'mid_status');
            $maxStatusType = $usage->getStatusType($this, $lValDate, 'max_status');

            $data[$fcI][$lowStatusType] += $usage->calculateStatusUsed($this, $lComplexCodePk, $lSeasonType, 'low', $option, $lValDate, $lSensorNo);
            $data[$fcI][$midStatusType] += $usage->calculateStatusUsed($this, $lComplexCodePk, $lSeasonType, 'mid', $option, $lValDate, $lSensorNo);
            $data[$fcI][$maxStatusType] += $usage->calculateStatusUsed($this, $lComplexCodePk,  $lSeasonType, 'max', $option, $lValDate, $lSensorNo);

            $lLowStatus = $data[$fcI]['low_status'];
            $lMidStatus = $data[$fcI]['mid_status'];
            $lMaxStatus = $data[$fcI]['max_status'];

            $uStatusQ = $this->emsQuery->getQueryUpdateDailyStatusType($option, $lSensorNo, $lValDate,  $lLowStatus, $lMidStatus, $lMaxStatus);
            $this->squery($uStatusQ);
        }
    }
}