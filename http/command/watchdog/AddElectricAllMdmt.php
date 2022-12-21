<?php
namespace Http\Command;

/**
 * Class AddElectricAllMdmt  무등산 전기 층,룸 사용량 조회
 */
class AddElectricAllMdmt extends Command
{
    /** @var string|2001 $complexCodePk 단지번호 */
    private string $complexCodePk = '2001';

    /** @var int|0 $option 에너지원, 전기를 의미  */
    private int $option = 0;

    /**
     * AddElectricAllMdmt Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricAllMdmt Destructor.
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

        // 전기 관련 센서 불러오기
        $electricData = $this->getSensorManager($complexCodePk)->getElectricSensor();

        $this->insertElectricElectricMeterData($complexCodePk, '2층 전체 전력', $option, $electricData['2층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '3층 전체 전력', $option, $electricData['3층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '옥탑 전체 전력', $option, $electricData['옥탑 전체 전력']);

        // 2층,3층,옥탑 계산값을 가지고 진행하기 때문에 옥탑전체전력 다음에 1층 전체전력을 진행함. 순서변경금지
        $this->insertElectricElectricMeterData($complexCodePk, '1층 전체 전력', $option, $electricData['1층 전체 전력']);

        $this->insertElectricElectricMeterData($complexCodePk, '1층 전기실', $option, $electricData['1층 전기실']);
        $this->insertElectricElectricMeterData($complexCodePk, '3층 EPS실', $option, $electricData['3층 EPS실']);

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
    public function insertElectricElectricMeterData(string $complexCodePk, string $type, int $option, array $data) : void
    {
        $fcData = $this->getLastUsed($complexCodePk, $data);

        $sensorNo = '';
        $val = 0;
        $valDate = '';
        $dong = '';
        $ho = '';

        switch ($type)
        {
            case '1층 전체 전력':
                $sensorNo = '2001_1F';
                //$val = ($fcData['건물 전체 전력'] + $fcData['신재생']) - ($fcData['2층 전체 전력'] + $fcData['3층 전체 전력'] + $fcData['옥탑 전체 전력']);
                $val = $fcData['1층 전체 전력1'] + $fcData['전기온수기'] + $fcData['통신전원'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '190';
                break;
            case '2층 전체 전력':
                $sensorNo = '2001_2F';
                $val = $fcData['2층 전체 전력1'] + $fcData['바닥판넬'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '191';
                break;
            case '3층 전체 전력':
                $sensorNo = '2001_3F';
                $val = $fcData['3층 전체 전력1'] + $fcData['운송'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '192';
                break;
            case '옥탑 전체 전력':
                $sensorNo = '2001_PH';
                $val = $fcData['전기기기'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '193';
                break;
            case '1층 전기실':
                $sensorNo = '985DAD60E5ED_10';
                $val = $fcData['1층 전체 전력1'] - $fcData['1층 전체 전력2'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '101';
                break;
            case '3층 EPS실':
                $sensorNo = '985DAD60BBB0_10';
                $zeroValue = 2060400;
                $val = $fcData['3층 전체 전력1'] - $fcData['3층 전체 전력2'];
                if ($val < 1) {
                    // 3층 EPS실의 경우 마이너스가 나오면 0으로 맞춰서 정수 맞추기..
                    $val += $zeroValue;
                }
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '301';
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