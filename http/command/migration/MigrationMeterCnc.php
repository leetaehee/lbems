<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;
use EMS_Module\Utility;

/**
 * Class MigrationMeterCnc  cnc 테이블에서 raw data  마이그레이션
 */
class MigrationMeterCnc extends Command
{
    /** @var array|int[] $cncReferenceTables CNC_REFERENCE_TABLES 참조 테이블 */
    private array $cncReferenceTables = Config::CNC_REFERENCE_TABLES;

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
    public function execute(array $params): ?bool
    {
        $startDateTime = '20220405000000';
        $endDateTime = '20220920162559';

        $cncRefTables = $this->cncReferenceTables;

        for ($i = 0; $i < count($cncRefTables); $i++) {
            $option = $cncRefTables[$i];

            // 센서정보 조회
            $rSensorQ = $this->emsQuery->getQuerySensorNo($option, 'cnc');
            $sensors = $this->query($rSensorQ);

            // 마이그레이션 실행
            $this->addMeterData($option, $sensors, $startDateTime, $endDateTime);
        }

        $this->close();

        $this->data = [];
        return true;
    }

    /**
     * 미터 데이터 추가
     *
     * @param int $option
     * @param array $sensors
     * @param string $startDateTime
     * @param string $endDateTime
     *
     * @throws \Exception
     */
    private function addMeterData(int $option, array $sensors, string $startDateTime, string $endDateTime) : void
    {
        $transGasToElectricValue = Config::GAS_TO_ELECTRIC_TRANS_VALUE;

        $migrationQuery = new MigrationQuery();

        $fcCount = count($sensors);
        if (count($sensors) === 0) {
            return;
        }

        for ($j = 0; $j < $fcCount; $j++) {
            $sensorNo = $sensors[$j]['sensor_sn'];

            // cnc 테이블에서 데이터 조회
            $rCncQ = $migrationQuery->getQueryCncMeterByDateData($sensorNo, $startDateTime, $endDateTime);
            $cncData = $this->query($rCncQ);

            // bems_meter_ 로 데이터 조회
            //$rCncQ = $migrationQuery->getQuerySelectRawData($option, $sensorNo, $startDateTime, $endDateTime);
            //$cncData = $this->query($rCncQ);

            $cncCount = count($cncData);
            if ($cncCount < 1) {
                continue;
            }

            for ($z = 0; $z < $cncCount; $z++) {
                // bems_meter_ 로 마이그레이션
                /*
                $sensorNo = $cncData[$z]['sensor_sn'];
                $valDate = $cncData[$z]['val_date'];
                $val = $cncData[$z]['val'];
                $errorCode = 0;
                */

                // cnc 테이블에 있는 정보로 마이그레이션
                $sensorNo = $cncData[$z]['sensor_sn'];
                $valDate = $cncData[$z]['val_date'];
                $ch1PulseVal = $cncData[$z]['ch1_pulse_val'];
                $ch1Unit = $cncData[$z]['ch1_unit'];
                $errorCode = $cncData[$z]['error_code'];
                $val = $ch1PulseVal * $ch1Unit;

                // 냉난방 GHP 로 변환하고자 할 경우 조회 되는 테이블 번호로 변경 할 것
                if ($option === 1) {
                    // 냉난방에 GHP 가 포함 되는 경우 Wh로 환산
                    $val = $val * $transGasToElectricValue * 1000;
                }

                // 미터데이터 추가
                $cMeterQ = $this->emsQuery->getQueryInsertCncMeterTableByEquipment(4, $sensorNo, $valDate, $val, $errorCode);
                //$this->squery($cMeterQ);
            }

        }
    }
}