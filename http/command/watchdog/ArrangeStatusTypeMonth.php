<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ArrangeStatusTypeMonth 경부하, 중부하, 최대부하 월통계
 */
class ArrangeStatusTypeMonth extends Command
{
    /**
     * ArrangeStatusTypeMonth Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeStatusTypeMonth Destructor.
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

        $today = date('Ymd');
        //$today = date('Ymd',strtotime('20220501'));

        $yesterday = Utility::getInstance()->addDay($today, -1);

        $statusEnergyTypes = Config::STATUS_ENERGY_TYPES;
        $tableCount = count($statusEnergyTypes);

        $ym = date('Ym', strtotime($today));

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $statusEnergyTypes[$i];

            // 생성된 월통계 조회
            $rMonthStatQ = $this->emsQuery->getQuerySelectStatusMonthData($option, $yesterday);
            $monthStatData = $this->query($rMonthStatQ);

            if (count($monthStatData) === 0) {
                // 데이터 없으면 진행 안함
                continue;
            }

            // 부하별 월 통계 데이터 추가
            $this->makeStatusData($option, $monthStatData);
        }

        $this->data = $data;
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

            $fcLowStatus = $dayStatusSumData[0]['low_status'];
            $fcMidStatus = $dayStatusSumData[0]['mid_status'];
            $fcMaxStatus = $dayStatusSumData[0]['max_status'];

            $uStatusQ = $this->emsQuery->getQueryUpdateMonthStatusType($option, $fcSensorNo, $fcYm, $fcLowStatus, $fcMidStatus, $fcMaxStatus);
            $this->squery($uStatusQ);
        }
    }
}