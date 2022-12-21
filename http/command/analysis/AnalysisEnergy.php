<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class AnalysisEnergy 분석 > 단위면적당 사용분석
 */
class AnalysisEnergy extends Command
{
    /** @var Usage|null $usage 사용량 */
    private ?Usage $usage = null;

    /**
     * AnalysisEnergy constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * AnalysisEnergy destructor.
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
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [
            'last' => [],
            'now' => []
        ];

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $option = isset($params[0]['value']) == true ? $params[0]['value'] : 0;
        $dateType = isset($params[1]['value']) == true ? Utility::getInstance()->removeXSS($params[1]['value']) : 0;
        $date = isset($params[2]['value']) == true ? Utility::getInstance()->removeXSS($params[2]['value']) : '0000-00-00';
        $floor = isset($params[3]['value']) == true ? Utility::getInstance()->removeXSS($params[3]['value']) : 'all';
        $room = isset($params[4]['value']) == true ? Utility::getInstance()->removeXSS($params[4]['value']) : 'all';
        $energyKey = isset($params[5]['value']) === true ? $params[5]['value'] : '';
        $dong = 'all';

        $sensors = [];
        $keySensor = [];
        $sensor = '';

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 분석 > 단위면적당 사용분석 데이터 조회
        $analysisData = $this->getAnalysisEnergyUseData($complexCodePk, $option, $date, $dateType, $dong, $floor, $room, $energyKey, $sensor);

        $data['now'] = $analysisData['now'];
        $data['last'] = $analysisData['last'];
        
        // 뷰에 데이터 바인딩
        $this->data = $data;
        return true;
    }

    /**
     * 분석 > 단위면적당 사용분석 데이터 조회
     * 
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param int $dateType
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $energyKey
     * @param string $sensor
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getAnalysisEnergyUseData(string $complexCodePk, int $option, string $date, int $dateType, string $dong, string $floor, string $room, string $energyKey, string $sensor): array
    {
        $fcData = [];
        $addOptions = [];
        $keySensor = [];
        $keySensors = [];
		$nows = [];
		$lasts = [];

        $lToday = date('Ymd');
        $usage = $this->usage;

        if ($option === 0) {
            // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        }

        // 설비별 및 에너지 키 네이름&배열로 넘어와 조회 하는 경우
        $keySensors = $this->sensorObj->getSpecialSensorKeyName();
        if (is_null($keySensors[$energyKey]) === false) {
            $keySensor = $keySensors[$energyKey];
        }

        $date = str_replace('-', '', $date);
        $date = $usage->getDateByOption($date, $dateType);

        // 주기에 따른 조회 날짜 출력 (현재)
        $dates = $this->getPeriod($date, $dateType);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // 주기에 따른 조회 날짜 출력 (과거)
        $lastDate = $usage->getLastDate($date, $dateType);
        $lastDates = $this->getPeriod($lastDate, $dateType);

        $lastStartDate = $lastDates['start'];
        $lastEndDate = $lastDates['end'];

        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $energyKey,
            'is_area' => true,
        ];

        // 금일인 경우 단위 면적 false로 하여 후속작업에서의 단위 변환을 위함.
        $dailyIsArea = $this->getDailyIsAreaValue($dateType, $lToday, $startDate);

        if (count($keySensors) > 0 && count($keySensor) > 0) {
            if ($dateType === 0) {
                $addOptions['is_use_next_date'] = false;

                $nows = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $startDate, $addOptions, $keySensor);
                $lasts = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $lastStartDate, $addOptions, $keySensor);
            } else {
                $addOptions['is_area'] = $dailyIsArea;
                $nows = $usage->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions, $keySensor);

                $addOptions['is_area'] = true;
                $lasts = $usage->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType, $lastStartDate, $lastEndDate, $addOptions, $keySensor);
            }
        } else {
            $addOptions['sensor'] = $sensor;

            if ($dateType === 0) {
                $addOptions['is_use_next_date'] = false;

                $nows = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
                $lasts = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $lastStartDate, $addOptions);
            } else {
                $addOptions['is_area'] = $dailyIsArea;
                $nows = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);

                $addOptions['is_area'] = true;
                $lasts = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $lastStartDate, $lastEndDate, $addOptions);
            }
        }

        if (is_bool($dailyIsArea) === true && $dailyIsArea === false) {
            // 금일인경우 단위면적 환산 전 값으로 변경 후 다시 재계산.. 캐시는 환산되지 않아서..
            if (is_null($nows) === true) {
                $nows = [];
            }
            $nows = $usage->setAreaValue($this, $complexCodePk, $option, $energyKey, $dateType, $date, $nows, true);
        }

        $fcData['now'] = $nows;
        $fcData['last'] = $lasts;

        return $fcData;
    }

    /**
     * 주기에 따른 검색 날짜 조회
     *
     * @param string $date
     * @param string $dateType
     *
     * @return array $fcData
     */
    private function getPeriod(string $date, string $dateType) : array
    {
        $fcData = [];

        switch ($dateType)
        {
            case 0:
                // 금년
                $temp = explode('-', $date);

                // 시작(월)~종료(월)
                $startDate = $temp[0];
                $endDate = $temp[0];

                $fcData = [
                    'start' => $startDate,
                    'end' => $endDate,
                ];
                break;
            case 1:
            case 6:
                // 금월
                $date = date('Y-m-d', strtotime($date . '01'));

                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                $fcData = [
                    'start' => $startDate,
                    'end' => $endDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));

                $fcData = [
                    'start' => $today,
                    'end' => $today
                ];
                break;
        }

        return $fcData;
    }

    /**
     * 금일에 따른 단위면적 true/false 반환 여부
     *
     * @param int $dateType
     * @param string $today
     * @param string $compareDate
     *
     * @return bool
     */
    private function getDailyIsAreaValue(int $dateType, string $today, string $compareDate) : bool
    {
        return ($dateType === 2 && $today === $compareDate) ? false : true;
    }
}