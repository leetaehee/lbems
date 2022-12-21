<?php
namespace Http\Command;

/**
 * Class AddElectricAllBhmt  북한산 전기 사용량, 태양광 소비량 조회
 */
class AddElectricAllBhmt extends Command
{
    /** @var string|2017 $complexCodePk 단지번호 */
    private string $complexCodePk = '2017';

    /** @var int|0 $option 에너지원, 전기를 의미  */
    private int $option = 0;

    /**
     * AddElectricAllBhmt Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricAllBhmt Destructor.
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

        $this->insertElectricElectricMeterData($complexCodePk, '한전 전체 전력', $option, $electricData['한전 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '지하1층 전체 전력', $option, $electricData['지하1층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '1층 전체 전력', $option, $electricData['1층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '2층 전체 전력', $option, $electricData['2층 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '옥상 전체 전력', $option, $electricData['옥상 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '태양광 소비량', 11, $electricData['태양광 소비량']);

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
            case '한전 전체 전력':
                $sensorNo = '2017_ALL';
                $val = $fcData['지하1층 전기실'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '190';
                break;
            case '지하1층 전체 전력' :
                $sensorNo = '2017_B1';
                $val = $fcData['지하1층 화장실복도1'] + $fcData['지하1층 화장실복도2'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '191';
                break;
            case '1층 전체 전력' :
                $sensorNo = '2017_1F';
                $val = $fcData['1층 화장실복도'] + $fcData['1층 EPS실'] + $fcData['1층 주방휴게실'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '192';
                break;
            case '2층 전체 전력' :
                $sensorNo = '2017_2F';
                $val = $fcData['2층 복도'] + $fcData['2층 EPS실'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '193';
                break;
            case '옥상 전체 전력' :
                $sensorNo = '2017_PH';
                $val = $fcData['지붕층 옥외'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '194';
                break;
            case '태양광 소비량' :
                $sensorNo = '2017_OUT_1';
                $val = $fcData['신재생1'] + $fcData['신재생2'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '195';

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