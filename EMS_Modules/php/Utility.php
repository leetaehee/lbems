<?php
namespace EMS_Module;

/**
 * Class Utility
 */
class Utility
{
    private static Utility $utility;

    private static array $divisors = Config::DIVISOR_VALUES;

    public static function getInstance()
    {
        if (isset(Utility::$utility) == false) {
            Utility::$utility = new Utility;
        }

        return Utility::$utility;
    }

    public function __construct()
    {
    }

    public function getTimeSpan(string $start, string $end, string $hour): float
    {
        if (isset($start) == false || isset($end) == false) {
            return 0;
        }

        $time1 = strtotime($start);
        $time2 = strtotime($end);
        $span = ($time2 - $time1) / 86400;

        if ($hour == false) {
            return round($span);
        }

        $float = $span - floor($span);

        return (double)(floor($span) . "." . round($float * 24));
    }

    public function parseTimeArray(array $d): array
    {
        $temp = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = $i . "";

            if ($i < 10) {
                $hour = '0' . $hour;
            }

            $hour = 'time' . $hour;
            $temp[$hour] = 0;
        }

        $len = count($d);

        for ($i = 0; $i < $len; $i++) {
            $hour = $d[$i]['hour'];
            $hour = 'time' . $hour;
            $temp[$hour] = round($d[$i]['power'] / Killo, 2);
        }

        return $temp;
    }

    public function getMaxGeneration(int $capa, int $day): int
    {
        return Config::MAX_GENERATION_TIME * $day * $capa;
    }

    public function hexToDec(string $hex): int
    {
        if (strlen($hex) != 2) {
            return $hex;
        }

        $hi = hexdec(substr($hex, 0, 1));
        $low = hexdec(substr($hex, 1, 1));

        $hi = (int)$hi << 4;
        $low = (int)$low;

        return $hi + $low;
    }

    public function toTwoDigit(int $num): string
    {
        return $num < 10 ? '0' . $num : $num;
    }

    public function addMonth(string $date, string $m): string
    {
        if ($date == 6) {
            // ??????????????? ?????? ????????? ????????? ????????? ???????????????.
            $date = $date . '01';
        }

        $time = strtotime($date);
        $str = $m . " months";
        $final = date("Ymd", strtotime($str, $time));

        return $final;
    }

    public function addDay(string $date, string $d): string
    {
        $time = strtotime($date);
        $str = $d . " day";
        $final = date("Ymd", strtotime($str, $time));
        return $final;
    }

    public function addMinute(string $date, string $minute): string
    {
        $time = strtotime($date);
        $str = $minute . " minutes";
        $final = date("YmdHi", strtotime($str, $time));

        return $final;
    }

    /**
     * ????????? ????????? ??????
     *
     * @param string $alias
     * @param string $col
     * @param string $val
     * @param string $operator
     *
     * @return string
     */
    public function makeWhereClause(string $alias, string $col, string $val, string $operator = '='): string
    {
        if ($val == "all" || $val == '') {
            // ????????? ALL ??? ???(1F, 2F ..)??? ??????????????? ???????????? ?????? ????????????
            return "";
        }
        return " AND {$alias}.{$col} {$operator} '{$val}' ";
    }

    /**
     * ???????????? ????????? ????????? ????????? ??????  (in, not in ...)
     *
     * @param string $alias
     * @param string $col
     * @param array $vals
     * @param string $operator
     *
     * @return string
     */
    public function makeWhereArrayClause(string $alias, string $col, array $vals, string $operator = 'IN'): string
    {
        $str = '';
        $whereCondition = '';

        if (count($vals) === 0) {
            return $whereCondition; // ???????????? ??????
        }

        foreach ($vals as $k => $v) {
            if (empty($str) === true) {
                $str .= "'{$v}'";
            } else if (empty($v) === false) {
                $str .= ",'{$v}'";
            }
        }

        if (empty($str) === false) {
            $whereCondition = " AND {$alias}.{$col} {$operator} ({$str})";
        }

        return $whereCondition;
    }

    public function setUnit(int $option, array $d): array
    {
        $divisor = self::$divisors;
        $temp = $divisor[$option];

        $arr = [];

        foreach ($d as $key => $value) {
            $arr[$key] = round($value / $temp, 2);
        }

        return $arr;
    }

    public function divideArray(array $d, string $divider): array
    {
        $temp = str_replace(',', '', $divider);

        if ($temp == 1 || $temp == 0) {
            return $d;
        }

        $arr = [];

        foreach ($d as $key => $value) {
            $arr[$key] = round($value / $temp, 2);
        }

        return $arr;
    }

    /**
     * ????????? ?????? ?????????  (?????????~?????????)
     *
     * @param $date
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getWeekDatePeriod(string $date): array
    {
        $today = date('Ymd');
        $dateObject = new \DateTime($today);

        $date = [
            'start' => $dateObject->modify('Sunday Last week')->format('Ymd'),
            'end' => $dateObject->modify('Saturday')->format('Ymd')
        ];

        return $date;
    }

    public function getDateFromDueday(string $dueday, string $d = ''): array
    {
        $tempDueDate = '';
        $dueDate = date('Ym');
        $isEndDay = false; // ????????? ?????? ??????

        if ($dueday < 10) {
            $dueday = '0' . $dueday;
        }

        if ($dueday === '99') {
            $isEndDay = true;
        } else {
            $dueDate = $dueDate . $dueday;
        }

        $date = [
            'start' => '00000000',
            'end' => '00000000'
        ];

        if (empty($date) === true) {
            $d = date('Ym');
        }

        if (strlen($d) === 8) {
            $tempDueDate = substr($d, 0, 6) . $dueday;
        } else {
            if (strlen($d) === 4) {
                $tempDueDate = $dueDate;
            } elseif ($isEndDay === false) {
                $tempDueDate = $d . $dueday;
            }
        }

        if ($isEndDay === true) {
            // ???????????? ????????? ??????
            if (strlen($d) === 8) {
                $d = substr($d, 0, 6);
            }

            $newTempEndDay = date('t', strtotime($d . '01'));
            $tempDueDate = $d . $newTempEndDay;

            $ym = date('Ym', strtotime($tempDueDate));
            $start = date('Ymd', strtotime($ym . '01'));
            $end = date('Ymd', strtotime($ym . $newTempEndDay));
            $nextStart = Utility::getInstance()->addMonth($start, +1);
            $nextYm = date('Ym', strtotime($nextStart));
            $nextEndDay = date('t', strtotime($nextStart));
            $nextEnd = date('Ymd', strtotime($nextYm . $nextEndDay));
        } else {
            $start = $tempDueDate;
            $endDayFromDueDate = (int)date('t', strtotime($start));

            $isNotShortEndDayFromDueDate = false;
            if ($endDayFromDueDate >= 28) {
                // ??? ?????? ??? ????????? 28??? ????????? ??????
                $isNotShortEndDayFromDueDate = true;
            }

            if ($isNotShortEndDayFromDueDate === false) {
                $start = Utility::getInstance()->addDay($start, 1);
            }

            $end = $tempDueDate;
            $day = (int)date("d");

            $nextStart = null;
            $nextEnd = null;
            $dueday = (int)$dueday;

            if ($day <= $dueday) {
                if ($isNotShortEndDayFromDueDate === true) {
                    $start = Utility::getInstance()->addMonth($start, -1);
                    $start = Utility::getInstance()->addDay($start, +1);
                    $nextStart = Utility::getInstance()->addMonth($start, +1);
                    $nextEnd = Utility::getInstance()->addDay($nextStart, -1);
                    $nextEnd = Utility::getInstance()->addMonth($nextEnd, +1);
                } else {
                    $start = Utility::getInstance()->addMonth($start, -1);
                }
            } elseif ($day > $dueday) {
                if ($isNotShortEndDayFromDueDate === true) {
                    $start = Utility::getInstance()->addMonth($start, -1);
                    $start = Utility::getInstance()->addDay($start, +1);

                    $nextStart = Utility::getInstance()->addMonth($start, +1);
                    $nextEnd = Utility::getInstance()->addDay($nextStart, -1);
                    $nextEnd = Utility::getInstance()->addMonth($nextEnd, +1);

                    $tStart = Utility::getInstance()->addDay($tempDueDate, +1);
                    if ($tStart !== $nextStart) {
                        // ?????? ??? 2?????? ?????? 28???????????? ?????? ?????? ????????????
                        $nextStart = Utility::getInstance()->addDay($end, +1);
                        $nextEnd = Utility::getInstance()->addDay($nextStart, -1);
                        $nextEnd = Utility::getInstance()->addMonth($nextEnd, +1);
                    }
                } else {
                    $start = Utility::getInstance()->addMonth($start, -1);
                    $start = Utility::getInstance()->addDay($start, +1);
                    $nextStart = Utility::getInstance()->addMonth($start, +1);
                    $nextEnd = Utility::getInstance()->addDay($nextStart, -1);
                    $nextEnd = Utility::getInstance()->addMonth($nextEnd, +1);
                }
            } else {
                $end = Utility::getInstance()->addMonth($end, 1);
            }
        }

        $date['start'] = $start;
        $date['end'] = $end;
        $date['next_start'] = $nextStart;
        $date['next_end'] = $nextEnd;
        $date['due_date'] = $dueDate;

        return $date;
    }

    public function getStartEndDateFromDueday(string $month, string $dueDay): array
    {
        $isEndDay = false; //  ?????? ??????
        $temp = [
            'start' => '00000000',
            'end' => '00000000',
        ];

        if (strlen($month) === 8) {
            $tempMonth = $month;
            $month = substr($tempMonth, 0, 6);
        }

        if ($dueDay === '99') {
            $endDay = date('t', strtotime($month . '01'));
            $dueDate = $month . $endDay;
            $isEndDay = true;
        } else {
            $dueDate = $month . $dueDay;
        }

        $endDayFromDueDate = (int)date('t', strtotime($dueDate));

        if ($isEndDay === true) {
            $startMonth = date('Ym', strtotime($dueDate));
            $startDate = date('Ymd', strtotime($startMonth . '01'));
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($dueDate);
        } else {
            if ($endDayFromDueDate === 28) {
                $startDate = (new \DateTime($dueDate))->modify('-1 month');
                $startDate = $startDate->modify('+1 day');
            } else {
                $startDate = (new \DateTime($dueDate))->modify('-1 month');
                $startDate = $startDate->modify('+1 day');
            }
            $endDate = new \DateTime($month . $dueDay);
        }

        return [
            'start' => $startDate->format('Ymd'),
            'end' => $endDate->format('Ymd'),
        ];
    }

    public function getYearFromDueday(string $dueday, string $d = ''): array
    {
        $date = [
            'start' => '00000000',
            'end' => '00000000'
        ];

        if (empty($d)) {
            $d = date('Ym');
        }
        if (strlen($d) == 4) {
            $d .= date('m');
        }

        $start = $d . $dueday;
        $start = Utility::getInstance()->addDay($start, 1);
        $end = $d . $dueday;
        $day = date("d");

        if ((int)$day <= (int)$dueday) {
            $start = Utility::getInstance()->addMonth($start, -1);
        } else {
            $end = Utility::getInstance()->addMonth($end, 1);
        }

        $date['start'] = $start;
        $date['end'] = $end;

        return $date;
    }

    /**
     * ??? ????????? ??????
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param string $year
     * @param string $dateCol
     * @param string $valCol
     *
     * @return array
     */
    public function makeYearData(string $complexCodePk, string $customUnit, array $d, string $year, string $dateCol, string $valCol): array
    {
        $ret = [];

        for ($i = 1; $i <= 12; $i++) {
            $month = $i;

            if ($i < 10) {
                $month = '0' . $i;
            }

            $ret[$year . $month] = 0;
        }

        $len = count($d);
        for ($i = 0; $i < $len; $i++) {
            $date = (int)$d[$i][$dateCol];
            $val = $d[$i][$valCol];
            if ($val < 0) {
                $val = 0;
            }

            if (array_key_exists($date, $ret) === true) {
                $ret[$date] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
            }
        }

        return $ret;
    }

    /**
     * ????????? ?????? ??? ??????
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param string $start
     * @param string $end
     * @param string $dateCol
     * @param string $valCol
     *
     * @return array
     */
    public function makeMonthRangeData(string $complexCodePk, string $customUnit, array $d, string $start, string $end, string $dateCol, string $valCol): array
    {
        $ret = [];

        $len = count($d);
        $date = '';

        $i = 0;
        while (true) {
            if (empty($date) === true) {
                $date = $start;
            }

            $ret[$date] = 0;

            // ??????????????? ??????
            $temp = date('Ymd', strtotime($date . '01'));
            $date = date('Ym', strtotime($temp . '+1 month'));

            if ($end > $date) {
                // ??? ?????? ??? 1??? ????????? ??????
                $ret[$date] = 0;
            }

            if ($end < $date) {
                // 1?????? ???????????? ?????? ???????????? ??????
                break;
            }

            $i++;
        }

        for ($i = 0; $i <= $len; $i++) {
            $date = (int)$d[$i][$dateCol];

            $val = $d[$i][$valCol];
            if ($val < 0) {
                $val = 0;
            }

            if (array_key_exists($date, $ret) === true) {
                $ret[$date] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
            }
        }

        return $ret;
    }

    /**
     * ??? ????????? ??????
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param string $date
     * @param string $dueDay
     * @param string $dateCol
     * @param string $valCol
     * @param bool $isUseNextDate
     *
     * @return array
     *
     * @throws \Exception
     */
    public function makeMonthData(string $complexCodePk, string $customUnit, array $d, string $date, string $dueDay, string $dateCol, string $valCol, bool $isUseNextDate): array
    {
        $day = date('d');

        if ($dueDay < 10) {
            $dueDay = '0' . $dueDay;
        }

        $temp = Utility::getInstance()->getStartEndDateFromDueday($date, $dueDay);

        $startDate = new \DateTime($temp['start']);
        $endDate = (new \DateTime($temp['end']))->modify('+1 day');

        if ($isUseNextDate === true && $day > $dueDay) {
            $tempStart = date('Ymd', strtotime($temp['start'] . '+1 months'));
            $tempEnd = date('Ymd', strtotime($temp['end'] . '+1 months'));

            $tStart = date('Ymd', strtotime($temp['end'] . '+1 day'));
            if ($tStart !== $tempStart) {
                // ?????? ??? 2?????? ?????? 28???????????? ?????? ?????? ????????????
                $tempStart = $tStart;
                $tempEnd = date('Ymd', strtotime($tempStart . '-1 day +1 month'));
            }

            $startDate = (new \DateTime($tempStart));
            $endDate = (new \DateTime($tempEnd))->modify('+1 day');
        }

        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($startDate, $interval, $endDate);

        $ret = [];

        foreach ($daterange as $date) {
            $dateStr = $date->format('Ymd');
            $ret[$dateStr] = 0;
        }

        $len = count($d);
        for ($i = 0; $i < $len; $i++) {
            $date = (int)$d[$i][$dateCol];

            $val = $d[$i][$valCol];
            if ($val < 0) {
                $val = 0;
            }

            if (array_key_exists($date, $ret)) {
                $ret[$date] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
            }
        }

        return $ret;
    }

    /**
     * ???????????? ???????????? ?????? ?????? ?????? ??????
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param string $start
     * @param string $end
     * @param string $dateCol
     * @param string $valCol
     *
     * @return array
     *
     * @throws \Exception
     */
    public function makeNotDueDayMonthData(string $complexCodePk, string $customUnit, array $d, string $start, string $end, string $dateCol, string $valCol): array
    {
        $startDate = new \DateTime($start);
        $endDate = (new \DateTime($end))->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($startDate, $interval, $endDate);

        $ret = [];

        foreach ($daterange as $date) {
            $dateStr = $date->format('Ymd');
            $ret[$dateStr] = 0;
        }

        $len = count($d);
        for ($i = 0; $i < $len; $i++) {
            $date = (int)$d[$i][$dateCol];

            $val = $d[$i][$valCol];
            if ($val < 0) {
                $val = 0;
            }

            if (array_key_exists($date, $ret) === true) {
                $ret[$date] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $val);
            }
        }

        return $ret;
    }

    /**
     * ??? ????????? ??????
     *
     * @param string $complexCodePk
     * @param string $customUnit
     * @param array $d
     * @param int $day
     *
     * @return array
     */
    public function makeMinuteData(string $complexCodePk, string $customUnit, array $d, int $day): array
    {
        $ret = [];

        if ($day == null) {
            $day = date('YmdH');
        }

        for ($i = 0; $i < 60; $i = $i + 5) {
            $minute = Utility::getInstance()->toTwoDigit($i);
            $time = $day . $minute;
            $ret[$time] = 0;
        }

        foreach ($d as $key => $value) {
            if (array_key_exists($key, $ret) === true) {
                $ret[$key] = Utility::getInstance()->getOverrideUnitValue($complexCodePk, $customUnit, $value);
            }
        }

        return $ret;
    }

    /**
     * ?????? ????????? ?????? ??????.
     *
     * @param array $destinations
     * @param array $adds
     * @param bool $isStatus
     *
     * @return array $fcData
     */
    public function addKeyArray(array $destinations, array $adds, bool $isStatus): array
    {
        $fcData = [];

        for ($i = 0; $i < count($destinations); $i++) {
            if ($destinations[$i]['home_grp_pk'] === $adds[$i]['home_grp_pk']) {
                $fcSum = $destinations[$i]['val'] + $adds[$i]['val'];
                $fcData[$i] = [
                    'home_grp_pk' => $destinations[$i]['home_grp_pk'],
                    'val' => $fcSum,
                ];

                if ($isStatus === true) {
                    $fcData[$i]['low_status'] = $destinations[$i]['low_status'] + $adds[$i]['low_status'];
                    $fcData[$i]['mid_status'] = $destinations[$i]['mid_status'] + $adds[$i]['mid_status'];
                    $fcData[$i]['max_status'] = $destinations[$i]['max_status'] + $adds[$i]['max_status'];
                }
            }
        }

        return $fcData;
    }

    /**
     * ????????? ?????? ???????????? ?????? ????????? ??????
     *
     * @param array $data
     * @param string|null $key
     *
     * @return array $fcData
     */
    public function setArraySumByMerge(array $data, string $key = null): array
    {
        $fcData = [];

        $isKeyExist = false;
        if ($key !== null) {
            $fcData = [
                "{$key}" => [],
            ];
            $isKeyExist = true;
        }

        for ($i = 0; $i < count($data); $i++) {
            if ($isKeyExist === true) {
                $fcUseds = $data[$i][$key];
                foreach ($fcUseds as $k => $v) {
                    $fcData[$key][$k] += $v;
                }
            } else {
                $fcUseds = $data[$i];
                foreach ($fcUseds as $k => $v) {
                    $fcData[$k] += $v;
                }
            }
        }

        return $fcData;
    }

    /**
     * ????????? ?????? ???????????? ?????? 0?????? ?????????
     *
     * @param array $data
     * @param string|null $key
     *
     * @return array $fcData
     */
    public function setArrayIndexZero(array $data, string $key = null): array
    {
        $fcData = [];

        $isKeyExist = false;
        if ($key !== null) {
            $fcData = [
                "{$key}" => [],
            ];
            $isKeyExist = true;
        }

        for ($i = 0; $i < count($data); $i++) {
            if ($isKeyExist === true) {
                $fcUseds = $data[$i][$key];
                foreach ($fcUseds as $k => $v) {
                    $fcData[$key][$k] = 0;
                }
            } else {
                $fcUseds = $data[$i];
                foreach ($fcUseds as $k => $v) {
                    $fcData[$k] = 0;
                }
            }
        }

        return $fcData;
    }

    /**
     * ????????? ?????? ?????? ?????? sum
     *
     * @param array $data
     * @param bool $isStatus
     *
     * @return array $fcData
     */
    public function getUsedArraySum(array $data, bool $isStatus) : array
    {
        $fcData = [
            'data' => 0,
            'price' => 0,
        ];

        if ($isStatus === true) {
            return $data;
        }

        if (is_array($data['data']) === false) {
           return [
               'data' => $data['data'],
               'price' => $data['price'],
           ];
        }

        $usedValues = array_values($data['data']);
        if (count($usedValues) > 0) {
            $fcData['data'] = (float)array_sum($usedValues);
        }

        $priceValues = array_values($data['price']);
        if (count($priceValues) > 0) {
            $fcData['price'] = array_sum($priceValues);
        }

        return $fcData;
    }

    /**
     * ????????? ?????? ??????, ???????????? ????????? ??? ?????? ??????
     *
     * @param string $key
     * @param array $data
     *
     * @return array
     */
    public function arrayKeyCheckResult(string $key, array $data) : array
    {
        return array_key_exists($key, $data) === true ? $data[$key] : [];
    }

    /**
     * ???????????? ????????? ???????????? ??????
     *
     * @param string $password
     *
     * @return string
     */
    public function getPasswordHashValue(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * ????????? ???????????? ???????????? ???????????? ??????
     *
     * @param string $password
     * @param string $dbPassword
     *
     * @return bool
     */
    public function getPasswordVerifyResult(string $password, string $dbPassword): bool
    {
        return password_verify($password, $dbPassword);
    }

    /**
     * ????????? ?????????/????????? ???  ???????????? ?????? ??? ??????
     *
     * @param int $keyLength
     *
     * @return string
     */
    public function getSecretKey(int $keyLength = 15): string
    {
        $secretKey = '';

        $secretKeyRule = Config::SECRET_KEY_RULE;
        $length = strlen($secretKeyRule);

        for ($fcIndex = 0; $fcIndex < $keyLength; $fcIndex++) {
            $secretKey .= $secretKeyRule[rand(0, $length - 1)];
        }

        return $secretKey;
    }

    /**
     * ????????? ?????????
     *
     * @param string $value
     * @param array $addOptions
     *
     * @return string
     */
    public function updateEncryption(string $value, array $addOptions = []): string
    {
        $envData = $this->getEnvData();

        $algorithm = Config::ENCRYPTION_ALGORITHM;
        $secretKey = isset($addOptions['secret_key']) === false ? $envData['SECRET_KEY'] : $addOptions['secret_key'];
        $ivKey = isset($addOptions['iv_key']) === false ? $envData['IV_KEY'] : $addOptions['iv_key'];

        return base64_encode(openssl_encrypt($value, $algorithm, hex2bin($secretKey), 1, hex2bin($ivKey)));
    }

    /**
     * ????????? ?????????
     *
     * @param string $value
     * @param array $addOptions
     *
     * @return string
     */
    public function updateDecryption(string $value, array $addOptions = []): string
    {
        $envData = $this->getEnvData();

        $algorithm = Config::ENCRYPTION_ALGORITHM;
        $secretKey = isset($addOptions['secret_key']) === false ? $envData['SECRET_KEY'] : $addOptions['secret_key'];
        $ivKey = isset($addOptions['iv_key']) === false ? $envData['IV_KEY'] : $addOptions['iv_key'];

        return openssl_decrypt(base64_decode($value), $algorithm, hex2bin($secretKey), 1, hex2bin($ivKey));
    }

    /**
     * ?????? ??? ??????
     *
     * @param string $value
     * @param string $hashAlgorithm
     *
     * @return string
     */
    public function getHashKey(string $value, string $hashAlgorithm = 'sha256'): string
    {
        return hash($hashAlgorithm, $value);
    }

    /**
     * ??????????????????????????????(XSS) ??????
     *
     * @param string $str
     *
     * @return string
     */
    public function removeXSS(string $str): string
    {
        return htmlspecialchars($str);
    }

    /**
     * ??? ????????? ???????????? ?????????????????????????????? ?????? ??????
     *
     * @param array $forms
     *
     * @return array
     */
    public function removeXSSFromFormData(array $forms): array
    {
        $fcData = [];

        foreach ($forms as $key => $value) {
            $value = empty($value) === false ? $this->removeXSS($value) : '';
            $fcData[$key] = $value;
        }

        return $fcData;
    }

    /**
     * array ????????? ?????? ?????? ????????? ??????
     *
     * @param array $data
     * @param string $keyName
     * @param string $searchColumn
     *
     * @return array
     */
    public function makeSelectedDataByKey(array $data, string $keyName, string $searchColumn): array
    {
        $fcData = [];

        if (count($data) < 1) {
            return $fcData;
        }

        if ($keyName === '') {
            return $data;
        }

        if ($keyName !== '') {
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i][$searchColumn] === $keyName) {
                    $fcData = $data[$i];
                    break;
                }
            }
        }

        return $fcData;
    }

    /**
     * curl ??????
     *
     * @param string $url
     * @param string $method
     * @param array $httpHeader
     * @param array $data
     * @param array $options
     *
     * @return array|false[]
     */
    public function curlProcess(string $url, string $method, array $httpHeader, array $data, array $options = []): array
    {
        $fcResult = [
            'success' => false,
        ];

        if (count($data) === 0) {
            return $fcResult;
        }

        $timeOut = isset($options['time_out']) === true ? $options['time_out'] : MaxTimeout;
        $xFormUrlencoded = isset($options['x-form-urlencoded']) === true ? $options['x-form-urlencoded'] : false;

        if ($method === 'GET') {
            $url = $url . '?' . http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (count($httpHeader) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        }

        if ($method === 'POST') {

            $jsonData = $this->responseJSON($data);
            if ($xFormUrlencoded === true) {
                $jsonData = http_build_query($data);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $fcResponse = curl_exec($ch);
        $fcResultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'code' => $fcResultCode,
            'msg' => $fcResponse
        ];
    }

    /**
     * JSON ??????
     *
     * @param array $prints
     * @param string $format
     *
     * @return string
     */
    public function responseJSON(array $prints, string $format = 'JSON_UNESCAPED_UNICODE'): string
    {
        $jsonContent = json_encode($prints);

        switch ($format) {
            case 'JSON_UNESCAPED_UNICODE' :
                $jsonContent = json_encode($prints, JSON_UNESCAPED_UNICODE);
                break;
        }

        return $jsonContent;
    }

    /**
     * ?????? ??????
     *
     * @param string $folderName
     * @param string $fileName
     * @param string $content
     * @param array $options
     *
     * @return string ?????? ????????? ?????? ?????? ?????? (?????? : YmdHis)
     */
    public function log(string $folderName, string $fileName, string $content, array $options = []) : string
    {
        $logTraceDate = date('YmdHis');

        /*
         * $options
         * show_type : ?????????????????? ?????? |
         * - string : ????????? (?????????),
         * - array : ??????
         */
        $showType = isset($options['show_type']) === true ? $options['show_type'] : 'string';

        $envData = $this->getEnvData();

        $path = $envData['LOG_PATH'] . "{$folderName}";

        $logPrintDate = "[" . $logTraceDate . "]";
        $fileName = $fileName . "_" . date('Ymd') . ".log";

        // ?????? ?????? ?????? ?????? ??????
        if (file_exists($path) === false) {
            mkdir($path, 0777, true);
        }

        /*
         * ?????? ?????? ?????? ??????
         * - ????????? ???????????? ????????? ?????? ?????? => ????????? ????????????
         */
        switch ($showType) {
            case 'array' :
                //error_log(var_export($xml, 1), 3, 'path ??????');
                break;
            case 'string' :
                error_log("{$logPrintDate} {$content} \n", 3, "{$path}/{$fileName}");
                break;
        }

        return $logTraceDate;
    }

    /**
     * ???????????? ????????? ???????????? ?????????
     *
     * @param string $str
     * @param string $delimiter
     *
     * @return array|null
     */
    public function getExplodeData(string $str, string $delimiter = ' ') : ?array
    {
        $fcData = explode($delimiter, $str);

        if (count($fcData) < 1) {
            return [];
        }

        return $fcData;
    }

    /**
     * ????????? ????????? implode??? ?????? ???????????? ??????
     *
     * @param array $data
     * @param string $separator
     *
     * @return string
     */
    public function makeImplodeString(array $data, string $separator = ",") : string
    {
        if (count($data) < 1) {
            return '';
        }

        return implode($separator, $data);
    }

    /**
     * ?????? ?????? ??????  (', ", * ???)
     *
     * @param array $data
     * @param string $character
     *
     * @return array
     */
    public function addCharacter(array $data, string $character = "'") : array
    {
        if (count($data) < 1) {
            return $data;
        }

        for ($i = 0; $i < count($data); $i++) {
            $value = $data[$i];

            if (empty($value) === true) {
                $value = '';
            }

            switch ($character) {
                case "'" :
                    if (is_int($data[$i]) === false && is_float($data[$i]) === false) {
                        $data[$i] = "'{$value}'";
                    }
                    break;
                case "`" :
                    $data[$i] = "`{$value}`";
                    break;
            }
        }

        return $data;
    }

    /**
     * ????????? ?????? ????????? ?????? ?????? ????????? ????????? ?????? ??????
     *
     * @param string $complexCodePk
     * @param string $unit
     * @param int $usage
     *
     * @return int $usage
     */
    public function getOverrideUnitValue(string $complexCodePk, string $unit, int $usage): int
    {
        $fcUsage = $usage;
        $complexInfo = CONFIG::UNIT_OVERRIDE_INFO;

        if (in_array($complexCodePk, $complexInfo) === false) {
            return $fcUsage;
        }

        if ($usage < 1) {
            return 0;
        }

        // ????????? ??????..
        // setUnit ??? ?????? ?????? ??????
        /*
            switch ($unit) {
                case 'm3' :
                    $fcUsage = $usage/1;
                    break;
            }
        */

        return $fcUsage;
    }

    /**
     * ????????? ?????? ??????  prefix ?????? ??????
     *
     * @return string
     */
    public function getConnectionMethodPrefix(): string
    {
        $connectionMethodPrefix = '';

        if (strpos($_SERVER['HTTP_REFERER'], '/mobile/') !== false
            || $_SERVER['REQUEST_URI'] === '/mobile/') {
            $connectionMethodPrefix = 'mb_';
        }

        return $connectionMethodPrefix;
    }

    /**
     * allData ?????? ?????? ?????? ??? ??? ??????
     *
     * @param string $column
     * @param array $data
     *
     * @return string
     */
    public function getCheckValueByAllData(string $column, array $data): string
    {
        $allDataJsonValues = Config::ALL_DATA_JSON_VALUES;

        $value = '';

        if (in_array($column, $allDataJsonValues) === true) {
            $value = $data[$column];
        }

        return $value;
    }

    /**
     * ???????????? ????????? ????????? ???????????? ?????? ??? ???
     *
     * @param int $option
     * @param string $energyName
     *
     * @return int
     */
    public function getChangedOption(int $option, string $energyName): int
    {
        $fcOption = $option;

        if ($option === 4 && $energyName === 'electric_ghp') {
            $fcOption = 1;
        }

        return $fcOption;
    }

    /**
     * env ?????? ??????
     *
     * @return array
     */
    public function getEnvData() : array
    {
        return parse_ini_file(dbConfigFile);
    }

    /**
     * ???????????? ??????
     *
     * @return array
     */
    public function getBaseDate() : array
    {
        $dateTime = date('YmdHis', strtotime('20221202211500'));
        //$dateTime = date('YmdHis');

        return [
            'date' => date('Ymd', strtotime($dateTime)),
            'date_time' => date('YmdHis', strtotime($dateTime)),
            'time' => date('His', strtotime($dateTime)),
        ];
    }


}