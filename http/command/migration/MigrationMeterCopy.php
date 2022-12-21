<?php
namespace Http\Command;

use EMS_Module\MigrationQuery;

/**
 * Class MigrationMeterCopy  meter 데이터를  다른데로 복사
 */
class MigrationMeterCopy extends Command
{
    /**
     * MigrationMeterCopy construct.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MigrationMeterCopy destruct.
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
        $migrationQuery = new MigrationQuery();

        $option = 0;

        $startDateTime = '20220324000000';
        $endDateTime = '20221101181000';

        $copySensor = '2012_1_3';
        $destinationSensor = '2012_ALL';

        $rRawDataQ = $migrationQuery->getQuerySelectRawData($option, $copySensor, $startDateTime, $endDateTime);
        $rawData = $this->query($rRawDataQ);

        // 마이그레이션
        $this->addMeterData($option, $destinationSensor, $rawData);

        $this->close();

        $this->data = [];
        return true;
    }

    /**
     * 미터 데이터 추가
     *
     * @param int $option
     * @param string $sensorNo
     * @param array $data
     *
     * @return void
     *
     * @throws \Exception
     */
    private function addMeterData(int $option, string $sensorNo, array $data) : void
    {
        $fcCount = count($data);

        $columns = [];

        if ($fcCount === 0) {
            return;
        }

        for ($i = 0; $i < $fcCount; $i++) {
            $valDate = $data[$i]['val_date'];
            $current= $data[$i]['current_w'];
            $errorCode = $data[$i]['error_code'];
            $val = $data[$i]['val'];

            // 미터데이터 추가
            $cMeterQ = $this->emsQuery->getQueryInsertMeterTableByEquipment($option, $sensorNo, $valDate, $val, $current, $errorCode, $columns);
            $this->squery($cMeterQ);
        }
    }
}