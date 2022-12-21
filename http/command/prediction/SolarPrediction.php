<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Usage;

/**
 * Class SolarPrediction
 */
class SolarPrediction extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * SolarPrediction constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * SolarPrediction destructor.
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

        // 에너지원
        $energyType = isset($params[0]['energy_no']) === true ? $params[0]['energy_no'] : '0';

        // 기간조회
        $periods = $this->getPeriod($complexCodePk, $energyType, $date);

        // 현재, 예측 사용량 조회
        $useds = $this->getUsedData($complexCodePk, $energyType, $date);
        if ($useds === false) {
            $this->data = 'Error';
            return true;
        }

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

     * @return array
     *
     * @throws \Exception
     */
    private function getUsedData(string $complexCodePk, int $option, string $date) : array
    {
        $fcData = [];
        $addOptions = [];

        $dateTypes = Config::PERIODS;
        $usage = $this->usage;
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $tempDate = $date;
        $solarSensors = $this->sensorObj->getSolarSensor();

        for ($i = 0; $i < count($dateTypes); $i++) {
            $dateType = $i;
            $saveKeyName = $dateTypes[$dateType];

            $date = $usage->getDateByOption($tempDate, $dateType);
            if ($dateType === 0 || $dateType === 3 || $dateType === 4) {
                continue;
            }

            $addOptions = [
                'energy_name' => 'solar',
                'solar_type' => 'I',
                'sensor' => $solarSensors['in'],
            ];

            $d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $fcData[$saveKeyName] = $d;
        }

        return $fcData;
    }
}