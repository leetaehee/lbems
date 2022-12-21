<?php
namespace Http\Command;

use EMS_Module\Config;

/**
 * Class AddElectricMdmt
 */
class AddElectricMdmt extends Command
{
    /**
     * AddElectricMdmt Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricMdmt Destructor.
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
        $tableCount = count(Config::SENSOR_TYPES);

        $today = date('YmdHis');
        $startHour = date('YmdHis', strtotime($today . '-5 minutes'));

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $i;

            if ($option === 1 || $option === 2 || $option === 5 || $option === 9) {
                // 가스, 수도, 보일러, 전열 제외
                // 태양광 소비량만 받도록 하기 (bems_sensor_solar.in_out = 'O')
                // 태양광 생산+소비 인경우 bems_sensor_solar.in_out='B'
                continue;
            }

            // 센서정보 조회
            $rSensorQ = $this->emsQuery->getQuerySensorNo($option, 'reti');
            $sensors = $this->query($rSensorQ);

            // bems_meter_에 데이터 추가
            $this->addMeterData($option, $startHour, $today, $sensors);
        }

        $this->data = [];
        return true;
    }

    /**
     * 미터 데이터 추가
     *
     * @param int $option
     * @param string $startDateHour
     * @param string $endDateHour
     * @param array $sensors
     *
     * @throws \Exception
     */
    private function addMeterData(int $option, string $startDateHour, string $endDateHour, array $sensors) : void
    {
        $fcCount = count($sensors);

        if ($fcCount === 0) {
            return;
        }

        for ($j = 0; $j < $fcCount; $j++) {
            $sensorNo = $sensors[$j]['sensor_sn'];
            $inout = $sensors[$j]['inout'];

            $sensorSplits = explode('_', $sensorNo);

            if (count($sensorSplits) < 2) {
                continue;
            }

            $groupId = $sensorSplits[1];
            $serial = $sensorSplits[0];

            $rRetiDataQ = $this->emsQuery->getQuerySelectRetiMeterData($groupId, $serial, $startDateHour, $endDateHour);
            $retiData = $this->query($rRetiDataQ);

            $retiCount = count($retiData);
            if ($retiCount < 1) {
                continue;
            }

            $val = $retiData[0]['val'];
            $valDate = $retiData[0]['val_date'];

            // 미터데이터 추가
            $cMeterQ = $this->emsQuery->getQueryInsertMeterTableByRetiEquipment($option, $sensorNo, $valDate, $val);
            $this->squery($cMeterQ);

            // 센서정보 업데이트
            $uSensorQ = $this->emsQuery->getQueryUpdateSensorTableByRetiEquipment($option, $sensorNo, $valDate, $val);
            $this->squery($uSensorQ);
        }
    }
}
