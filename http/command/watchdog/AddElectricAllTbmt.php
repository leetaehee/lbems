<?php
namespace Http\Command;

/**
 * Class AddElectricAllTbmt  태백산 전기 층,룸 사용량 조회
 */
class AddElectricAllTbmt extends Command
{
    /** @var string|2002 $complexCodePk 단지번호 */
    private string $complexCodePk = '2002';

    /** @var int|0 $option 에너지원, 전기를 의미  */
    private int $option = 0;

    /**
     * AddElectricAllTbmt Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricAllTbmt Destructor.
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

        $this->insertElectricElectricMeterData($complexCodePk, '1층 전체 전력', $option, $electricData['1층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '2층 전체 전력', $option, $electricData['2층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '3층 전체 전력', $option, $electricData['3층 전체 전력']);

        // 층별 전체 사용량 구한 후, 전체 전력 구해야 함.
        $this->insertElectricElectricMeterData($complexCodePk, '전체 전력', $option, $electricData['전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '한전 전체 전력', $option, $electricData['한전 전체 전력']);

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

        switch ($type)
        {
            case '전체 전력':
                $sensorNo = '2002_1_2_3_F';
                $val = $fcData['1층 전체 전력'] + $fcData['2층 전체 전력'] + $fcData['3층 전체 전력'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '000';
                break;
            case '한전 전체 전력':
                $sensorNo = '2002_ALL';
                // 생산량과 소비량과 계측 시작 사용량 차이로 인해 520000을 더해서 영점 맞춤
                $val = $fcData['전체 전력'] - ($fcData['신재생1'] + 520000);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '190';
                break;
            case '1층 전체 전력':
                $sensorNo = '2002_1F';
                // 생산량과 소비량과 계측 시작 사용량 차이로 인해 520000을 더해서 영점 맞춤
                $val = ($fcData['1층 전체 전력1'] + $fcData['신재생 소비량'] + 520000) - ($fcData['2층 전체 전력1'] + $fcData['2층 전체 전력3'] + $fcData['3층 전체 전력1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '191';
                break;
            case '2층 전체 전력':
                $sensorNo = '2002_2F';
                $val = $fcData['2층 전체 전력1'] + $fcData['2층 전체 전력2'] + $fcData['2층 전체 전력3'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '192';
                break;
            case '3층 전체 전력':
                $sensorNo = '2002_3F';
                $val = $fcData['3층 전체 전력1'] + $fcData['3층 전체 전력2'] + $fcData['3층 전체 전력3'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '193';
                break;
            case '1층 홀':
                $sensorNo = '2002_1_1';
                $val = $fcData['1층 전체 전력'] - ($fcData['냉난방1'] + $fcData['냉난방2'] + $fcData['냉난방3'] + $fcData['운송1'] + $fcData['전등1'] + $fcData['전등2'] + $fcData['전등3'] + $fcData['전등4'] + $fcData['환기1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '101';
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