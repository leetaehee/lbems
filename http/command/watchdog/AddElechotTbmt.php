<?php
namespace Http\Command;

/**
 * Class AddElechotMdmt  태백산 전열 구하기
 */
class AddElechotTbmt extends Command
{
    /** @var string|2002 $complexCodePk 단지번호 */
    private string $complexCodePk = '2002';

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

        // 1층 홀 전열은 add_electric_all_tbmt.php에서 진행
        $this->insertElectricElechotMeterData($complexCodePk, '1층 홀', $option, $elechotData['1층 홀']);
        $this->insertElectricElechotMeterData($complexCodePk, '1층 물탱크실', $option, $elechotData['1층 물탱크실']);
        $this->insertElectricElechotMeterData($complexCodePk, '2층 홀', $option, $elechotData['2층 홀']);
        $this->insertElectricElechotMeterData($complexCodePk, '2층 농산물 판매장', $option, $elechotData['2층 농산물 판매장']);
        $this->insertElectricElechotMeterData($complexCodePk, '2층 사무실', $option, $elechotData['2층 사무실']);
        $this->insertElectricElechotMeterData($complexCodePk, '3층 홀', $option, $elechotData['3층 홀']);
        $this->insertElectricElechotMeterData($complexCodePk, '3층 공관숙소1', $option, $elechotData['3층 공관숙소1']);
        $this->insertElectricElechotMeterData($complexCodePk, '3층 공관숙소2', $option, $elechotData['3층 공관숙소2']);

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
            case '1층 홀':
                $sensorNo = '2002_1_1';
                $electric1FAll = ($fcData['1층 전체 전력1'] + $fcData['신재생 소비량']) - ($fcData['2층 전체 전력1'] + $fcData['2층 전체 전력3'] + $fcData['3층 전체 전력1']);
                $electricHot = $fcData['냉난방1'] + $fcData['냉난방2'] + $fcData['냉난방3'] + $fcData['운송1'] + $fcData['전등1'] + $fcData['전등2'] + $fcData['전등3'] + $fcData['전등4'] + $fcData['환기1'] + $fcData['동력 시설1'];
                // 생산량과 소비량과 계측 시작 사용량 차이로 인해 520000을 더해서 영점 맞춤
                $val = ($electric1FAll - $electricHot) + 520000;
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '101';
                break;
            case '1층 물탱크실':
                $sensorNo = '2002_1_2';
                $val = $fcData['동력 시설1'] - ($fcData['급수 시설1'] + $fcData['급탕 시설1'] + $fcData['급탕 시설2'] + $fcData['배수 시설1'] + $fcData['급탕 에너지1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '102';
                break;
            case '2층 홀':
                $sensorNo = '2002_2_1';
                $val = $fcData['2층 전체 전력1'] - ($fcData['전등1'] + $fcData['급탕 에너지1'] + $fcData['환기1'] + $fcData['환기2']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '201';
                break;
            case '2층 농산물 판매장':
                $sensorNo = '2002_2_2';
                $val = $fcData['2층 전체 전력2'] - ($fcData['전등1'] + $fcData['급탕1'] + $fcData['환기1'] + $fcData['환기2'] + $fcData['냉난방1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '202';
                break;
            case '2층 사무실':
                $sensorNo = '2002_2_3';
                $val = $fcData['2층 전체 전력3'] - ($fcData['전등1'] + $fcData['환기1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '203';
                break;
            case '3층 홀':
                $sensorNo = '2002_3_1';
                $val = $fcData['3층 전체 전력1'] - ($fcData['전등1'] + $fcData['환기1'] + $fcData['환기2']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '301';
                break;
            case '3층 공관숙소1':
                $sensorNo = '2002_3_2';
                $val = $fcData['3층 전체 전력2'] - ($fcData['전등1'] + $fcData['급탕1'] + $fcData['환기1'] + $fcData['환기2'] + $fcData['냉난방1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '302';
                break;
            case '3층 공관숙소2':
                $sensorNo = '2002_3_3';
                $val = $fcData['3층 전체 전력3'] - ($fcData['전등1'] + $fcData['급탕1'] + $fcData['환기1'] + $fcData['냉난방1']);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '303';
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