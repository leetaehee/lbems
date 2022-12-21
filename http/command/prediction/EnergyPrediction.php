<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Usage;

/**
 * Class EnergyPrediction
 */
class EnergyPrediction extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * EnergyPrediction constructor.
     */
	public function __construct() 
	{
		parent::__construct();

        $this->usage = new Usage();
	}

    /**
     * EnergyPrediction destructor.
     */
	public function __destruct() 
	{
		parent::__destruct();
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
        $data = [];

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $date = $this->baseDateInfo['date'];

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 에너지원
        $option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;

        // 기간조회
        $periods = $this->getPeriod($complexCodePk, $option, $date);

        // 현재, 예측 사용량 조회
        $useds = $this->getUsedData($complexCodePk, $option, $date);

        // 뷰에 보여질 데이터 저장
        $data = [
            'periods' => $periods,
            'useds'=> $useds
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 주기별 기간 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getPeriod(string $complexCodePk, int $option, string $date) : array
    {
        $usage = $this->usage;

        $time = strtotime($date);

        $lastMonth = date('Ym', strtotime($date . '-1 month'));

        // 금일
        $dailyStartDate = $dailyEndDate = date('Y.m.d', strtotime($date));

        // 금주
        $dayOfTheWeek = date('w', $date);
        if ($dayOfTheWeek === '0') {
            // 현재가 일요일인 경우 다음날로 +1 변경한다.
            $time = strtotime($date . '+1 day');
        }
        $weeklyStartDate = date('Y.m.d', strtotime('LAST SUNDAY', $time));
        $weeklyEndDate = date('Y.m.d', strtotime('SATURDAY', $time));

        // 금월
        $monthPeriods = $usage->getDueDatePeriodByMonth($this, $complexCodePk, $option, $lastMonth);
        $monthStartDate = date('Y.m.d', strtotime($monthPeriods['start_date']));
        $monthEndDate = date('Y.m.d', strtotime($monthPeriods['end_date']));

        return [
            'daily' => [
                'start' => $dailyStartDate,
                'end' => $dailyEndDate
            ],
            'weekly' => [
                'start' => $weeklyStartDate,
                'end' => $weeklyEndDate
            ],
            'month' => [
                'start' => $monthStartDate,
                'end' => $monthEndDate
            ]
        ];
	}

	 /**
     * 예측에서 보여질 데이터 조회 (금일/금주/금월  현재와 예상사용량 조회)
     *
     * @param string $complexCodePk
     * @param int $option
	 * @param string $date
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    private function getUsedData(string $complexCodePk, int $option, string $date) : array
    {
        $fcData = [];
        $floorSensors = [];
        $floors = [];
        $addOptions = [];

        $usage = $this->usage;

        $tempDate = $date;
        $dateTypes = Config::PERIODS;
        $sensorTypes = Config::SENSOR_TYPES;

        if ($option === 0 && is_null($this->sensorObj) === false) {
            $floorSensors = $this->sensorObj->getElectricFloorSensor();
            $floors = array_keys($floorSensors);
        }

        for ($i = 0; $i < count($dateTypes); $i++) {
            $dateType = $i;
            $saveKeyName = $dateTypes[$dateType];

            $date = $usage->getDateByOption($tempDate, $dateType);
            if ($dateType === 0 || $dateType === 3 || $dateType === 4) {
                continue;
            }

            if (count($floors) === 0) {
                // 층별 센서정보 미정으로 되어있는 경우
                $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
                $fcData[$saveKeyName] = $d;
            }

            for ($j = 0; $j < count($floors); $j++) {
                $floor = $floors[$j];
                $sensor = $floorSensors[$floor]['all'];

                $addOptions = [
                    'floor' => $floor,
                    'sensor' => $sensor,
                    'energy_name' => $sensorTypes[$option],
                ];
                $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);

                // 테이블 구조상 lbems_db의 경우 층별 개별 검색
                $fcData[$saveKeyName]['current']['data'][$floor] = $d['current']['data'];
                $fcData[$saveKeyName]['predict']['data'][$floor] = $d['predict']['data'];
            }
        }

        return $fcData;
    }
}