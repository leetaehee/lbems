<?php
namespace Http\Command;

/**
 * Class AddElechotMdmt  무등산 전열 구하기
 */
class AddElechotMdmt extends Command
{
    /** @var string|2001 $complexCodePk 단지번호 */
    private string $complexCodePk = '2001';

    /** @var int|5 $option 에너지원, 전열을 의미  */
    private int $option = 5;

    /**
     * AddElechotMdmt Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElechotMdmt Destructor.
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

        // 전열 관련 센서 불러오기
        $elechotData = $this->getSensorManager($complexCodePk)->getElectricElechotSensor();

        $this->insertElectricElechotMeterData($complexCodePk, '1층 전기실', $option, $elechotData['1층 전기실']);
        $this->insertElectricElechotMeterData($complexCodePk, '1층 EPS실', $option, $elechotData['1층 EPS실']);
        $this->insertElectricElechotMeterData($complexCodePk, '2층 EPS실', $option, $elechotData['2층 EPS실']);
        $this->insertElectricElechotMeterData($complexCodePk, '3층 EPS실', $option, $elechotData['3층 EPS실']);
        $this->insertElectricElechotMeterData($complexCodePk, '3층 식당', $option, $elechotData['3층 식당']);

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
     * 전열 계산
     *
     * @param string $complexCodePk
     * @param string $type
     * @param int $option
     * @param array $data
     *
     * @throws \Exception
     */
    private function insertElectricElechotMeterData(string $complexCodePk, string $type, int $option, array $data) : void
    {
        $fcData = $this->getLastUsed($complexCodePk, $data);

        $sensorNo = '';
        $val = 0;
        $valDate = '';
        $dong = '';
        $ho = '';

        switch ($type)
        {
            case '1층 전기실':
                $sensorNo = '985DAD60E5ED_0';
                $val = $fcData['1층 전체 전력1'] - ($fcData['1층 전체 전력2'] + $fcData['전등1'] + $fcData['전등2'] + $fcData['전등3'] + $fcData['전등4']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '101';
                break;
            case '1층 EPS실':
                $sensorNo = '985DAD60D991_0';
                $val = $fcData['1층 전체 전력2'] - ($fcData['전등1'] + $fcData['전등2'] + $fcData['전등3'] + $fcData['전등4'] + $fcData['전등5'] + $fcData['전등6'] + $fcData['전등7']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '102';
                break;
            case '2층 EPS실':
                $sensorNo = '985DAD60D1B1_0';
                $val = $fcData['2층 전체 전력1'] - ($fcData['전등1'] + $fcData['전등2'] + $fcData['전등3'] + $fcData['전등4'] + $fcData['전등5'] + $fcData['전등6']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '201';
                break;
            case '3층 EPS실':
                $sensorNo = '985DAD60BBB0_0';
                $zeroValue = 5830700;
                $val = $fcData['3층 전체 전력1'] - ($fcData['3층 전체 전력2'] + $fcData['환기1'] + $fcData['전등1'] + $fcData['전등2'] + $fcData['전등3'] + $fcData['전등4']);
                if ($val < 1) {
                    // 3층 EPS실의 경우 마이너스가 나오면 0으로 맞춰서 정수 맞추기..
                    $val += $zeroValue;
                }
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '301';
                break;
            case '3층 식당':
                $sensorNo = '985DAD60C116_0';
                $val = $fcData['3층 전체 전력2'] - ($fcData['환기1'] + $fcData['환기2'] + $fcData['난방1'] + $fcData['전등1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '302';
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