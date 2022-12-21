<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Usage;

/**
 * Class MobilePrediction
 */
class MobilePrediction extends Command
{
    /** @var Usage|null $usage 사용량 | 요금  */
    private ?Usage $usage = null;

    /**
     * Prediction constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * Prediction destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행함수
     *
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $sessionComplexPk = $this->getSettingComplexCodePk($_SESSION['mb_ss_complex_pk']);
        $this->sensorObj = $this->getSensorManager($sessionComplexPk);

        $data = [];
        $option = isset($params[0]['value']) === true ? $params[0]['value'] : '0';
        $date = $this->baseDateInfo['date'];
        $dong = 'all';

        // 예측 데이터 조회
        $predictData = $this->getPredictData($sessionComplexPk, $option, $date, $dong);

        // view에 데이터 바인딩
        $data = [
            'predict_data' => $predictData,
        ];
        $this->data = $data;

        return true;
    }

    /**
     * 예측 데이터 조회 (층별로 할 경우 개별 검색으로 개선 필요)
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param string $dong
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getPredictData(string $complexCodePk, int $option, string $date, string $dong) : array
    {
        $dateTypes = Config::PERIODS;

        $fcData = [];
        $addOptions = [];

        $usage = $this->usage;

        $tempDate = $date;
        $floor = $room = 'all';

        $sensor = '';
        if ($option === 0) {
            // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        }

        for ($i = 0; $i < count($dateTypes); $i++) {
            $dateType = $i;
            $saveKeyName = $dateTypes[$dateType];

            $date = $usage->getDateByOption($tempDate, $dateType);
            if ($dateType === 0 || $dateType === 3 || $dateType === 4) {
                // 금일, 금월, 금년만 조회
                continue;
            }

            $solarType = '';
            if ($option === 11) {
                $solarType = 'I';
            }

            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'sensor' => $sensor,
                'solar_type' => $solarType,
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$saveKeyName] = $d;
        }

        return $fcData;
    }
}