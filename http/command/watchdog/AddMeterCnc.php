<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class AddMeterCnc cnc meter 생성하기
 */
class AddMeterCnc extends Command
{
    /** @var array|int[] $cncReferenceTables CNC_REFERENCE_TABLES 참조 테이블 */
    private array $cncReferenceTables = Config::CNC_REFERENCE_TABLES;

    /**
     * AddMeterCnc Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddMeterCnc Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool|mixed
     *
     * @throws \Exception
     */
    public function execute(array $params): ?bool
    {
        $cncRefTables = $this->cncReferenceTables;

        for ($i = 0; $i < count($cncRefTables); $i++) {
            $option = $cncRefTables[$i];

            // 센서정보 조회
            $rSensorQ = $this->emsQuery->getQuerySensorNo($option, 'cnc');
            $sensors = $this->query($rSensorQ);

            // bems_meter_ 에 데이터 추가
            $this->addMeterData($option, $sensors);
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
     *
     * @throws \Exception
     */
    private function addMeterData(int $option, array $sensors) : void
    {
        $transGasToElectricValue = Config::GAS_TO_ELECTRIC_TRANS_VALUE;

        $fcCount = count($sensors);
        if (count($sensors) === 0) {
            return;
        }

        for ($j = 0; $j < $fcCount; $j++) {
            $sensorNo = $sensors[$j]['sensor_sn'];
            $complexCodePk = $sensors[$j]['complex_code_pk'];
            $preVal = (float)$sensors[$j]['pre_val'];

            $channelInfo = $this->getChannelInfo($complexCodePk,$sensorNo);
            $searchSensorNo = $channelInfo['sensor_sn'];
            $chanelNo = $channelInfo['channel_no'];

            // cnc 테이블에서 데이터 조회
            $rCncQ = $this->emsQuery->getQueryCncMeterData($searchSensorNo);
            $cncData = $this->query($rCncQ);

            $cncCount = count($cncData);
            if ($cncCount < 1) {
                continue;
            }

            for ($z = 0; $z < $cncCount; $z++) {
                $valDate = $cncData[$z]['val_date'];
                $ch1PulseVal = $cncData[$z]['ch1_pulse_val'];
                $ch2PulseVal = $cncData[$z]['ch2_pulse_val'];
                $ch1Unit = $cncData[$z]['ch1_unit'];
                $ch2Unit = $cncData[$z]['ch2_unit'];
                $errorCode = $cncData[$z]['error_code'];

                $val = ($ch1PulseVal * $ch1Unit) + $preVal;

                if ($chanelNo == 2) {
                    // 채널 2번을 사용하는 경우 2번값으로 계산 할 것
                    $val = ($ch2PulseVal * $ch2Unit) + $preVal;
                }

                if ($option === 4) {
                    // 냉난방에 GHP 가 포함 되는 경우 Wh로 환산
                    $val = $val * $transGasToElectricValue * 1000;
                }

                // 미터데이터 추가
                $cMeterQ = $this->emsQuery->getQueryInsertCncMeterTableByEquipment($option, $sensorNo, $valDate, $val, $errorCode);
                $this->squery($cMeterQ);

                // 센서정보 업데이트
                $uSensorQ = $this->emsQuery->getQueryUpdateCncSensorTableByEquipment($option, $sensorNo, $valDate, $val, $errorCode);
                $this->squery($uSensorQ);
            }
        }
    }

    /**
     * 채널 정보 조회
     *
     * @param string $complexCodePk
     * @param string $sensorNo
     *
     * @return array
     */
    private function getChannelInfo(string $complexCodePk, string $sensorNo) : array
    {
        $explodeData = Utility::getInstance()->getExplodeData($sensorNo, '_');
        $fcChannelInfo = [
            'channel_no' => '',
            'sensor_sn' => $explodeData[0]
        ];

        $cncTwoChannelInfo = Config::CNC_TWO_CHANNEL_COMPLEX_INFO;
        if (in_array($complexCodePk, $cncTwoChannelInfo) === false) {
            return $fcChannelInfo;
        }

        if (count($explodeData) > 1) {
            $fcChannelInfo['channel_no'] = $explodeData[1];
            return $fcChannelInfo;
        }

        return $fcChannelInfo;
    }
}