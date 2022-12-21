<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Usage;

/**
 * Class ArrangeStatusTypeDay 경부하, 중부하, 최대부하 일통계
 */
class ArrangeStatusTypeDay extends Command
{
    /** @var Usage|null $usage */
    private ?Usage $usage = null;

    /**
     * ArrangeStatusTypeDay Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * ArrangeStatusTypeDay Destructor.
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

        $statusEnergyTypes = Config::STATUS_ENERGY_TYPES;
        $tableCount = count($statusEnergyTypes);

        $today = date('Ymd');
        //$today = date('Ymd', strtotime('20220505'));
        $yesterday = date('Ymd', strtotime($today . '-1 day'));
        $month = date('n', strtotime($yesterday));

        $seasonType = Config::MONTH_SEASON_TYPES[$month];

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $statusEnergyTypes[$i];

            // 생성된 일통계 조회
            $rDailyStatQ = $this->emsQuery->getQuerySelectDailySensorData($option, $yesterday);
            $dailyStatData = $this->query($rDailyStatQ);

            // 부하별 일 통계 생성
            $this->makeStatusData($option, $seasonType, $yesterday, $dailyStatData);
        }

        $this->data = $data;
        return true;
    }

    /**
     * 부하별 일 통계 생성
     *
     * @param int $option
     * @param string $seasonType
     * @param string $date
     * @param array $data
     *
     * @throws \Exception
     */
    private function makeStatusData(int $option, string $seasonType, string $date, array $data) : void
    {
        $usage = $this->usage;

        $dataCount = count($data);

        if ($dataCount === 0) {
            return;
        }

        $lowStatusType = $usage->getStatusType($this, $date, 'low_status');
        $midStatusType = $usage->getStatusType($this, $date, 'mid_status');
        $maxStatusType = $usage->getStatusType($this, $date, 'max_status');

        for ($fcI = 0; $fcI < $dataCount; $fcI++) {
            $fcSensorNo = $data[$fcI]['sensor_sn'];
            $fcComplexCodePk = $data[$fcI]['complex_code_pk'];

            // 초기화
            // low_status : 경부하, mid_status: 중부하, max_status: 최대부하
            $data[$fcI]['low_status'] = 0;
            $data[$fcI]['mid_status'] = 0;
            $data[$fcI]['max_status'] = 0;

            $data[$fcI][$lowStatusType] += $usage->calculateStatusUsed($this, $fcComplexCodePk, $seasonType, 'low', $option, $date, $fcSensorNo);
            $data[$fcI][$midStatusType] += $usage->calculateStatusUsed($this, $fcComplexCodePk, $seasonType, 'mid', $option, $date, $fcSensorNo);
            $data[$fcI][$maxStatusType] += $usage->calculateStatusUsed($this, $fcComplexCodePk, $seasonType, 'max', $option, $date, $fcSensorNo);

            $lLowStatus = $data[$fcI]['low_status'];
            $lMidStatus = $data[$fcI]['mid_status'];
            $lMaxStatus = $data[$fcI]['max_status'];

            $uStatusQ = $this->emsQuery->getQueryUpdateDailyStatusType($option, $fcSensorNo, $date,  $lLowStatus, $lMidStatus, $lMaxStatus);
            $this->squery($uStatusQ);
        }
    }
}