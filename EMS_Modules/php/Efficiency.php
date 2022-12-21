<?php
namespace EMS_Module;

use Http\Command\Command;
use Http\SensorManager;

/**
 * Class Efficiency 역률
 */
class Efficiency
{
    /** @var EmsQuery|null $emsQuery */
    private ?EMSQuery $emsQuery = null;

    /**
     * Efficiency constructor.
     */
    public function __construct()
    {
        $this->emsQuery = new EMSQuery();
    }

    /**
     * Efficiency destructor.
     */
    public function __destruct()
    {
    }

    /**
     * SensorManager 사용하기
     *
     * @param string $complexCodePk
     *
     * @return SensorInterface
     */
    private function getSensorManager(string $complexCodePk) : SensorInterface
    {
        $sensorManager = new SensorManager();

        return $sensorManager->getSensorObject($complexCodePk);
    }

    /**
     * 주기별 쿼리
     *
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQuery(int $option, int $dateType, string $date, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $query = '';

        switch ($dateType) {
            case 0:
                // year
                $query = $this->emsQuery->getQueryEfficiencyYearData($option, $date, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            case 1:
                // month
                $query = $this->emsQuery->getQueryEfficiencyMonthData($option, $startDate, $endDate, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            case 2:
                // day
                $query = $this->emsQuery->getQueryEfficiencyDayData($option, $date, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            case 5:
                // daily 테이블 - 기간별 검색 '월', 기간별 일 검색, '주' 검색
                $query = $this->emsQuery->getQueryEfficiencyMonthRangeData($option, $startDate, $endDate, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            default:
                // code...
                break;
        }

        return $query;
    }

    /**
     * 주기별 쿼리 (home 테이블 기준)
     *
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryByHome(int $option, int $dateType, string $date, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $query = '';

        switch ($dateType) {
            case 0:
                // 년 검색
                $query = $this->emsQuery->getQueryEfficiencyYearDataByHome($option, $date, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            case 1:
                // 월 검색
                $query = $this->emsQuery->getQueryEfficiencyMonthDataByHome($option, $startDate, $endDate, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            case 4:
            case 2:
                // 금일
                $query = $this->emsQuery->getQueryEfficiencyCurrentDayHomeDataBySensorTable($option, $date, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
                break;
            default:
                break;
        }

        return $query;
    }

    /**
     * 주기별 배열 값 정리. (마감일 기준)
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param string $date
     * @param int $dueDay
     * @param int $dateType
     * @param bool $isUseNextDate
     *
     * @return array
     *
     * @throws \Exception
     */
    public function rearrangeData(string $complexCodePk, string $customUnit, array $d, string $date, int $dueDay, int $dateType, bool $isUseNextDate) : array
    {
        $temp = $d;

        switch ($dateType) {
            case 0:
                //year
                $temp = Utility::getInstance()->makeYearData($complexCodePk, $customUnit, $d, $date, 'ym', 'val');
                break;
            case 1:
                //month
                $temp = Utility::getInstance()->makeMonthData($complexCodePk, $customUnit, $d, $date, $dueDay, 'val_date', 'val', $isUseNextDate);
                break;
            case 2:
                //day
                $temp = [];
                $tempDate = $d[0]['val_date'];

                if (substr($tempDate, 0, 4) == '0000' || empty($tempDate) === true) {
                    $tempDate = $date;
                }

                for ($i = 0; $i < 24; $i++) {
                    $col = "val_${i}";
                    $temp2 = $tempDate . Utility::getInstance()->toTwoDigit($i);
                    $val = $d[0][$col];
                    if (empty($val) === true) {
                        $val = 0;
                    }

                    if (empty($val) === false && $val < 0) {
                        $val = 0;
                    }
                    $temp[$temp2] =  Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
                }
                break;
            case 5:
                //week
                $temp = [];

                $len = count($d);
                for ($i = 0; $i < $len; $i++) {
                    $time = $d[$i]['val_date'];

                    $val = $d[$i]['val'];
                    if ($val < 0) {
                        $val = 0;
                    }

                    $temp[$time] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
                }
                break;
            default:
                // code...
                break;
        }

        return $temp;
    }

    /**
     * 주기별 배열 값 정리. (마감일 관련 없음)
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param string $start
     * @param string $end
     * @param int $dateType
     *
     * @return array $temp
     */
    public function rearrangeNotDueDayData(string $complexCodePk, string $customUnit, array $d, string $start, string $end, int $dateType) : array
    {
        $temp = $d;

        switch ($dateType) {
            case 0:
                //year
                $temp = Utility::getInstance()->makeYearData($complexCodePk, $customUnit, $d, $start, 'ym', 'val');
                break;
            case 1:
            case 6:
                //month
                $temp = Utility::getInstance()->makeNotDueDayMonthData($complexCodePk, $customUnit, $d, $start, $end, 'val_date', 'val');
                break;
            case 2:
                //day
                if (count($d) > 0) {
                    $temp = [];
                    $tempDate = $d[0]['val_date'];

                    if (substr($tempDate, 0, 4) == '0000' || empty($tempDate) === true) {
                        $tempDate = $start;
                    }

                    for ($i = 0; $i < 24; $i++) {
                        $col = "val_${i}";
                        $temp2 = $tempDate . Utility::getInstance()->toTwoDigit($i);
                        $val = $d[0][$col];

                        if (empty($val) === true) {
                            $val = 0;
                        }

                        if (empty($val) === false && $val < 0) {
                            $val = 0;
                        }

                        $temp[$temp2] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);;
                    }
                }
                break;
            case 5:
                //기간별 월 검색
                $temp = Utility::getInstance()->makeMonthRangeData($complexCodePk, $customUnit, $d, $start, $end, 'val_date', 'val');
                break;
            default:
                // code...
                break;
        }

        return $temp;
    }

    /**
     * 예측 사용량 정보 포맷화
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param string $key
     * @param array $currents
     *
     * @return array $fcData
     */
    private function rearrangeDataByPredict(string $complexCodePk, string $customUnit, string $key, array $currents) : array
    {
        $fcData = [];

        $dateCount = count($currents);
        $keyName = '';

        if ($dateCount > 1) {
            for ($i = 0; $i < $dateCount; $i++) {
                $currentValue = 0;

                $keyName = $currents[$i]['home_grp_pk'];
                $keyName = ($key === 'all') ? $key : $keyName;

                if ($currents[$i]['val'] > 0) {
                    $currentValue = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $currents[$i]['val']);
                }

                $fcData['current']['data'][$keyName] += (float)$currentValue;
            }
        } else {
            $keyName = empty($keyNamey) === true ? $key : $keyName;
            $currentValue = (float)$currents[0]['val'];

            $fcData['current']['data'][$keyName] = $currentValue < 0 ? 0 : Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $currentValue);
        }

        return $fcData;
    }

    /**
     * $addOptions 배열을 개별 분리
     *
     * @param array $addOptions
     *
     * @return array
     */
    private function getAssignmentFromOptions(array $addOptions) : array
    {
        $fcData = [];

        $floor = isset($addOptions['floor']) === true ? $addOptions['floor'] : 'all';
        $room = isset($addOptions['room']) === true ? $addOptions['room'] : 'all';
        $sensor = isset($addOptions['sensor']) === true ? $addOptions['sensor'] : '';
        $isUseNextDate = isset($addOptions['is_use_next_date']) === true ? $addOptions['is_use_next_date'] : true;
        $energyName = isset($addOptions['energy_name']) === true ? $addOptions['energy_name'] : '';
        $startDate = isset($addOptions['start_date']) === true ? $addOptions['start_date'] : '';
        $endDate = isset($addOptions['end_date']) === true ? $addOptions['end_date'] : '';
        $isCache = isset($addOptions['is_cache']) === true ? $addOptions['is_cache'] : false;

        $fcData = [
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'is_use_next_date' => $isUseNextDate,
            'energy_name' => $energyName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_cache' => $isCache
        ];

        return $fcData;
    }

    /**
     * 효율 조회 (마감일 기준)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param array $addOptions
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getEfficiencyData(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions) : array
    {
        $ret = [];

        $lDay = date('j');
        $lToday = date('Ymd');

        // 해체할당
        $assigns = $this->getAssignmentFromOptions($addOptions);
        $isUseNextDate = $assigns['is_use_next_date'];

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $floorQuery = Utility::getInstance()->makeWhereClause('home', 'home_grp_pk', $assigns['floor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);

        $query = $this->emsQuery->getDueday($option, $complexCodePk);
        $d = $command->query($query);
        $dueDay = $d[0]['closing_day'];

        $tempDueDay = $dueDay < 10 ? "0{$dueDay}" : $dueDay;
        $tempDueDay = date('d', strtotime(date('Ym') . $tempDueDay));

        $isEndDay = false;
        if ($dueDay === 99) {
            $isEndDay = true;
        }

        $dueDate = date('Ym') . $tempDueDay;
        $tmpNextDate = date('Ym', strtotime('+1 months', strtotime($dueDate)));

        if ($dateType == 2 && $date == date('Ymd')) {
            //금일도 일데이터를 시간단위로 생성하기 때문에 일통계를 바라보도록 수정.
            //$dateType = 4;
        }

        $tempDateType = $dateType;
        if ($dateType == 5) {
            // 주간 날짜 범위 구하기
            $temp = Utility::getInstance()->getWeekDatePeriod($date);
            $dateType = 1;
        } else {
            // 마감일
            $temp = Utility::getInstance()->getDateFromDueday($dueDay, $date);
        }

        if ($isUseNextDate === true) {
            // 대시보드에서 검색 날짜 설정
            $tempStDt = ($lDay > $dueDay && isset($temp['next_start']) === true) ? $temp['next_start'] : $temp['start'];
            $tempEdDt = ($lDay > $dueDay && isset($temp['next_end']) === true) ? $temp['next_end'] : $temp['end'];
        }

        if ($isUseNextDate === false) {
            // 대시보드가 아닌 모든 메뉴에서..
            $tempStDt = $temp['start'];
            $tempEdDt = $temp['end'];
        }

        $tempDueDate = $temp['due_date'];

        $query = $this->getQuery($option, $dateType, $date, $tempStDt, $tempEdDt, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
        $d = $command->query($query);

        // 내년 날짜 체크를 위한 변수
        $tempYear = $date;
        if ($dateType === 0) {
            $yearDate = date('Ymd', strtotime($date . '0101'));
            if (strtotime($lToday) <= strtotime($yearDate)) {
                $date = date('Y', strtotime($date));
            }
        }

        if ($dateType == 0 && $date == date('Y')) {
            // 0 == 년
            if ($lDay > $dueDay) {
                $tempStDt = $temp['next_start'];
                $tempEdDt = $temp['next_end'];
            }

            if ($isEndDay === false) {
                $yearDueDay = (int)date('t', strtotime($tempDueDate));
                if ($yearDueDay === 28
                    && $lDay < $dueDay
                    && (date('Ym', strtotime($tempDueDate)) === date('Ym', strtotime($tempStDt)))) {
                    // 마감일이 28일이면서 2월달처럼 경우, 시작일이 다음달로 이월되는경우
                    $tempStDt = date('Ymd', strtotime($tempStDt . '-1 month'));
                } else {
                    $tempStDt = date('Ymd', strtotime($tempStDt . '-1 day'));
                }
            }

            $query = $this->emsQuery->getQuerySelectEfficiencyCurrentYearData($option, $tempStDt, $tempEdDt, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
            $current = $command->query($query);

            if (count($current) > 0) {
                if ($lDay > $dueDay) {
                    // 마감일을 넘을 경우 다음일자로 변경함.
                    $current[0]['ym'] = $tmpNextDate;
                } else {
                    $current[0]['ym'] = date('Ym', strtotime($tempEdDt));
                }
                $d[] = $current[0];
            }
            $date = $tempYear;
        }

        $dateType = $tempDateType;
        $d = $this->rearrangeData($complexCodePk, $option, $d, $date, $dueDay, $dateType, $isUseNextDate);

        $ret = [
            'data' => $d
        ];

        return $ret;
    }

    /**
     * 효율 조회 (날짜~날짜 기준)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $start
     * @param string $end
     * @param array $addOptions
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getEfficiencyDataByRange(Command $command, string $complexCodePk, int $option, int $dateType, string $start, string $end, array $addOptions) : array
    {
        $ret = [];
        $temp = [];

        // 해체할당
        $assigns = $this->getAssignmentFromOptions($addOptions);

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $floorQuery = Utility::getInstance()->makeWhereClause('home', 'home_grp_pk', $assigns['floor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);

        if ((int)$dateType === 2) {
            $startDate = new \DateTime($start);
            $endDate = new \DateTime($end);
            $dates = $startDate->diff($endDate);
            $diffDay = (int)$dates->days;

            if ($diffDay === 0) {
                // 1일 인 경우 0-23시로 검색
                //금일도 일데이터를 시간단위로 생성하기 때문에 일통계를 바라보도록 수정.
                //$dateType = 4;
            }

            if ($diffDay > 0 && $dateType === 2) {
                // 1일 이상인 경우 일로 검색
                $dateType = 1;
            }
        }

        if ($dateType === 3 || $dateType === 4) {
            $date = $start;
        } else {
            $date = $this->getDateByOption($start, $dateType);
        }

        $tmpDates = $this->getModifyPeriod($dateType, $start, $end);

        $query = $this->getQuery($option, $dateType, $date, $tmpDates['start'], $tmpDates['end'], $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
        $d = $command->query($query);

        $dateType = (int)$dateType;
        $d = $this->rearrangeNotDueDayData($complexCodePk, $option, $d, $start, $end, $dateType);

        $ret = [
            'data' => $d,
        ];

        return $ret;
    }

    /**
     * 효율 조회 (bems_home 테이블 기준)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param array $addOptions
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getEfficiencyDataByHome(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions) : array
    {
        $ret = [];
        $currents = [];
        $predicts = [];

        $startDate = '';
        $endDate = '';

        // 해체 할당
        $assigns = $this->getAssignmentFromOptions($addOptions);
        $isUseNextDate = $assigns['is_use_next_date'];
        $pStartDate = $assigns['start_date'];
        $pEndDate = $assigns['end_date'];

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $floorQuery = Utility::getInstance()->makeWhereClause('home', 'home_grp_pk', $assigns['floor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);

        $query = $this->emsQuery->getDueday($option, $complexCodePk);
        $d = $command->query($query);
        $dueDay = $d[0]['closing_day'];

        if ($dateType == 2 && $date == date('Ymd')) {
            $dateType = 4;
        }

        if ($dateType == 5) {
            // 주간 날짜 범위 구하기
            $temp = Utility::getInstance()->getWeekDatePeriod($date);
            $dateType = 1;
        } else {
            // 마감일
            $temp = Utility::getInstance()->getDateFromDueday($dueDay, $date);
        }

        $lDay = date('j');

        if ($isUseNextDate === true) {
            // 대시보드에서 검색 날짜 설정
            $startDate = ($lDay > $dueDay && isset($temp['next_start']) === true) ? $temp['next_start'] : $temp['start'];
            $endDate = ($lDay > $dueDay && isset($temp['next_end']) === true) ? $temp['next_end'] : $temp['end'];
        }

        if ($isUseNextDate === false) {
            // 대시보드가 아닌 모든 메뉴에서..
            $startDate = $temp['start'];
            $endDate = $temp['end'];
        }

        if (empty($pStartDate) === false && empty($pEndDate) === false) {
            // 날짜~날짜 검색 하기위에서  종료일자를 $addOptions 으로 입력받은 값으로 변경
            $startDate = $pStartDate;
            $endDate = $pEndDate;
            $date = date('Ym', strtotime($startDate));
        }

        $query = $this->getQueryByHome($option, $dateType, $date, $startDate, $endDate, $complexQuery, $floorQuery, $roomQuery, $sensorQuery);
        $d = $command->query($query);

        // 현재, 예상 사용량 데이터 포맷화
        $key = $assigns['floor'];
        $d = $this->rearrangeDataByPredict($complexCodePk, $option, $key, $d);
        $currents = $d['current']['data'];

        $ret = [
            'current' => [
                'data' => $currents,
            ],
        ];

        return $ret;
    }

    /**
     * 주기에 따라 날짜 값 조회
     *
     * @param string $date
     * @param int $dateType
     *
     * @return string
     */
    public function getDateByOption(string $date, int $dateType) : string
    {
        $d = $date;

        switch ($dateType)
        {
            case 0:
                //year
                $d = substr($date, 0, 4);
                break;
            case 1:
                //month
                $d = substr($date, 0, 6);
                break;
            case 2:
            case 5:
                //day
                $d = substr($date, 0, 8);
                break;
        }

        return $d;
    }

    /**
     * 시작일과 종료일에 일 추가하기
     *
     * @param int $dateType
     * @param string $start
     * @param string $end
     *
     * @return array
     */
    private function getModifyPeriod(int $dateType, string $start, string $end) : array
    {
        $fcData = [
            'start' => $start,
            'end' => $end
        ];

        switch ($dateType)
        {
            case 5:
                $fcData = [
                    'start' => $start . '01',
                    'end' => $end . '31'
                ];
                break;
            default:
                break;
        }

        return $fcData;
    }
}