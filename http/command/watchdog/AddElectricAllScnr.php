<?php
namespace Http\Command;

/**
 * Class AddElectricAllScnr 전기 전체 전력 조회
 */
class AddElectricAllScnr extends Command
{
    /** @var string|2003 $complexCodePk 단지번호 */
    private string $complexCodePk = '2003';

    /** @var int|0 $option 에너지원, 전기를 의미 */
    private int $option = 0;

    /**
     * AddElectricAllNedOb Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricAllNedOb Destructor.
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
    public function execute(array $params): ?bool
    {
        $complexCodePk = $this->complexCodePk;
        $option = $this->option;

        // 전기 관련 센서 불러오기
        $electricData = $this->getSensorManager($complexCodePk)->getElectricSensor();

        $this->insertElectricElectricMeterData($complexCodePk, '전체 전력', $option, $electricData['전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '한전 전체 전력', $option, $electricData['한전 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '1층 전체 전력', $option, $electricData['1층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '2층 전체 전력', $option, $electricData['2층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '3층 전체 전력', $option, $electricData['3층 전체 전력']);

        $this->data = [];
        return true;
    }

    /**
     * 센서에서 사용량 조회 (최근)
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
     * 전기 계산
     *
     * @param string $complexCodePk
     * @param string $type
     * @param int $option
     * @param array $data
     *
     * @throws \Exception
     */
    private function insertElectricElectricMeterData(string $complexCodePk, string $type, int $option, array $data)
    {
        $fcData = $this->getLastUsed($complexCodePk, $data);

        $sensorNo = '';
        $val = 0;
        $valDate = '';
        $dong = '';
        $ho = '';

        switch ($type) {
            case '전체 전력':
                $sensorNo = '2003_1_2_3_F';
                $val = $fcData['1층 외부 기사대기실 옆'] + $fcData['신재생 소비량'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '000';
                break;
            case '한전 전체 전력':
                $sensorNo = '2003_ALL';
                $val = $fcData['1층 외부 기사대기실 옆'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '190';
                break;
            case '1층 전체 전력':
                $sensorNo = '2003_1F';
                $val = $fcData['1세반 앞 복도'] + $fcData['1층 외부 기사대기실 옆'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '191';
                break;
            case '2층 전체 전력':
                $sensorNo = '2003_2F';
                $val = $fcData['2층 엘레베이터 홀'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '192';
                break;
            case '3층 전체 전력':
                $sensorNo = '2003_3F';
                $val = $fcData['3층 계단실'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '193';
                break;
        }

        if ($val < 1) {
            $val = 0;
        }

        // bems_meter_ 데이터 추가
        $cElectricQ = $this->emsQuery->getQueryInsertMeterTable($option, $sensorNo, $valDate, $val);
        $this->squery($cElectricQ);

        // bems_sensor_ 업데이트
        $uElectricQ = $this->emsQuery->getQueryUpdateSensorTable($option, $sensorNo, $complexCodePk, $dong, $ho, $valDate, $val);
        $this->squery($uElectricQ);
    }
}