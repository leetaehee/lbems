<?php
namespace Http\Command;

/**
 * Class AddEleventMdmt 무등산 환기 구하기
 */
class AddEleventMdmt extends Command
{
    /** @var string|2001 $complexCodePk 단지번호 */
    private string $complexCodePk = '2001';

    /** @var int|10 $option 에너지원, 환기를 의미  */
    private int $option = 10;

    /**
     * AddEleventMdmt constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddEleventMdmt destructor.
     */
    public function __destruct()
    {
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
        $complexCodePk = $this->complexCodePk;
        $option = $this->option;

        // 환기 관련 센서 불러오기
        $eleventData = $this->getSensorManager($complexCodePk)->getElectricVentSensor();

        $this->insertElectricVentMeterData($complexCodePk, '옥탑층', $option, $eleventData['옥탑층']);

        $this->data = [];
        return true;
    }

    /**
     * 센서에서 사용량 조회
     *
     * @param string $complexCodePk
     * @param array $data
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
    private function getLastUsed(string $complexCodePk, array $data) : array
    {
        $fcData = [];

        foreach ($data AS $k => $sensors) {
            $option = $k;

            foreach ($sensors AS $k => $sensorNo) {
                $rSensorQ = $this->emsQuery->getQueryCurrentMeterUsedBySensor($complexCodePk, $option, $sensorNo);
                $sensorData = $this->query($rSensorQ);

                $fcData[$k] = $sensorData[0]['val'];
            }
        }

        return $fcData;
    }

    /**
     * 환기 계산
     *
     * @param string $complexCodePk
     * @param string $type
     * @param int $option
     * @param array $data
     *
     * @throws \Exception
     */
    private function insertElectricVentMeterData(string $complexCodePk, string $type, int $option, array $data) : void
    {
        $fcData = $this->getLastUsed($complexCodePk, $data);

        $sensorNo = '';
        $val = 0;
        $valDate = '';
        $dong = '';
        $ho = '';

        switch ($type)
        {
            case '옥탑층':
                $sensorNo = '985DAD60CBEC_0';
                $val = $fcData['전기기기'] - ($fcData['냉난방1'] + $fcData['냉난방2'] + $fcData['냉난방3'] + $fcData['냉난방4']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '401';
                break;
        }

        if ($val < 1) {
            $val = 0;
        }

        // 전열 데이터 추가
        $cElechotQ = $this->emsQuery->getQueryInsertMeterTable($option, $sensorNo, $valDate, $val);
        $this->squery($cElechotQ);

        // 센서 정보 업데이트
        $uElechotQ = $this->emsQuery->getQueryUpdateSensorTable($option, $sensorNo, $complexCodePk, $dong, $ho, $valDate, $val);
        $this->squery($uElechotQ);
    }
}