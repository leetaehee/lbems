<?php
namespace Http\Command;

/**
 * AddElectricAllKhc 김해 행정복지센터 전기 층,룸 사용량 조회
 */
class AddElectricAllKhc extends Command
{
    /** @var string|2010 $complexCodePk 단지번호 */
    private string$complexCodePk = '2010';

    /** @var int|0 $option 에너지원, 전기를 의미  */
    private int $option = 0;

    /** @var array $electricSensors 전기 관련 계산식 센서정보  */
    private array $electricSensors = [];

    /**
     * AddElectricAllKhc Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricAllKhc Destructor.
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
        $this->electricSensors = $electricData;

        $this->insertElectricElectricMeterData($complexCodePk, '한전 전체 전력', $option, $electricData['한전 전체 전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '지하1층 전체전력', $option, $electricData['지하1층 전체전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '1층 전체전력', $option, $electricData['1층 전체전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '2층 전체전력', $option, $electricData['2층 전체전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '3층 전체전력', $option, $electricData['3층 전체전력']);
        $this->insertElectricElectricMeterData($complexCodePk, '전기 가스 태양광', $option, $electricData['전기 가스 태양광']);

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
    private function insertElectricElectricMeterData(string $complexCodePk, string $type, int $option, array $data) : void
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
                $sensorNo = '2010_ALL';
                $val = $this->getBuildingElectricCalculateSum($complexCodePk, $type);
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '190';
                break;
            case '지하1층 전체전력':
                $sensorNo = '2010_B1';
                $val = $fcData['지하1층 전체1'] + $fcData['지하1층 전체3'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '191';
                break;
            case '1층 전체전력':
                $sensorNo = '2010_1F';
                $val = $fcData['1층 전체1'] + $fcData['1층 전체2'] + $fcData['1층 전체3'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '192';
                break;
            case '2층 전체전력':
                $sensorNo = '2010_2F';
                $val = $fcData['2층 전체1'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '193';
                break;
            case '3층 전체전력':
                $sensorNo = '2010_3F';
                $val = $fcData['3층 전체1'];
                $valDate = date('YmdHis');
                $dong = 'A';
                $ho = '194';
                break;
            case '전기 가스 태양광' :
                $sensorNo = '2010_EGS';
                $val = $this->getBuildingElectricCalculateSum($complexCodePk, '한전 전체 전력') + $fcData['GHP'] + $fcData['태양광 발전량'];
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

    /**
     * 한전전체전력 계산식
     *
     * @param string $complexCodePk
     * @param string $key
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getBuildingElectricCalculateSum(string $complexCodePk, string $key) : int
    {
        $fcValue = 0;

        $sensors = $this->electricSensors[$key];
        if (count($sensors) < 1) {
            return $fcValue;
        }

        $fcData = $this->getLastUsed($complexCodePk, $sensors);

        $fcValue = $fcData['신재생1'] + $fcData['운송1'] + $fcData['전기차 충전1'] + $fcData['지하1층 전체1'] + $fcData['지하1층 전체3'] + $fcData['1층 전체1'] + $fcData['1층 전체2'] + $fcData['1층 전체3'] + $fcData['2층 전체1'] + $fcData['3층 전체1'] + $fcData['옥상 전체1'];

        return $fcValue;
    }
}