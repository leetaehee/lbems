<?php
namespace Http\Command;

/**
 * AddElectricAllHjecc 장애인 내일 키움 직업 교육센터 전기 층,룸 사용량 조회
 */
class AddElectricAllHjecc extends Command
{
    /** @var string|2013 $complexCodePk 단지번호 */
    private string $complexCodePk = '2013';

    /** @var int|0 $option 에너지원, 전기를 의미  */
    private int $option = 0;

    /**
     * AddElectricAllHjecc Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddElectricAllHjecc Destructor.
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
        $this->insertElectricElectricMeterData($complexCodePk, '가동 메인', $option, $electricData['가동 메인']);
        $this->insertElectricElectricMeterData($complexCodePk, '나동 메인', $option, $electricData['나동 메인']);

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
                $sensorNo = '2013_ALL';
                $val = $fcData['가동 1층 전체 전력'];
                $valDate = date('YmdHis');
                $dong = '가';
                $ho = '190';
                break;
            case '가동 메인':
                $sensorNo = '2013_가';
                $val = ($fcData['가동 1층 전체 전력'] + $fcData['가동 태양광 소비량']) - $fcData['나동 1층 판넬 메인'];
                $valDate = date('YmdHis');
                $dong = '가';
                $ho = '191';
                break;
            case '나동 메인' :
                $sensorNo = '2013_나';
                $val = $fcData['나동 1층 판넬 메인'];
                $valDate = date('YmdHis');
                $dong = '나';
                $ho = '192';
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