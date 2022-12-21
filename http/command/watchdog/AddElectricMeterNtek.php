<?php
namespace Http\Command;

use EMS_Module\Config;

/**
 * Class AddElectricMeterNtek ntek 데이터 조회 후 meter 테이블 추가
 */
class AddElectricMeterNtek extends Command
{
    /**
     * AddElectricMeterNtek Constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricMeterNtek Destructor.
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
        $tableCount = count(Config::SENSOR_TABLES);

        for ($i = 0; $i < $tableCount; $i++) {
            $option = $i;

            if ($option === 1 || $option === 2 || $option === 5 || $option === 9) {
                // 가스, 수도, 보일러, 전열 제외
                // 태양광 소비량만 받도록 하기 (bems_sensor_solar.in_out = 'O')
                // 태양광 생산+소비 인경우 bems_sensor_solar.in_out='B'
               continue;
            }

            // 센서정보 조회
            $rSensorQ = $this->emsQuery->getQuerySensorNo($option);
            $sensors = $this->query($rSensorQ);

            // bems_meter_에 데이터 추가
            $this->addMeterData($option, $sensors);
        }

        $this->db->close();

        $this->data = [];
        return true;
    }

    /**
     * 미터 데이터 추가
     *
     * @param int $option
     * @param array $sensors
     *
     * @throws \Exception
     */
    private function addMeterData(int $option, array $sensors) : void
    {
        $fcCount = count($sensors);

        $divisors = Config::DIVISOR_VALUES;
        $divisor = $divisors[$option];

        $efficiencyTables = Config::EFFICIENCY_TABLES;

        if ($fcCount === 0) {
            return;
        }

        for ($j = 0; $j < $fcCount; $j++) {
            $columns = [];

            $sensorNo = $sensors[$j]['sensor_sn'];
            $replaceSensorSn = $sensors[$j]['replace_sensor_sn'];

            // 신규센서 만들기
            $this->makeSensor($option, $sensors[$j]);

            // ntek 테이블에서 검색할 센서번호 찾기
            $searchSensor = empty($replaceSensorSn) === false ? $replaceSensorSn : $sensorNo;

            // ntek 테이블에서 데이터 조회
            $rNtekQ = $this->emsQuery->getQueryNtekMeterData($searchSensor);
            $ntekData = $this->query($rNtekQ);

            $ntekCount = count($ntekData);
            if ($ntekCount < 1) {
                continue;
            }

            $valDate = $ntekData[0]['val_date'];
            $current = $ntekData[0]['watt'];
            $val = $ntekData[0]['kwh_imp'] * $divisor;
            $errorCode = $ntekData[0]['error_code'];
            $allData = $ntekData[0]['all_data'];
            $pf = $ntekData[0]['pf'];

            $efficiencyTable = $efficiencyTables[$option];

            if (empty($efficiencyTable) === false) {
                // 역률 정보가 존재할 경우 역률 정보도 미터에 추가..
                $columns['pf'] = $pf;
            }

            // 미터데이터 추가
            $cMeterQ = $this->emsQuery->getQueryInsertMeterTableByEquipment($option, $sensorNo, $valDate, $val, $current, $errorCode, $columns);
            $this->squery($cMeterQ);

            // 센서정보 업데이트
            $uSensorQ = $this->emsQuery->getQueryUpdateSensorTableByEquipment($option, $sensorNo, $valDate, $val, $errorCode, $allData);
            $this->squery($uSensorQ);
        }
    }

    /**
     * 기존 센서번호 정보로 새로운 센서 만들기
     *
     * @param int $option
     * @param array $data
     *
     * @throws \Exception
     */
    private function makeSensor(int $option, array $data) : void
    {
        $fcColumns = [];

        $complexCodePk = $data['complex_code_pk'];
        $homeDongPk = $data['home_dong_pk'];
        $homeHoPk = $data['home_ho_pk'];
        $sensorNo = '';

        if ($option === 11 && $data['inout'] === 'B') {
            // 태양광 inout = 'B' 이면 발전량 센서를 추가한다.
            $sensorNo = $complexCodePk . "_1";

            $fcColumns  = [
                'memo' => '태양광',
                'inout' => 'I',
                'maker' => $data['maker'],
            ];
        }

        if (empty($sensorNo) === false && count($fcColumns) > 0) {
            // 센서 추가
            $cSensorQ = $this->emsQuery->getQueryInsertSensorData($complexCodePk, $option,  $homeDongPk, $homeHoPk, $sensorNo, $fcColumns);
            $this->squery($cSensorQ);
        }
    }
}