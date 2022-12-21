<?php
namespace Http\Command;

use EMS_Module\MigrationQuery;
use EMS_Module\Config;

/**
 * Class MigrationMeterNtek 엔텍 데이터 마이그레이션
 */
class MigrationMeterNtek extends Command
{
    /**
     * MigrationMeterNtek construct.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MigrationMeterNtek destruct.
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
    public function execute(array $params): ?bool
    {
        $startDate = '20211021';
        $endDate = '20211110';

        $option = 0;

        // 센서정보 조회
        $rSensorQ = $this->emsQuery->getQuerySensorNo($option);
        $sensors = $this->query($rSensorQ);

        // 마이그레이션 진행
        $this->addMeterData($option, $sensors, $startDate, $endDate);

        // db close..
        $this->close();

        $this->data = [];
        return true;
    }

    /**
     * 미터 데이터 추가
     *
     * @param int $option
     * @param array $sensors
     * @param string $startDate
     * @param string $endDate
     *
     * @throws \Exception
     */
    private function addMeterData(int $option, array $sensors, string $startDate, string $endDate) : void
    {
        $migrationQuery = new MigrationQuery();

        $fcCount = count($sensors);

        $divisors = Config::DIVISOR_VALUES;
        $divisor = $divisors[$option];

        $columns = [];

        if ($fcCount === 0) {
            return;
        }

        for ($j = 0; $j < $fcCount; $j++) {
            $sensorNo = $sensors[$j]['sensor_sn'];

            // ntek 테이블에서 데이터 조회- 역률 마이그레이션 시  해당 컬럼 추가해서 할 것... 현재는 제외 시킴
            $rNtekQ = $migrationQuery->getQueryNtekMeterByDateData($sensorNo, $startDate, $endDate);
            $ntekData = $this->query($rNtekQ);

            $ntekCount = count($ntekData);
            if ($ntekCount < 1) {
                continue;
            }

            for ($z = 0; $z < $ntekCount; $z++) {
                $sensorNo = $ntekData[$z]['sensor_sn'];
                $valDate = $ntekData[$z]['val_date'];
                $current = $ntekData[$z]['watt'];
                $val = $ntekData[$z]['kwh_imp'] * $divisor;
                $errorCode = $ntekData[$z]['error_code'];

                // 미터데이터 추가
                $cMeterQ = $this->emsQuery->getQueryInsertMeterTableByEquipment($option, $sensorNo, $valDate, $val, $current, $errorCode, $columns);
                $this->squery($cMeterQ);
            }
        }
    }
}