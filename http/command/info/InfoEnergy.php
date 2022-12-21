<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Usage;

/**
 * Class InfoEnergy 정보감시
 */
class InfoEnergy extends Command
{
    /** @var  Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * InfoEnergy constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * InfoEnergy destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수.
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
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $date = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '0000';
        $dateType = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 0;
        $floor = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : 'all';
        $room = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : 'all';
        $energyKey = isset($params[5]['value']) === true ? $params[5]['value'] : 'electric';
        $dong = isset($params[6]['value']) === true ? $params[6]['value'] : 'all';

        $option = (int)$option;
        $dateType = (int)$dateType;

        $sensor = '';

        if ($dateType === 1) {
            // 금월 데이터 bems_meter_ 테이블로 검색하고자 할 경우 (한달만 검색 할 것)
            //$dateType = 6;
        }

        // 주기에 따른 조회 날짜 출력
        $periods = $this->getPeriod($date, $dateType);

        // 부하별 기준값 데이터 조회
        $standardData = $this->getStandard($option, $complexCodePk, $dateType);

        // 사용량 조회
        $useds = $this->getUsedByPeriod($complexCodePk, $option, $dateType, $periods, $dong, $floor, $room, $sensor, $energyKey);

        // 주요지표 데이터 조회
        $indicators = $this->getMainIndicator($useds, $standardData);

        // 평균값 및 비율 계산 (현재시점으로..)
        $statusData = $this->getStatusData($complexCodePk, $option, $dateType, $periods, $standardData, $dong, $floor, $room, $sensor, $energyKey);

        // 커스텀 단위
        $customUnits = $this->sensorObj->getCustomUnit();

        // 뷰에 보여질 데이터 전달
        $data = [
            'indicator_data' => $indicators,
            'status_data' => $statusData,
            'custom_unit' => $customUnits[$energyKey],
        ];

        $this->data = $data;

        return true;
    }

    /**
     * 주기에 따른 검색 날짜 조회
     *
     * @param string $date
     * @param int $dateType
     *
     * @return array
     */
    private function getPeriod(string $date, int $dateType) : array
    {
        $fcData = [];

        switch ($dateType)
        {
            case 0:
                // 금년
                $endMonth = date('Y-m', strtotime($date));
                $temp = explode('-', $endMonth);

                // 시작(월)~종료(월)
                $startDate = $temp[0] . '01';
                $endDate = $temp[0] . '' . '12';
                $endThisDate = $temp[0] . '' . date('m');

                // 주요지표에서 전년 구하기 위한 날짜
                $prevStartDate = date('Ym', strtotime("-1 year", strtotime($startDate . '01')));
                $prevEndDate = date('Ym', strtotime("-1 year", strtotime($endDate . '01')));
                $endThisDate = date('Ym', strtotime($endThisDate . '01'));

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'this_end_date' => $endThisDate,
                    'prev_start_date' => $prevStartDate,
                    'prev_end_date' => $prevEndDate
                ];

                break;
            case 1:
            case 6:
                // 금월
                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $temp[2];
                $endThisDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                // 주요지표에서 전월 구하기 위한 날짜
                $prevStartDate = date("Ymd", strtotime('-1 month', strtotime($startDate)));
                $prevEndDate = date("Ymd", strtotime('-1 month', strtotime($date)));
                $endThisDate = date("Ymd",strtotime($endThisDate));

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'this_end_date' => $endThisDate,
                    'prev_start_date' => $prevStartDate,
                    'prev_end_date' => $prevEndDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));
                $prevDate = date("Ymd", strtotime("-1 day", strtotime($date)));

                $fcData = [
                    'today' => $today,
                    'prev_date' => $prevDate
                ];
                break;
        }

        return $fcData;
    }

    /**
     * 경,중,최대부하 기준값 데이터 조회
     *
     * @param int $option
     * @param string $complexCodePk
     * @param int $dateType
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getStandard(int $option, string $complexCodePk, int $dateType) : array
    {
        $fcData = [];
        $periodStandard = '';

        $usage = $this->usage;;
        $standardData = $usage->getReference($this, $complexCodePk, $option);

        $standards = explode('/', $standardData);

        switch ($dateType)
        {
            case 0:
                // 금년
                $periodStandard = $standards[2];
                break;
            case 1:
            case 6:
                // 금월
                $periodStandard = $standards[1];
                break;
            case 2:
                // 금일
                $periodStandard = $standards[0];
                break;
        }

        $fcData = [
            'period_standard' => $periodStandard,
            'standards' => [
                'low_status'=> [
                    'min'=> 0,
                    'max'=> $periodStandard + ($periodStandard * (20/100))
                ],
                'mid_status'=> [
                    'min'=> $periodStandard + ($periodStandard * (20/100)),
                    'max'=> $periodStandard + ($periodStandard * (50/100))
                ],
                'max_status'=> [
                    'min'=> $periodStandard + ($periodStandard * (50/100)),
                    'max'=> $periodStandard + ($periodStandard * (100/100))
                ]
            ]
        ];

        return $fcData;
    }

    /**
     * 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param array $periods
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $sensor
     * @param string $energyKey
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getUsedByPeriod(string $complexCodePk, int $option, int $dateType, array $periods, string $dong, string $floor, string $room, string $sensor, string $energyKey) : array
    {
        $fcData = [];
        $cacheData = [];

        $usage = $this->usage;

        if ($dateType === 2) {
            // 금일
            $startDate = $endDate = $periods['today'];
            $prevStartDate = $prevEndDate = $periods['prev_date'];
        }

        if ($dateType === 1 || $dateType === 6) {
            // 금월
            $prevStartDate = $periods['prev_start_date'];
            $prevEndDate = $periods['prev_end_date'];
            $startDate = $periods['start_date'];
            $endDate = $periods['end_date'];
        }

        if ($dateType === 0) {
            // 금년
            $prevStartDate = $usage->getDateByOption($periods['prev_start_date'], $dateType);
            $prevEndDate = $usage->getDateByOption($periods['prev_end_date'], $dateType);
            $startDate = $usage->getDateByOption($periods['start_date'], $dateType);
            $endDate = $usage->getDateByOption($periods['end_date'], $dateType);
        }

        // 이전 사용량 조회
        $prevData = $this->getInfoData($complexCodePk, $option, $dateType, $prevStartDate, $prevEndDate, $dong, $floor, $room, $sensor, $energyKey);

        // 현재 사용량 조회
        $nowData = $this->getInfoData($complexCodePk, $option, $dateType, $startDate, $endDate, $dong, $floor, $room, $sensor, $energyKey);

        $fcData = [
            'prev_data' => $prevData['data'],
            'now_data' => $nowData['data'],
        ];

        return $fcData;
    }

    /**
     * 주요지표 데이터 조회
     *
     * @param array $used
     * @param array $standardData
     *
     * @return array
     */
    private function getMainIndicator(array $used, array $standardData) : array
    {
        $fcData = [];

        $prevData = $used['prev_data'];
        $nowData = $used['now_data'];

        $periodStandard = $standardData['period_standard'];
        $standards = $standardData['standards'];

        $preValues = array_values($prevData);
        $nowValues = array_values($nowData);

        $prevDates = array_keys($prevData);
        $nowDates = array_keys($nowData);

        $curIndicators = [];
        $prevIndicators = [];

        $lowStatusPrevSum = $midStatusPrevSum = $maxStatusPrevSum = 0;
        $lowStatusCurSum = $midStatusCurSum = $maxStatusCurSum = 0;

        for ($i = 0; $i < count($preValues); $i++) {
            // 현재
            $nowValue = $nowValues[$i];

            if ($nowValue >= $periodStandard) {
                if ($nowValue <= $standards['low_status']['max']) {
                    $lowStatusCurSum += $nowValue;
                }
            }

            if ($nowValue >= $standards['mid_status']['min']) {
                if ($nowValue <= $standards['mid_status']['max']) {
                    $midStatusCurSum += $nowValue;
                }
            }

            if ($nowValue > $standards['max_status']['min']) {
                $maxStatusCurSum += $nowValue;
            }

            $curIndicators = [
                'date'=> $nowDates[$i],
                'low'=> $lowStatusCurSum,
                'mid'=> $midStatusCurSum,
                'max'=> $maxStatusCurSum
            ];

            // 이전
            $preValue = $preValues[$i];

            if ($preValue >= $periodStandard) {
                if ($preValue <= $standards['low_status']['max']) {
                    $lowStatusPrevSum += $preValue;
                }
            }

            if ($preValue >= $standards['mid_status']['min']) {
                if ($preValue <= $standards['mid_status']['max']) {
                    $midStatusPrevSum += $preValue;
                }
            }

            if ($preValue > $standards['max_status']['min']) {
                $maxStatusPrevSum += $preValue;
            }
        }

        $curIndicators = [
            //'date'=> $nowDates[$i],
            'low'=> $lowStatusCurSum,
            'mid'=> $midStatusCurSum,
            'max'=> $maxStatusCurSum
        ];

        $prevIndicators = [
            //'date'=> $prevDates[$i],
            'low'=> $lowStatusPrevSum,
            'mid'=> $midStatusPrevSum,
            'max'=> $maxStatusPrevSum
        ];

        $cid = $curIndicators;
        $pid = $prevIndicators;

        $lowDifferPercent = $midDifferPercent = $maxDifferPercent = 0;

        /**
         * sample.
         * (현월 사용량 - 전월 사용량)/(현월 사용량 + 전월 사용량) * 100
        if (($icd['mid']+$ipd['mid']) >= 0) {
        $midDifferPercent = (($icd['mid']-$ipd['mid'])/($icd['mid']+$ipd['mid']))*100;
        ...
        }
         */

        if (($pid['low']) > 0) {
            // 경부하
            $lowDifferPercent = (($cid['low']/$pid['low'])*100)-100;
        }

        if ($pid['low'] === 0 && $cid['low'] > 0) {
            // 경부하- 이전이 0이고, 현재가 늘어난 경우
            $lowDifferPercent = 100;
        }

        if (($pid['mid']) > 0) {
            // 중부하
            $midDifferPercent = (($cid['mid']/$pid['mid'])*100)-100;
        }

        if ($pid['mid'] === 0 && $cid['mid'] > 0) {
            // 중부하- 이전이 0이고, 현재가 늘어난 경우
            $midDifferPercent = 100;
        }

        if (($pid['max']) > 0) {
            // 최대부하
            $maxDifferPercent = (($cid['max']/$pid['max'])*100)-100;
        }

        if ($pid['max'] === 0 && $cid['max'] > 0) {
            // 최대부하- 이전이 0이고, 현재가 늘어난 경우
            $maxDifferPercent = 100;
        }

        $fcData = [
            'low' => [
                'prev_period_sum' => $pid['low'],
                'cur_period_sum' => $cid['low'],
                'differ_percent' => $lowDifferPercent,
            ],
            'mid' => [
                'prev_period_sum' => $pid['mid'],
                'cur_period_sum' => $cid['mid'],
                'differ_percent' => $midDifferPercent,
            ],
            'max' => [
                'prev_period_sum' => $pid['max'],
                'cur_period_sum' => $cid['max'],
                'differ_percent' => $maxDifferPercent,
            ],
        ];

        return $fcData;
    }

    /**
     * 평균 사용량과 부하별 비율 계산
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param array $periods
     * @param array $standardData
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $sensor
     * @param string $energyKey
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getStatusData(string $complexCodePk, int $option, int $dateType, array $periods, array $standardData, string $dong, string $floor, string $room, string $sensor, string $energyKey) : array
    {
        $fcData = [];
        $cacheData = [];

        $usage = $this->usage;
        $fileCache = $this->fileCache;

        if ($dateType === 2) {
            // 금일
            $startDate = $endDate = $periods['today'];
            $prevStartDate = $prevEndDate = $periods['prev_date'];
        }

        if ($dateType === 1 || $dateType === 6) {
            // 금월
            $startDate = $periods['start_date'];
            $endDate = $periods['this_end_date'];
        }

        if ($dateType === 0) {
            // 금년
            $startDate = $usage->getDateByOption($periods['start_date'], $dateType);
            $endDate = $usage->getDateByOption($periods['this_end_date'], $dateType);
        }

        $standard = (float)$standardData['period_standard'];
        $standards = $standardData['standards'];

        // 현재 사용량 조회
        $nowData = $this->getInfoData($complexCodePk, $option, $dateType, $startDate, $endDate, $dong, $floor, $room, $sensor, $energyKey);

        $lowStatusSum = 0;
        $midStatusSum = 0;
        $maxStatusSum = 0;
        $normalStatusSum = 0;
        $infoStatusData = [];

        foreach ($nowData['data'] AS $k => $v) {
            // 부하타입 (경부하- 0, 중부하- 1, 최대부하- 2)
            $infoType = 0;

            // 부하구분
            if ($v < $standard) {
                $infoType = 3; // 정상
                $normalStatusSum += $v;
            }

            if ($v >= $standard) {
                if ($v <= $standards['low_status']['max']) {
                    $infoType = 0; // 경부하
                    $lowStatusSum += $v;
                }
            }

            if ($v >= $standards['mid_status']['min']) {
                if ($v <= $standards['mid_status']['max']) {
                    $infoType = 1; // 중부하
                    $midStatusSum += $v;
                }
            }

            if ($v > $standards['max_status']['min']) {
                $infoType = 2; // 최대부하
                $maxStatusSum += $v;
            }

            $infoStatusData[] = [
                'date'=> $k,
                'val'=> $v,
                'type'=> $infoType,
                'standard'=> $standard,
            ];
        }

        // 기준값 대비 평균 사용량
        $totalSumStatus = ($lowStatusSum + $midStatusSum + $maxStatusSum);
        if ($totalSumStatus < 1) {
            $lowStatusTypeRate = $midStatusTypeRate = $maxStatusTypeRate = 0;
            $averageUseage = 0;
        } else {
            $lowStatusTypeRate = round(($lowStatusSum/$totalSumStatus)*100);
            $midStatusTypeRate = round(($midStatusSum/$totalSumStatus)*100);
            $maxStatusTypeRate = round(($maxStatusSum/$totalSumStatus)*100);

            $dateCount = count($infoStatusData);
            $averageUseage = $totalSumStatus/$dateCount;
        }

        $averagePercent = 0;
        if ($averageUseage > 0 && $standard > 0) {
            $averagePercent = ($averageUseage/$standard)*100;
        }


        $fcData = [
            'status_data' => $infoStatusData,
            'status_sum' => [
                'low' => $lowStatusSum,
                'mid' => $midStatusSum,
                'max' => $maxStatusSum
            ],
            'status_rate' => [
                // 부하별 비율
                'low'=> $lowStatusTypeRate,
                'mid'=> $midStatusTypeRate,
                'max'=> $maxStatusTypeRate
            ],
            'status_average' => [
                // 평균 사용량
                'average'=> number_format($averageUseage),
                'percent'=> number_format($averagePercent)
            ]
        ];

        return $fcData;
    }

    /**
     * 센서별로 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $startDate
     * @param string $endDate
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $sensor
     * @param string $energyKey
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
    private function getInfoData(string $complexCodePk, int $option, int $dateType, string $startDate, string $endDate, string $dong, string $floor, string $room, string $sensor, string $energyKey) : array
    {
        $fcData = [];
        $keySensors = [];
        $keySensor = [];
        $addOptions = [];

        $usage = $this->usage;

        if ($option === 0) {
            // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        }

        // 에너지 키 네임&배열로 넘어와 조회 하는 경우
        $keySensors = $this->sensorObj->getSpecialSensorKeyName();
        if (is_null($keySensors[$energyKey]) === false) {
            $keySensor = $keySensors[$energyKey];
        }

        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $energyKey,
        ];

        if (is_array($keySensors) === true && count($keySensor) > 0) {
            if ($dateType === 0) {
                $addOptions['is_use_next_date'] = false;

                $fcData = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $startDate, $addOptions, $keySensor);
            } else {
                $fcData = $usage->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions, $keySensor);
            }
        } else {
            $addOptions['sensor'] = $sensor;

            if ($dateType === 0) {
                $fcData = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
            } else {
                $fcData = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
            }
        }

        return $fcData;
    }
}