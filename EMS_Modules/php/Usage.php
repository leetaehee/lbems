<?php
namespace EMS_Module;

use Module\FileCache;

use Http\SensorManager;
use Http\Command\Command;

/**
 * [사용량 주요 메서드]
 *
 * getEnergyData, getEnergyDataBySensor (검침일 기준 조회)
 * getEnergyDataByRange, getEnergyDataByRangeBtSensor (기간별 조회)
 * getEnergyDataByHome, getEnergyHomeDataBySensor -> getEnergySumData 로 변경 예정  (사용량 합침 조회)
 *
 * [사용량 통합 메서드]
 *
 * getUsageSumData  -> getEnergyDataByHome, getEnergyHomeDataBySensor
 *
 * [참고]
 * - getEnergy_타입_BySensor  테이블 파라미터 통일
 * - 사용량 메서들을 관리 하는 컨트롤러(?) 메서드 검토
 */

/**
 * Class Usage 사용량 | 요금 조회
 */
class Usage
{
    /** @var EmsQuery|null $emsQuery */
    private ?EMSQuery $emsQuery = null;

    /** @var Fee|null $fee */
    private ?Fee $fee = null;

    /** @var array $envData env 정보  */
    private array $envData = [];

    /** @var array $cacheData 캐시 정보 */
    private array $cacheData = [];

    /**
     * Usage constructor.
     */
    public function __construct()
    {
        $envData = Utility::getInstance()->getEnvData();

        $this->emsQuery = new EMSQuery();
        $this->fee = new Fee();
        $this->envData = $envData;

        $this->setCacheData();

    }

    /**
     * Usage destructor.
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
     * 캐시 정보 초기화
     *
     * @return void
     */
    private function setCacheData() : void
    {
        $path = 'usage/';

        $this->cacheData = [
            'getEnergyData' => [
                'current' => (new FileCache('getEnergyData', "{$path}/current"))->cacheLoad(),
                'last' => (new FileCache('getEnergyData', "{$path}/last"))->cacheLoad()
            ],
            'getEnergyDataByRange' =>[
                'current' => (new FileCache('getEnergyDataByRange', "{$path}/current"))->cacheLoad(),
                'last' => (new FileCache('getEnergyDataByRange', "{$path}/last"))->cacheLoad()
            ],
            'getUsageSumData' => [
                'current' => (new FileCache('getUsageSumData', "{$path}/current"))->cacheLoad(),
                'last' => (new FileCache('getUsageSumData', "{$path}/last"))->cacheLoad(),
            ],
        ];
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
     * @param string $dongQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     * @param string $equipmentQuery
     * @param int $receiveTime
     *
     * @return string
     */
    public function getQuery(int $option, int $dateType, string $date, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery, int $receiveTime = 5) : string
    {
        $query = '';

        switch ($dateType) {
            case 0:
                //year
                $query = $this->emsQuery->getQueryEnergyYearData($option, $date, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 1:
                //month
                $query = $this->emsQuery->getQueryEnergyMonthData($option, $startDate, $endDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 2:
                //day
                $query = $this->emsQuery->getQueryEnergyDayData($option, $date, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 3:
                //hour
                $query = $this->emsQuery->getQueryEnergyHourData($option, $date, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery, $receiveTime);
                break;
            case 4:
                //today
                $query = $this->emsQuery->getQueryEnergyCurrentDayData($option, $date, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 5:
                // daily 테이블 - 기간별 검색 '월', 기간별 일 검색, '주' 검색
                $query = $this->emsQuery->getQueryEnergyMonthRangeData($option, $startDate, $endDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 6:
                // 기간별 검색 '일' (meter) - 한달치만 허용 하고, 사용자가 많을 경우 사용 자제 권고 (시스템 느려짐)
                $query = $this->emsQuery->getQueryEnergyMonthMeterData($option, $startDate, $endDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
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
     * @param string $dongQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     * @param string $equipmentQuery
     *
     * @return string
     */
    public function getQueryByHome(int $option, int $dateType, string $date, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $query = '';

        switch ($dateType) {
            case 0:
                // 년 검색
                $query = $this->emsQuery->getQueryEnergyYearDataByHome($option, $date, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 1:
                // 월 검색
                $query = $this->emsQuery->getQueryEnergyMonthDataByHome($option, $startDate, $endDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
                break;
            case 4:
            case 2:
                // 금일
                $query = $this->emsQuery->getQueryEnergyCurrentDayHomeDataBySensorTable($option, $date, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
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

                    $temp[$temp2] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
                }
                break;
            case 3:
                //hour
                $len = count($d);

                if ($len > 0) {
                    $temp = [];
                    $lastVals = [];
                    $day = null;

                    for ($i = 0; $i < $len; $i++) {
                        $sensor = $d[$i]['sensor_sn'];
                        $val_date = $d[$i]['val_date'];
                        $val = $d[$i]['val'];

                        if (array_key_exists($sensor, $lastVals) == false) {
                            $lastVals[$sensor] = $val;
                            continue;
                        }

                        $usage = $val - $lastVals[$sensor];
                        if ($usage < 0) {
                            $usage = 0;
                        }

                        $lastVals[$sensor] = $val;

                        $minute = ((int)(substr($val_date, 10, 2) / 5)) * 5;
                        $minute = Utility::getInstance()->toTwoDigit($minute);

                        if ($day === null) {
                            $day = substr($val_date, 0, 10);
                        }

                        $time = $day . $minute;

                        if (array_key_exists($time, $temp) === false) {
                            $temp[$time] = 0;
                        }

                        $temp[$time] = $temp[$time] + $usage;
                    }

                    $temp = Utility::getInstance()->makeMinuteData($complexCodePk, $customUnit, $temp, $day);
                }
                break;
            case 4:
                //today
                $temp = [];
                $tempDate = date('Ymd', strtotime($date));

                for ($i = 0; $i < 24; $i++) {
                    $temp2 = $tempDate . Utility::getInstance()->toTwoDigit($i);
                    $temp[$temp2] = 0;
                }

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
     * @return array
     *
     * @throws \Exception
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
                        $col = "val_{$i}";
                        $temp2 = $tempDate . Utility::getInstance()->toTwoDigit($i);
                        $val = $d[0][$col];

                        if (empty($val) === true) {
                            $val = 0;
                        }

                        if (empty($val) === false && $val < 0) {
                            $val = 0;
                        }

                        $temp[$temp2] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
                    }
                }
                break;
            case 3:
                //hour
                $len = count($d);

                if ($len > 0) {
                    $temp = [];
                    $lastVals = [];
                    $day = null;

                    for ($i = 0; $i < $len; $i++) {
                        $sensor = $d[$i]['sensor_sn'];
                        $val_date = $d[$i]['val_date'];

                        $val = $d[$i]['val'];
                        if ($val < 0) {
                            $val = 0;
                        }

                        if (array_key_exists($sensor, $lastVals) == false) {
                            $lastVals[$sensor] = $val;
                            continue;
                        }

                        $usage = $val - $lastVals[$sensor];
                        if ($usage < 0) {
                            $usage = 0;
                        }

                        $lastVals[$sensor] = $val;

                        $minute = ((int)(substr($val_date, 10, 2) / 5)) * 5;
                        $minute = Utility::getInstance()->toTwoDigit($minute);

                        if ($day === null) {
                            $day = substr($val_date, 0, 10);
                        }

                        $time = $day . $minute;

                        if (array_key_exists($time, $temp) === false) {
                            $temp[$time] = 0;
                        }

                        $temp[$time] = $temp[$time] + $usage;
                    }

                    $temp = Utility::getInstance()->makeMinuteData($complexCodePk, $customUnit, $temp, $day);
                }
                break;
            case 4:
                //today
                $temp = [];
                $tempDate = $start;

                for ($i = 0; $i < 24; $i++) {
                    $temp2 = $tempDate . Utility::getInstance()->toTwoDigit($i);
                    $temp[$temp2] = 0;
                }

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
            case '5':
                //기간별 월 검색
                $temp = Utility::getInstance()->makeMonthRangeData($complexCodePk, $customUnit, $d, $start, $end, 'val_date', 'val');
                break;
            default:
                # code...
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
     * @param array $predicts
     * @param bool $isStatus
     *
     * @return array $fcData
     */
    private function rearrangeDataByPredict(string $complexCodePk, string $customUnit, string $key, array $currents, array $predicts, bool $isStatus) : array
    {
        $fcData = [];

        $dateCount = count($currents);

        //$keyName = '';
        $keyName = $key;

        if ($dateCount > 1) {
            for ($i = 0; $i < $dateCount; $i++) {
                $currentValue = 0;
                $predictValue = 0;

                //$keyName = $currents[$i]['home_grp_pk'];
                //$keyName = ($key === 'all') ? $key : $keyName;

                $keyName = $key;

                if ($currents[$i]['val'] > 0) {
                    $currentValue = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$currents[$i]['val']);
                }

                if ($predicts[$i]['val'] > 0) {
                    $predictValue = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$predicts[$i]['val']);
                }

                $currentValue = $currentValue < 0 ? 0 : $currentValue;
                $predictValue = $predictValue < 0 ? 0 : $predictValue;

                $fcData['current']['data'][$keyName] += $currentValue;
                $fcData['predict']['data'][$keyName] += $predictValue;

                if ($isStatus === true) {
                    $fcData['current']['data']['low_status'] += Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$currents[$i]['low_status']);
                    $fcData['current']['data']['mid_status'] += Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$currents[$i]['mid_status']);
                    $fcData['current']['data']['max_status'] += Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$currents[$i]['max_status']);
                }
            }
        } else {
            $keyName = empty($keyName) === true ? $key : $keyName;

            $currentValue = (int)$currents[0]['val'];
            $predictValue = (int)$predicts[0]['val'];

            $fcData['current']['data'][$keyName] = $currentValue < 0 ? 0 : Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$currents[0]['val']);
            $fcData['predict']['data'][$keyName] = $predictValue < 0 ? 0 : Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, (int)$predicts[0]['val']);

            if ($isStatus === true) {
                $lowStatus = (int)$currents[0]['low_status'];
                $midStatus = (int)$currents[0]['mid_status'];
                $maxStatus = (int)$currents[0]['max_status'];

                $fcData['current']['data']['low_status'] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $lowStatus);
                $fcData['current']['data']['mid_status'] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $midStatus);
                $fcData['current']['data']['max_status'] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $maxStatus);
            }
        }

        return $fcData;
    }

    /**
     * 층 조건문 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $sensor
     *
     * @return string
     */
    private function getFloorQuery(string $complexCodePk, int $option, string $dong, string $floor, string $room, string $sensor) : string
    {
        $isElectric = false;
        $electricSensor = $this->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);

        if ($electricSensor === $sensor) {
            // 전기 데이터가 맞는지 확인
            $isElectric = true;
        }

        if (is_int($option) === true
            && $option === 0
            && empty($sensor) === false
            && $isElectric === true) {
            // 전기는 전체, 층별, 룸별 센서별로 검색을 해야 함 - 무등산 초기 개발 작업할 때
            // 동별 추가
            if ($dong === 'all' && $floor === 'all' && $room == 'all') {
                $floor = '0M';
            } else if (($dong !== 'all' && $floor == 'all')
                || ($floor !== 'all' && $room === 'all')) {
                $floor = 'ALL';
            }
        }

        if (is_int($option) === true
            && $option === 0
            && $floor === 'all'
            && empty($sensor) === true) {
            return Utility::getInstance()->makeWhereArrayClause('home', 'home_grp_pk', ['0M', 'ALL'], 'NOT IN');
        }

        return Utility::getInstance()->makeWhereClause('home', 'home_grp_pk', $floor);
    }

    /**
     * 커스텀 단위 정보 조회
     *
     * @param string $complexCodePk
     * @param string $energyName
     *
     *
     * @return string
     */
    private function getCustomUnit(string $complexCodePk, string $energyName): string
    {
        $fcUnit = '';

        $customUnits = $this->getSensorManager($complexCodePk)->getCustomUnit();
        if (empty($customUnits[$energyName]) === false) {
            $fcUnit = $customUnits[$energyName];
        }

        return $fcUnit;
    }

    /**
     * 커스텀 단위에 대한 option 번호 조회
     *
     * @param string $customUnit
     * @param int $option
     *
     * @return int
     */
    private function getCustomOption(string $customUnit, int $option): int
    {
        $fcOption = $option;

        if (empty($customUnit) === true) {
            return $fcOption;
        }

        switch ($customUnit) {
            case 'm3' :
                $fcOption = 1;
                break;
        }

        return $fcOption;
    }

    /**
     * 태양광 inout 조건 반환
     *
     * @param int $option
     * @param string $solarType
     *
     * @return string
     */
    private function getSolarQuery(int $option, string $solarType) : string
    {
        $fcSolarQuery = '';

        if ($option !== 11) {
            return $fcSolarQuery;
        }

        if (empty($solarType) === true) {
            return $fcSolarQuery;
        }

        $fcSolarQuery = Utility::getInstance()->makeWhereClause('sensor', 'inout', $solarType);
        if ($solarType === 'O') {
            $fcSolarQuery = Utility::getInstance()->makeWhereArrayClause('sensor', 'inout', ['B', $solarType]);
        }

        return $fcSolarQuery;
    }

    /**
     * bems_sensor_equipment 테이블 참조 시  type 조건 반환
     *
     * @param int $option
     * @param string $energyName
     *
     * @return string
     */
    private function getEquipmentTypeQuery(int $option, string $energyName) : string
    {
        $fcEquipmentTypeQuery = '';

        if ($option !== 14) {
            return $fcEquipmentTypeQuery;
        }

        if (in_array($energyName, Config::EQUIPMENT_TYPE_ITEMS) === false) {
            return $fcEquipmentTypeQuery;
        }

        $fcEquipmentTypeQuery = Utility::getInstance()->makeWhereClause('sensor', 'type', $energyName);

        return $fcEquipmentTypeQuery;
    }

    /**
     * 금일 데이터를 시간대별로 추출
     *
     * @param Command $command
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getTodayHourData(Command $command, int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : array
    {
        $fcData = [];
        $currentHour = date('H'); // 현재 시

        /*
         * 시간대별로 구하는 것은 아래와 같이 예외 상황이 많음
         *
         * 시간 사용량 = 현재 시간 누적값 - 이전 시간 누적값
         * 만약 이전 시간대가 없는 경우
         * 시간 사용량 = 금일 최대 누적값 - 현재 시간 누적값
         *
         * 만약 오늘 데이터가 없다면, 오늘, 2일전 등등..
         * 모든 사항을 고려 할 경우 시스템 부하 생김
         */
        for ($fcHour = 0; $fcHour <= $currentHour; $fcHour++) {
            if ($fcHour < 10) {
                $fcHour = '0' . $fcHour;
            }

            $currentDateHour = $date . $fcHour; // 현재 시간 = 오늘날짜 + 시
            $tempDateTime = $currentDateHour . '0000';
            $previousDateHour = date('YmdH', strtotime($tempDateTime . '-1 hours')); // 이전시간

            $rTodayHourQ = $this->emsQuery->getQueryEnergyCurrentDayByPreviousHourData($option, $previousDateHour, $currentDateHour, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $rHourData = $command->query($rTodayHourQ);

            $fcData[] = [
                'val_date' => $rHourData[0]['val_date'],
                'val' => $rHourData[0]['val'],
                'time' => $rHourData[0]['day'],
            ];
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
    private function getAssignmentFromOptions(array $addOptions): array
    {
        $fcData = [];

        $dong = isset($addOptions['dong']) === true ? $addOptions['dong'] : 'all';
        $floor = isset($addOptions['floor']) === true ? $addOptions['floor'] : 'all';
        $room = isset($addOptions['room']) === true ? $addOptions['room'] : 'all';
        $sensor = isset($addOptions['sensor']) === true ? $addOptions['sensor'] : '';
        $isArea = isset($addOptions['is_area']) === true ? $addOptions['is_area'] : false;
        $isUseNextDate = isset($addOptions['is_use_next_date']) === true ? $addOptions['is_use_next_date'] : true;
        $solarType = isset($addOptions['solar_type']) === true ? $addOptions['solar_type'] : '';
        $energyName = isset($addOptions['energy_name']) === true ? $addOptions['energy_name'] : '';
        $startDate = isset($addOptions['start_date']) === true ? $addOptions['start_date'] : '';
        $endDate = isset($addOptions['end_date']) === true ? $addOptions['end_date'] : '';
        $isCache = isset($addOptions['is_cache']) === true ? $addOptions['is_cache'] : true;
        $isStatus = isset($addOptions['is_status']) === true ? $addOptions['is_status'] : false;
        $recursive = isset($addOptions['recursive']) === true ? $addOptions['recursive'] : false;
        $separatedSensors = isset($addOptions['separated_sensors']) === true ? $addOptions['separated_sensors'] : [];
        $timeType = isset($addOptions['time_type']) === true ? $addOptions['time_type'] : 'current';

        $fcData = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'sensor' => $sensor,
            'is_area' => $isArea,
            'is_use_next_date' => $isUseNextDate,
            'solar_type' => $solarType,
            'energy_name' => $energyName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_cache' => $isCache,
            'is_status' => $isStatus,
            'recursive' => $recursive,
            'separated_sensors' => $separatedSensors,
            'time_type' => $timeType,
        ];

        return $fcData;
    }

    /**
     * 재귀함수를 이요하여 센서번호별로 사용량 합치기
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $startDate
     * @param string $endDate
     * @param bool $isArea
     * @param string $functionName
     * @param array $addOptions
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getSumData(Command $command, string $complexCodePk, int $option, int $dateType, string $startDate, string $endDate, bool $isArea, string $functionName, array $addOptions) : array
    {
        $fcData = [];
        $fcAddOptions = $addOptions;

        $energyName = $fcAddOptions['energy_name'];

        $sensorData = [];

        if (empty($functionName) === true) {
            return $fcData;
        }

        if (in_array($option, [0, 11, 12, 14]) === true) {
            /*
             * 센서번호로 검색을 해야하는 경우는 해당 함수 타지 않음
             * - 전기는 각 층별 센서번호가 존재함
             * - 태양광도 마찬가지 (inout = 'I' or 'O')
             * - fems도 센서번호로 검색하므로 위 번호는 그대로..
             * - bems 는 공용부만..
             */
            return $fcData;
        }

        if ($dateType === 3) {
            // 5분단위 데이터 출력 시
            $rSensorQ = $this->emsQuery->getQuerySensorData($complexCodePk, true, $option);
            $sensorData = $command->query($rSensorQ);
        }

        if (count($sensorData) === 0) {
            return $fcData;
        }

        // 재귀함수 적용
        $fcAddOptions['recursive'] = true;

        foreach ($sensorData AS $i => $values) {
            switch ($functionName) {
                case 'getEnergyData' :
                    $fcAddOptions['sensor'] = $values['sensor'];
                    $fcData[] = $this->getEnergyData($command, $complexCodePk, $option, $dateType, $startDate, $fcAddOptions);
                    break;
                /*
                    case 'getEnergyDataByRange' :
                        break;
                    case 'getEnergyDataByHome' :
                        break;
                */
            }
        }

        $resultCount = count($fcData);

        if ($resultCount > 1) {
            $tempData = Utility::getInstance()->setArraySumByMerge($fcData, 'data');
            $tempPrice = $this->setPrice($tempData['data'], $option, $complexCodePk, $energyName, $startDate, $command, $dateType, $isArea);

            $fcData = [
                'data' => $tempData['data'],
                'price' => $tempPrice,
            ];
        }

        if ($resultCount === 1) {
            $fcData = [
                'data' => $fcData[0]['data'],
                'price' => $fcData[0]['price'],
            ];
        }

        return $fcData;
    }

    /**
     * 캐시 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $energyName
     * @param string $function
     * @param string $timeType
     * @param array $options
     *
     * @return array
     */
    private function getCacheData(string $complexCodePk, int $dateType, string $energyName, string $function, string $timeType, array $options = []) : array
    {
        $fcData = [];
        $envData = $this->envData;

        if ($options['is_cache'] === false) {
            return $fcData;
        }

        if (in_array($dateType, [0, 1, 2]) === false) {
            // 우선은 금일,금월,금년만 적용되고 나머지는 캐시 안탐
            return $fcData;
        }

        $cacheFilePath = $envData['CACHE_FILE_PATH'];
        if (empty($cacheFilePath) === true) {
            return $fcData;
        }

        $siteType = $envData['SITE_TYPE'];
        if ($siteType === 'fems') {
            // fems는 전기 테이블만 사용함
            $energyName = 'electric';
        }

        $cacheData = $this->cacheData;

        // 캐시읽기
        $fcData = $cacheData[$function][$timeType][$dateType][$complexCodePk][$energyName];
        $fcData = [];
        if (count($fcData) === 0) {
            return []; // 캐시 없으면 빈 배열 반환
        }

        return $fcData;
    }

    /**
     * 사용량 | 요금 조회 (마감일 기준)
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
    public function getEnergyData(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions) : array
    {
        $ret = [];

        $receiveTime = isset(Config::RAW_DATA_RECEIVE_PERIODS[$complexCodePk]) === false ? 5 : Config::RAW_DATA_RECEIVE_PERIODS[$complexCodePk];

        $lDay = date('j');
        $lToday = date('Ymd');

        $functionName = __FUNCTION__;

        // 해체할당
        $assigns = $this->getAssignmentFromOptions($addOptions);
        $isUseNextDate = $assigns['is_use_next_date'];
        $isArea = $assigns['is_area'];
        $energyName = $assigns['energy_name'];
        $dong = $assigns['dong'];
        $floor = $assigns['floor'];
        $room = $assigns['room'];
        $recursive = $assigns['recursive'];
        $timeType = $assigns['time_type'];
        $isCache = $assigns['is_cache'];

        $customUnit = $this->getCustomUnit($complexCodePk, $energyName);

        // 캐시 조회
        $cacheOptions = [
            'is_cache' => $isCache,
        ];

        // 캐시안함.. 필요하면 할 것
        $cacheData = $this->getCacheData($complexCodePk, $dateType, $energyName, $functionName, $timeType, $cacheOptions);
        if (count($cacheData) > 0) {
            return $cacheData;
        }

        if ($floor === 'all' && $room === 'all' && $dateType === 3 && $recursive === false) {
            // 5분 데이터 조회 시 센서별로 검색 후 합치도록 개선 (성능개선)
            $ret = $this->getSumData($command, $complexCodePk, $option, $dateType, $date, $date, $isArea, $functionName, $addOptions);
            if (count($ret) > 0) {
                return $ret;
            }
        }

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $dongQuery = Utility::getInstance()->makeWhereClause('home', 'home_dong_pk', $dong);
        $floorQuery = $this->getFloorQuery($complexCodePk, $option, $dong, $floor, $room, $assigns['sensor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $room);
        $solarQuery = $this->getSolarQuery($option, $assigns['solar_type']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);
        $equipmentQuery = $this->getEquipmentTypeQuery($option, $energyName);

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

        /*
            if ($dateType === 4) {
                // 주기가 오늘이면서, 시간별로 추출하기 위해서는 이전시간 '현재시간 사용량 - 최대사용량'을 해야하기 때문에 분기 처리함
                // 추후 시간 통계 테이블이 있다면 이 조건은 필요가 없음
                //$d = $this->getTodayHourData($command, $option, $date, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            }
        */

        $tempDueDate = $temp['due_date'];

        $query = $this->getQuery($option, $dateType, $date, $tempStDt, $tempEdDt, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery, $receiveTime);
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

            $previousMonth = date('Ym', strtotime($tempStDt));

            /**
             * 금월 데이터 조회- 미터 테이블을 이용하지 않고 월통계와 센서테이블로 튜닝함
             * 사용량 계산식 = 전월 - 현재 사용량
             * (성능 개선으로 더 이상 사용하지 않으나 쿼리는 삭제 하지 않음- 2020-01-14)
             */
            //$query = $this->emsQuery->getQueryEnergyCurrentYearData($option, $tempStDt, $tempEdDt, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $query = $this->emsQuery->getQueryEnergyCurrentYearDataBySensorTable($option, $previousMonth, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
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
        } else if ($dateType == 1 && ($date === date('Ym') || $date > date('Ym'))) {
            // 1 == 월
            $today = date('Ymd');
            $previousDate = date('Ymd', strtotime($today . '-1 day'));

            /**
             * 금일 데이터 조회- 미터 테이블을 이용하지 않고 일통계와 센서테이블로 튜닝함
             * 사용량 계산식 = 전일 - 현재 사용량
             * (성능 개선으로 더 이상 사용하지 않으나 쿼리는 삭제 하지 않음- 2020-01-15)
             */
            //$query = $this->emsQuery->getQueryEnergyCurrentMonthData($option, $today, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $query = $this->emsQuery->getQueryEnergyCurrentMonthDataBySensorTable($option, $previousDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
            $current = $command->query($query);

            if (count($current) > 0) {
                $current[0]['val_date'] = $today;
                $d[] = $current[0];
            }
        }

        if ($dateType == 1 && $date == date('Ym')) {	}

        $dateType = $tempDateType;
        $d = $this->rearrangeData($complexCodePk, $customUnit, $d, $date, $dueDay, $dateType, $isUseNextDate, $customUnit);

        if ($isArea === true) {
            $areas = $this->setAreaValue($command, $complexCodePk, $option, $energyName, $dateType, $date, $d);

            $d = $areas['data'];
            $price = $areas['price'];
        } else {
            $price = $this->setPrice($d, $option, $complexCodePk, $energyName, $date, $command, $dateType, $isArea);
            $option = $this->getCustomOption($customUnit, $option);

            $d = Utility::getInstance()->setUnit($option, $d);
        }

        $ret = [
            'data' => $d,
            'price' => $price,
        ];

        return $ret;
    }

    /**
     * 사용량 | 요금 조회 (마감일 기준, 센서 세부 조회)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param array $addOptions
     * @param array $sensors
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getEnergyDataBySensor(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions, array $sensors) : array
    {
        $fcData = [];
        $fcNows = [];

        $assigns = $this->getAssignmentFromOptions($addOptions);

        $userFloor = $assigns['floor'];
        $energyName = $assigns['energy_name'];
        $isCache = $assigns['is_cache'];

        if (count($sensors) === 0) {
            return $this->getEnergyData($command, $complexCodePk, $option, $dateType, $date, $addOptions);
        }

        if ($userFloor !== 'all') {
            // 개별층 조회
            $sensors = $sensors[$userFloor];

            if (is_null($sensors) === true) {
                return $fcData;
            }

            foreach ($sensors as $sensor) {
                $addOptions['floor'] = $userFloor;
                $addOptions['sensor'] = $sensor;

                $fcNows[] = $this->getEnergyData($command, $complexCodePk, $option, $dateType, $date, $addOptions);
            }
        } else {
            // 모든 층 조회
            foreach ($sensors AS $floor => $values) {
                if (empty($values) === true) {
                    continue;
                }

                foreach ($values AS $sensor) {
                    $addOptions['floor'] = $floor;
                    $addOptions['sensor'] = $sensor;

                    $fcNows[] = $this->getEnergyData($command, $complexCodePk, $option, $dateType, $date, $addOptions);
                }
            }
        }

        $fcDataCount = count($fcNows);

        if ($fcDataCount > 1) {
            $fcNows = Utility::getInstance()->setArraySumByMerge($fcNows, 'data');
            $fcPrices = $this->setPrice($fcNows['data'], $option, $complexCodePk, $energyName, $date, $command, $dateType,false);

            $fcData = [
                'data' => $fcNows['data'],
                'price' => $fcPrices,
            ];
        }

        if ($fcDataCount === 1) {
            $fcData = [
                'data' => $fcNows[0]['data'],
                'price' => $fcNows[0]['price'],
            ];
        }

        return $fcData;
    }

    /**
     * 사용량 | 요금 조회 (bems_home 테이블 기준)
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
    private function getEnergyDataByHome(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions) : array
    {
        $ret = [];
        $currents = [];
        $predicts = [];

        $startDate = '';
        $endDate = '';

        $functionName = 'getUsageSumData';

        // 해체 할당
        $assigns = $this->getAssignmentFromOptions($addOptions);
        $isUseNextDate = $assigns['is_use_next_date'];
        $isArea = $assigns['is_area'];
        $pStartDate = $assigns['start_date'];
        $pEndDate = $assigns['end_date'];
        $energyName = $assigns['energy_name'];
        $isStatus = $assigns['is_status'];
        $dong = $assigns['dong'];
        $timeType = $assigns['time_type'];
        $isCache = $assigns['is_cache'];

        $customUnit = $this->getCustomUnit($complexCodePk, $energyName);

        // 캐시 조회
        $cacheOptions = [
            'is_cache' => $isCache,
        ];

        $cacheData = $this->getCacheData($complexCodePk, $dateType, $energyName, $functionName, $timeType, $cacheOptions);
        if (count($cacheData) > 0) {
            return $cacheData;
        }

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $dongQuery = Utility::getInstance()->makeWhereClause('home', 'home_dong_pk', $dong);
        $floorQuery = $this->getFloorQuery($complexCodePk, $option, $dong, $assigns['floor'], $assigns['room'], $assigns['sensor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
        $solarQuery = $this->getSolarQuery($option, $assigns['solar_type']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);
        $equipmentQuery = $this->getEquipmentTypeQuery($option, $energyName);

        $query = $this->emsQuery->getDueday($option, $complexCodePk);
        $d = $command->query($query);
        $dueDay = $d[0]['closing_day'];

        $isEndDay = false;
        if ($dueDay === 99) {
            $isEndDay = true;
        }

        $dueDate = date('Ym') . $dueDay;
        $tmpNextDate = date('Ym', strtotime('+1 months', strtotime($dueDate)));

        $tempDateType = $dateType;

        if ($dateType == 2 && $date == date('Ymd')) {
            //$dateType = 4;
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
        $lToday = date('Ymd');
        $tempDueDate = $temp['due_date'];

        $startDate = '';
        $endDate = '';

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

        if ($dateType === 1
            && empty($pStartDate) === false && empty($pEndDate) === false) {
            // 날짜~날짜 검색 하기위에서 종료일자를 $addOptions 으로 입력받은 값으로 변경
            $startDate = $pStartDate;
            $endDate = $pEndDate;
            $date = date('Ym', strtotime($startDate));

            if ($endDate < $lToday) {
                // 과거 월을 조회 시에는 금월 조회할 필요가 없기 때문에 lToday 값을 변경한다.
                $lToday = $endDate;
            }
        }

        $query = $this->getQueryByHome($option, $dateType, $date, $startDate, $endDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
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
            // 금년
            if ($lDay > $dueDay) {
                $startDate = $temp['next_start'];
                $endDate = $temp['next_end'];
            }

            $preStartDate = $startDate;

            if ($isEndDay === false) {
                $yearDueDay = (int)date('t', strtotime($tempDueDate));
                if ($yearDueDay === 28
                    && $lDay < $dueDay
                    && (date('Ym', strtotime($tempDueDate)) === date('Ym', strtotime($startDate)))) {
                    // 마감일이 28일이면서 2월달처럼 경우, 시작일이 다음달로 이월되는경우
                    $preStartDate = date('Ymd', strtotime($preStartDate . '-1 month'));
                } else {
                    $preStartDate = date('Ymd', strtotime($preStartDate . '-1 day'));
                }
            }

            $previousMonth = date('Ym', strtotime($preStartDate));

            /**
             * 금월 데이터 조회- 미터 테이블을 이용하지 않고 월통계와 센서테이블로 튜닝함
             * 사용량 계산식 = 전월 - 현재 사용량
             * (성능 개선으로 더 이상 사용하지 않으나 쿼리는 삭제 하지 않음- 2020-01-14)
             */
            // 아래 함수는 20210329 ~ 20210428 시 검색 오류가 있으므로 사용하게 될 경우 반드시 수정할 것
            //$query = $this->emsQuery->getQueryEnergyCurrentYearDataByHome($option, $startDate, $endDate, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $query = $this->emsQuery->getQueryEnergyCurrentYearHomeDataBySensorTable($option, $previousMonth, $startDate, $endDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
            $current = $command->query($query);

            if (count($current) > 0) {
                $d = Utility::getInstance()->addKeyArray($d, $current, $isStatus);
            }

            $date = $tempYear;
        }

        if ($dateType === 1 && ($date === date('Ym') || $date > date('Ym'))) {
            // 금월
            $today = $lToday;
            $previousDate = date('Ymd', strtotime($today . '-1 day'));

            /**
             * 금일 데이터 조회- 미터 테이블을 이용하지 않고 일통계와 센서테이블로 튜닝함
             * 사용량 계산식 = 전일 - 현재 사용량
             * (성능 개선으로 더 이상 사용하지 않으나 쿼리는 삭제 하지 않음- 2020-01-15)
             */
            //$query = $this->emsQuery->getQueryEnergyCurrentMonthDataByHome($option, $today, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $query = $this->emsQuery->getQueryEnergyCurrentHomeMonthDataBySensorTable($option, $previousDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
            $current = $command->query($query);

            if (count($current) > 0) {
                $d = Utility::getInstance()->addKeyArray($d, $current, $isStatus);
            }
        }

        // 예측 데이터 조회
        $tempAddOptions = [
            'floor' => $assigns['floor'],
            'room' => $assigns['room'],
            'sensor' => $assigns['sensor'],
            'solar_type' => $assigns['solar_type'],
        ];
        $predicts = $this->getPredictData($command, $option, $tempDateType, $complexCodePk, $tempAddOptions);

        // 현재, 예상 사용량 데이터 포맷화
        $dateType = $tempDateType;
        $key = $assigns['floor'];
        $d = $this->rearrangeDataByPredict($complexCodePk, $customUnit, $key, $d, $predicts, $isStatus);

        $currents = $d['current']['data'];
        $predicts = $d['predict']['data'];

        // 요금 계산
        if ($isArea == false) {
            $currentPrices = $this->setPrice($currents, $option, $complexCodePk, $energyName, $date, $command, $dateType, $isArea, false);
            $predictPrices = $this->setPrice($predicts, $option, $complexCodePk, $energyName, $date, $command, $dateType, $isArea, false);
        }

        if (count($d) > 0) {
            $option = $this->getCustomOption($customUnit, $option);

            $currents = Utility::getInstance()->setUnit($option, $currents);
            $predicts = Utility::getInstance()->setUnit($option, $predicts);
        }

        $ret = [
            'current' => [
                'data' => $currents,
                'price' => $currentPrices,
            ],
            'predict' => [
                'data' => $predicts,
                'price' => $predictPrices,
            ],
        ];

        return $ret;
    }

    /**
     * 사용량 | 요금 조회 (bems_home 테이블 기준, 센서 세부 조회)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param array $addOptions
     * @param array $sensors
     * @param string $selectedKey
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
    private function getEnergyHomeDataBySensor(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions, array $sensors, string $selectedKey = 'current') : array
    {
        $fcData = [
            'current' => [
                'data' => [],
                'price' => []
            ],
            'predict' => [
                'data' => [],
                'price' => []
            ],
        ];

        $fcNows = [];

        $assigns = $this->getAssignmentFromOptions($addOptions);

        $userFloor = $assigns['floor'];
        $energyName = $assigns['energy_name'];
        $isArea = $assigns['is_area'];

        if ($userFloor !== 'all') {
            // 개별 층 조회
            $sensors = $sensors[$userFloor];

            if (is_null($sensors) === true) {
                return $fcData;
            }

            foreach ($sensors AS $sensor) {
                $addOptions['floor'] = $userFloor;
                $addOptions['sensor'] = $sensor;

                $temps = $this->getEnergyDataByHome($command, $complexCodePk, $option, $dateType, $date, $addOptions);
                $fcNows[] = $temps[$selectedKey];
            }
        } else {
            // 모든 층 조회
            foreach ($sensors AS $floor => $values) {
                if (empty($values) === true) {
                    continue;
                }

                foreach ($values AS $sensor) {
                    $addOptions['floor'] = $floor;
                    $addOptions['sensor'] = $sensor;

                    $temps = $this->getEnergyDataByHome($command, $complexCodePk, $option, $dateType, $date, $addOptions);
                    $fcNows[] = $temps[$selectedKey];
                }
            }
        }

        $fcDataCount = count($fcNows);

        if ($fcDataCount > 1) {
            $fcNows = Utility::getInstance()->setArraySumByMerge($fcNows, 'data');
            $fcPrices = $this->setPrice($fcNows['data'], $option, $complexCodePk, $energyName, $date, $command, $dateType, $isArea, false);

            $fcData['current'] = [
                'data' => $fcNows['data'],
                'price' => $fcPrices,
            ];
        }

        if ($fcDataCount === 1) {
            $fcData['current'] = [
                'data' => $fcNows[0]['data'],
                'price' => $fcNows[0]['price'],
            ];
        }

        return $fcData;
    }

    /**
     * 사용량 | 요금 조회 (날짜~날짜 기준)
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
    public function getEnergyDataByRange(Command $command, string $complexCodePk, int $option, int $dateType, string $start, string $end, array $addOptions) : array
    {
        $ret = [];
        $temp = [];

        $lToday = date('Ymd');

        $receiveTime = isset(Config::RAW_DATA_RECEIVE_PERIODS[$complexCodePk]) === false ? 5 : Config::RAW_DATA_RECEIVE_PERIODS[$complexCodePk];

        // 해체할당
        $assigns = $this->getAssignmentFromOptions($addOptions);
        $isArea = $assigns['is_area'];
        $energyName = $assigns['energy_name'];
        $aFloor = $assigns['floor'];
        $dong = $assigns['dong'];
        $timeType = $assigns['time_type'];
        $isCache = $assigns['is_cache'];

        $functionName = __FUNCTION__;

        $customUnit = $this->getCustomUnit($complexCodePk, $energyName);

        // 캐시 조회
        $cacheOptions = [
            'is_cache' => $isCache,
        ];

        $cacheData = $this->getCacheData($complexCodePk, $dateType, $energyName, $functionName, $timeType, $cacheOptions);
        if (count($cacheData) > 0) {
            return $cacheData;
        }

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $dongQuery = Utility::getInstance()->makeWhereClause('home', 'home_dong_pk', $dong);
        $floorQuery = $this->getFloorQuery($complexCodePk, $option, $dong, $assigns['floor'], $assigns['room'], $assigns['sensor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
        $solarQuery = $this->getSolarQuery($option, $assigns['solar_type']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);
        $equipmentQuery = $this->getEquipmentTypeQuery($option, $energyName);

        if ($dateType === 2) {
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

        /*
            if ($dateType === 4) {
                // 주기가 오늘이면서, 시간별로 추출하기 위해서는 이전시간 '현재시간 사용량 - 최대사용량'을 해야하기 때문에 분기 처리함
                // 추후 시간 통계 테이블이 있다면 이 조건은 필요가 없음
                //$d = $this->getTodayHourData($command, $option, $date, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            }
        */

        $query = $this->getQuery($option, $dateType, $date, $tmpDates['start'], $tmpDates['end'], $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery, $receiveTime);
        $d = $command->query($query);

        $dateType = (int)$dateType;

        if ($dateType === 0 && $date === date('Y')) {
            // 년- 현재년도 조회
            /**
             * 월 통계 테이블에 데이터가 들어갈 때 마감일이 종속 되어 있기 때문에 캘린더를 통해 검색 하는 경우
             * 월 통계 테이블과 센서 테이블을 이용해서 하는 튜닝은 불가능 (원인: 1-31일)
             * 마감일 때문에 getEnergyData() 함수 사용함. 문제가 발생 할 경우 아래 주석 제거
             */
            // 금월 데이터를 가져오기 위한 조회 조건 조회
            /*
            $todayData = $this->getModifyDailyPeriod($dateType);

            $query = $this->emsQuery->getQueryEnergyCurrentYearData($option, $todayData['start'], $todayData['end'], $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $current = $command->query($query);

            $lastIndex = count($d) === 0 ? 0 : count($d) - 1;

            if (count($current) > 0) {
                if ($lDay < $endDay) {
                    $d[$lastIndex] = $current[0];
                }
            }
            */
        }

        if ($dateType === 5 && $end >= date('Ym')) {
            // 월- 현재 월까지 조회 했을 때
            $todayData = $this->getModifyDailyPeriod($dateType);
            $previousDate = date('Ymd', strtotime($todayData['start'] . '-1 day'));

            /**
             * 금일 데이터 조회- 미터 테이블을 이용하지 않고 일통계와 센서테이블로 튜닝함
             * 사용량 계산식 = 전일 - 현재 사용량
             * (성능 개선으로 더 이상 사용하지 않으나 쿼리는 삭제 하지 않음- 2020-01-15)
             */
            //$query = $this->emsQuery->getQueryEnergyCurrentMonthData($option, $todayData['start'], $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $query = $this->emsQuery->getQueryEnergyCurrentMonthDataBySensorTable($option, $previousDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
            $current = $command->query($query);

            if (count($current) > 0) {
                $val = $current[0]['val'];
                $lastIndex = count($d)-1;
                $d[$lastIndex]['val'] += $val;
            }
        }

        if ($dateType === 1 && $end >= $lToday){
            // 일- 현재 월까지 조회 했을 때
            $today = date('Ymd');

            $todayData = $this->getModifyDailyPeriod($dateType);
            $previousDate = date('Ymd', strtotime($todayData['start'] . '-1 day'));

            /**
             * 금일 데이터 조회- 미터 테이블을 이용하지 않고 일통계와 센서테이블로 튜닝함
             * 사용량 계산식 = 전일 - 현재 사용량
             * (성능 개선으로 더 이상 사용하지 않으나 쿼리는 삭제 하지 않음- 2020-01-15)
             */
            //$query = $this->emsQuery->getQueryEnergyCurrentMonthData($option, $todays['start'], $complexQuery,  $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
            $query = $this->emsQuery->getQueryEnergyCurrentMonthDataBySensorTable($option, $previousDate, $complexQuery, $dongQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery, $equipmentQuery);
            $current = $command->query($query);

            if (count($current) > 0) {
                $current[0]['val_date'] = $today;
                $d[] = $current[0];
            }
        }

        $d = $this->rearrangeNotDueDayData($complexCodePk, $option, $d, $start, $end, $dateType);

        if ($isArea === true) {
            $areas = $this->setAreaValue($command, $complexCodePk, $option, $energyName, $dateType, $date, $d);

            $d = $areas['data'];
            $price = $areas['price'];
        } else {
            $price = $this->setPrice($d, $option, $complexCodePk, $energyName, $date, $command, $dateType, $isArea);

            $option = $this->getCustomOption($customUnit, $option);
            $d = Utility::getInstance()->setUnit($option, $d);
        }

        $ret = [
            'data' => $d,
            'price' => $price,
        ];

        return $ret;
    }

    /**
     * 사용량 | 요금 조회 (날짜~날짜 기준, 센서 세부 조회)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $startDate
     * @param string $endDate
     * @param array $addOptions
     * @param array $sensors
     *
     * @return array $fcData
     *
     * @throws \Exception
     */
    public function getEnergyDataByRangeBySensor(Command $command, string $complexCodePk, int $option, int $dateType, string $startDate, string $endDate, array $addOptions, array $sensors) : array
    {
        $fcData = [];
        $fcNows = [];

        $assigns = $this->getAssignmentFromOptions($addOptions);

        $userFloor = $assigns['floor'];
        $energyName = $assigns['energy_name'];
        $isArea = $assigns['is_area'];
        $isCache = $assigns['is_cache'];

        if (count($sensors) === 0) {
            return $this->getEnergyDataByRange($command, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
        }

        if ($userFloor !== 'all') {
            // 개별층 조회
            $sensors = $sensors[$userFloor];

            if (is_null($sensors) === true) {
                return $fcData;
            }

            foreach ($sensors as $sensor) {
                $addOptions['floor'] = $userFloor;
                $addOptions['sensor'] = $sensor;
                $fcNows[] = $this->getEnergyDataByRange($command, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
            }
        } else {
            // 모든 층 조회
            foreach ($sensors AS $floor => $values) {
                if (empty($values) === true) {
                    continue;
                }

                foreach ($values AS $sensor) {
                    $addOptions['floor'] = $floor;
                    $addOptions['sensor'] = $sensor;
                    $fcNows[] = $this->getEnergyDataByRange($command, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
                }
            }
        }

        $fcDataCount = count($fcNows);

        if ($fcDataCount > 1) {
            $fcNows = Utility::getInstance()->setArraySumByMerge($fcNows, 'data');

            if ($dateType === 3 || $dateType === 4) {
                $date = $startDate;
            } else {
                $date = $this->getDateByOption($startDate, $dateType);
            }

            $fcPrices = $this->setPrice($fcNows['data'], $option, $complexCodePk, $energyName, $date, $command, $dateType, $isArea);

            $fcData = [
                'data' => $fcNows['data'],
                'price' => $fcPrices,
            ];
        }

        if ($fcDataCount === 1) {
            $fcData = [
                'data' => $fcNows[0]['data'],
                'price' => $fcNows[0]['price'],
            ];
        }

        return $fcData;
    }

    /**
     * 예측 데이터 조회
     *
     * @param Command $command
     * @param int $option
     * @param int $dateType
     * @param string $complexCodePk
     * @param array $addOptions
     * @param bool $isUnitTransfer 컨트롤러에서 개별적으로 사용할 경우 사용 (단위변환 Wh->kWh)
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getPredictData(Command $command, int $option, int $dateType, string $complexCodePk, array $addOptions, bool $isUnitTransfer = false) : array
    {
        // 해체 할당
        $assigns = $this->getAssignmentFromOptions($addOptions);

        $complexQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);
        $floorQuery = $this->getFloorQuery($complexCodePk, $option, $assigns['dong'], $assigns['floor'], $assigns['room'], $assigns['sensor']);
        $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
        $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $assigns['sensor']);
        $solarQuery = Utility::getInstance()->makeWhereClause('sensor', 'inout', $assigns['solar_type']);

        // 예측은 bems_stat_daily_ 테이블에서 하루 전 일자를 조회 함.
        $preDay = date('Ymd', strtotime(date('Ymd') . '-1 day'));
        $predictColumn = Config::PREDICT_COLUMN_NAMES[$dateType];
        if (empty($predictColumn) === true) {
            $predictColumn = 'predict_day';
        }

        $query = $this->emsQuery->getPredictDataBySensor($option, $predictColumn, $preDay, $complexQuery, $floorQuery, $roomQuery, $sensorQuery, $solarQuery);
        $predicts = $command->query($query);

        $column = Config::COLUMN_NAMES[$option];
        $divisor = Config::DIVISOR_VALUES[$option];

        if ($isUnitTransfer === true && $column === 'total_wh') {
            // 전기일경우 1000으로 나눈다.
            $predicts[0]['val'] = $predicts[0]['val']/$divisor;
        }

        return $predicts;
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

    /**
     * 년, 월 검색시에는 금일 데이터 추출 할 수 있도록 날짜 필요 (보고서 페이지에 해당됨)
     *
     * @param int $dateType
     *
     * @return array
     *
     */
    private function getModifyDailyPeriod(int $dateType) : array
    {
        $fcData = [];

        $today = date('Ymd');

        switch($dateType)
        {
            case 0:
                // 년
                $ym = date('Ym', strtotime($today));
                $fcData = [
                    'start' => date('Ymd', strtotime($ym . '01')),
                    'end' => date('Ymd', strtotime($ym . date('t', $today)))
                ];
                break;
            case 5:
            case 1:
                // 기간별 월 검색
                $fcData = [
                    'start' => $today,
                    'end' => $today
                ];
            default:
                break;
        }

        return $fcData;
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
     * 요금 조회
     *
     * @param array $d
     * @param int $option
     * @param string $complexCodePk
     * @param string $energyName
     * @param string $date
     * @param Command $command
     * @param int $dateType
     * @param bool $isArea
     * @param bool|true $isDateKey
     *
     * @return array
     *
     * @throws \Exception
     */
    public function setPrice(array $d, int $option, string $complexCodePk, string $energyName, string $date, Command $command, int $dateType, bool $isArea, bool $isDateKey = true) : array
    {
        $temp = [];

        foreach ($d as $key => $value) {
            if ($isArea == true && $value <= 0) {
                $temp[]	= 0;
            } else {
                $value = $this->setUnitCalculate($option, $energyName, $value, $isArea);
                $temp[] = $this->fee->getPrice($command, $key, $value, $option, $energyName, $complexCodePk, $date, $dateType, $isDateKey, $isArea);
            }
        }

        return $temp;
    }

    /**
     * 주어진 값을  단위에 맞게 환산
     *
     * @param int $option
     * @param string $energyName
     * @param int $value
     * @param bool $isArea
     *
     * @return int
     */
    public function setUnitCalculate(int $option, string $energyName, int $value, bool $isArea) : int
    {
        $fcResult = $value;

        if ($option === 4 && $energyName === 'electric_ghp' && $isArea === false) {
            // 현재는 전기인데, 가스 요금으로 환산해서 함
            // 추후 문제가 발생 할 경우 아래 코드는 주석 처리 할 것
            $fcResult = $value / 1000 / Config::GAS_TO_ELECTRIC_TRANS_VALUE;
        }

        return $fcResult;
    }

    /**
     * 단위면적으로 환산
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param string $energyName
     * @param int $dateType
     * @param string $date
     * @param array $data
     * @param bool|false $isLoop
     *
     * @return array
     *
     * @throws \Exception
     */
    public function setAreaValue(Command $command, string $complexCodePk, int $option, string $energyName, int $dateType, string $date, array $data, bool $isLoop = false) : array
    {
        $fcData = [];
        $fcPrices = [];
        $addOptions = [];

        $dividers = Config::DIVISOR_VALUES;
        $divider = $dividers[$option];

        $areaValue = $this->getDivider($command, $complexCodePk, $addOptions);

        if ($isLoop === true) {
            foreach ($data['data'] as $date => $item) {
                // wh 환산 값 복구 작업..
                $fcData[$date] = $item * $divider;
            }
        } else {
            $fcData = $data;
        }

        $tempUseds = $fcData; // 요금 구할 때는 단위 면적을 적용하지 않게 하기 위해서 임시로 저장

        // 사용량은 단위면적으로 계산
        $fcData = Utility::getInstance()->divideArray($fcData, $areaValue);

        $fcPrices = $this->setPrice($tempUseds, $option, $complexCodePk, $energyName, $date, $command, $dateType, true);

        return [
            'data' => $fcData,
            'price' => $fcPrices,
        ];
    }

    /**
     * 주기에 따라 과거 날짜값 조회
     *
     * @param string $date
     * @param int $dateType
     *
     * @return string
     */
    public function getLastDate(string $date, int $dateType) : string
    {
        $d = $date;

        switch ($dateType) {
            case 0:
                //year
                $d = $d - 1;
                break;
            case 1:
            case 6:
                //month
                $date = date('Ymd', strtotime($date . '01'));

                $d = Utility::getInstance()->addMonth($date, -1);
                $d = substr($d, 0, 6);
                break;
            case 2:
            case 4:
                //day
                $d = Utility::getInstance()->addDay($date, -1);
                break;
        }

        return $d;
    }

    /**
     * 단위 면적 조회
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param array $addOptions
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getDivider(Command $command, string $complexCodePk, array $addOptions) : int
    {
        $query = '';

        /**
         * 층: bems_complex
         * 세부: bems_home
         */
        /*
			if ($floor === 'all') {
				$query = $this->emsQuery->getQueryComplexLandArea($complexCodePk);
			} else {
			   $assigns = $this->getAssignmentFromOptions($addOptions);

			   $floorQuery = Utility::getInstance()->makeWhereClause('home', 'home_grp_pk', $assigns['floor']);
			   $roomQuery = Utility::getInstance()->makeWhereClause('home', 'home_ho_pk', $assigns['room']);
			   $query = $this->emsQuery->getQueryHomeLandArea($complexCodePk, $floorQuery, $roomQuery);
			}
		*/
        $query = $this->emsQuery->getQueryComplexLandArea($complexCodePk);
        $d = $command->query($query);

        if (count($d) <= 0)  {
            return 1;
        }

        return (int)$d[0]['building_area'];
    }

    /**
     * 기준값 조회
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param string|null $column
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getReference(Command $command, string $complexCodePk, int $option, string $column = null) : string
    {
        $query = $this->emsQuery->getQueryReference($complexCodePk, $option, $column);
        $temp = $command->query($query);

        $val = 0;

        if (count($temp) > 0) {
            $val = $temp[0]['val'];
        }

        return $val;
    }

    /**
     * 금월 데이터 조회 시 마감일에 따른 주기 조회
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param string $dateFormat
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getDueDatePeriodByMonth(Command $command, string $complexCodePk, int $option, string $date, string $dateFormat = 'Ymd') : array
    {
        $fcData = [];

        $query = $this->emsQuery->getDueday($option, $complexCodePk);
        $d = $command->query($query);

        $day = date('d');
        $dueDay = (int)$d[0]['closing_day'];
        $dueDate = '';

		$dueDay = $dueDay < 10 ? "0{$dueDay}" : $dueDay;

        if ($dueDay === 99) {
            $date = date('Ym');
            $endDay = date('t');
            $fcMonthStartDate = date($dateFormat, strtotime($date . '01'));
            $fcMonthEndDate = date($dateFormat, strtotime($date . $endDay));
        } else {
            $dueDate = $date . $dueDay;
            $fcMonthStartDate = date($dateFormat, strtotime($dueDate . '+1 day'));
            $fcMonthEndDate = date($dateFormat, strtotime($dueDate . '+1 month'));
        }

        if ($day > $dueDay) {
            // 현재 일이 마감일 보다 큰 경우
            $fcMonthStartDate = date($dateFormat, strtotime($dueDate . '+1 month +1 day'));
            $fcMonthEndDate = date($dateFormat, strtotime($dueDate . '+2 month'));
        }

        $fcData = [
            'start_date' => $fcMonthStartDate,
            'end_date' => $fcMonthEndDate,
            'due_date' => $dueDate,
            'due_day' => $dueDay,
        ];

        return $fcData;
    }

    /**
     * 부하 사용량 계산 (경부하,중부하,최대부하)
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param string $seasonType
     * @param string $statusType
     * @param int $option
     * @param string $date
     * @param string $sensorNo
     *
     * @return int
     *
     * @throws \Exception
     */
    public function calculateStatusUsed(Command $command, string $complexCodePk, string $seasonType, string $statusType, int $option, string $date, string $sensorNo) : int
    {
        $fcUsed = 0;

        $timeRanges = Config::SEASON_STATUS_TIME_RANGES[$seasonType][$statusType];
        $startMinute = Config::RAW_DATA_RECEIVE_START_MINUTE[$complexCodePk];

        if (count($timeRanges) === 0) {
            return $fcUsed;
        }

        $startMinute =  empty($startMinute) === true ? '00' : sprintf('%02d', $startMinute);

        foreach ($timeRanges AS $items) {
            $startDateTime = "{$date}{$items['start']}{$startMinute}00";
            $endDateTime = "{$date}{$items['end']}{$startMinute}00";
            $endDateTime = date('YmdHis', strtotime("{$endDateTime} +1 hours"));

            $query = $this->emsQuery->getQuerySelectStatusRawData($option, $sensorNo, $startDateTime, $endDateTime);
            $data = $command->query($query);

            if (isset($data[0]['val']) == true && $data[0]['val'] > 0) {
                $fcUsed += $data[0]['val'];
            }
        }

        return $fcUsed;
    }

    /**
     * 요일별에 따라 부하 사용 계산방식을 위한 함수
     *
     * @param Command $command
     * @param string $date
     * @param string $choiceStatusType
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getStatusType(Command $command, string $date, string $choiceStatusType) : string
    {
        $weekDay = date('w', strtotime($date));

        // 공휴일 조회
        $rHolidayQ = $this->emsQuery->getQuerySelectIsHoliday($date);
        $holidayData = $command->query($rHolidayQ);
        $holidayDate = count($holidayData) > 0 ? $holidayData[0]['holiday_date'] : '';

        if ((empty($holidayDate) === false || $weekDay == 0)
            && ($choiceStatusType === 'max_status' || $choiceStatusType === 'mid_status')) {
            // 일요일, 공휴일은 모두 최대부하-> 경부하, 중부하로 처리
            return 'low_status';
        }

        if (empty($holidayDate) === true
            && $weekDay == 6
            && $choiceStatusType === 'max_status') {
            // 공휴일이 아닌 토요일의 최대 부하-> 중간부하로 설정
            return 'mid_status';
        }

        return $choiceStatusType;
    }


    /**
     * 건물 위치 센서 조회
     *
     * @param string $complexCodePk
     * @param string $dong
     * @param string $floor
     * @param string $room
     *
     * @return string|null
     */
    public function getBuildingLocationSensor(string $complexCodePk, string $dong, string $floor, string $room) : string
    {
        $buildingSensor = '';

        $sensorObj = $this->getSensorManager($complexCodePk);

        if (is_null($sensorObj) === true) {
            return $buildingSensor;
        }

        $dongSensors = $sensorObj->getElectricDongSensor();
        $floorSensors = $sensorObj->getElectricFloorSensor();

        if ($dong === 'all' && $floor === 'all') {
            if (count($dongSensors) > 0) {
                return $dongSensors[$dong][$floor] ?? '';
            }

            if (count($floorSensors) > 0) {
                return $floorSensors[$floor][$room] ?? '';
            }
        }

        if ($dong !== 'all' &&
            ($floor === 'all' || $floor !== 'all')) {
            return $dongSensors[$dong][$floor] ?? '';
        }

        if ($dong === 'all'
            && ($floor === 'all' || $floor !== 'all')) {
            return $floorSensors[$floor][$room] ?? '';
        }

        return $buildingSensor;
    }

    /**
     * 사용량 조회
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
    public function getUsageSumData(Command $command, string $complexCodePk, int $option, int $dateType, string $date, array $addOptions = []) : array
    {
        $separatedSensors = [];
        $fcData = [];

        $isStatus = isset($addOptions['is_status']) === true ? $addOptions['is_status'] : false;
        $separatedSensors = isset($addOptions['separated_sensors']) == true ? $addOptions['separated_sensors'] : [];

        if (count($separatedSensors) > 0) {
            $fcData = $this->getEnergyHomeDataBySensor($command, $complexCodePk, $option, $dateType, $date, $addOptions, $separatedSensors);
        } else {
            $fcData = $this->getEnergyDataByHome($command, $complexCodePk, $option, $dateType, $date, $addOptions);
        }

        return [
            'current' => Utility::getInstance()->getUsedArraySum($fcData['current'], $isStatus),
            'predict' => Utility::getInstance()->getUsedArraySum($fcData['predict'], $isStatus),
        ];
    }
}