<?php
namespace EMS_Module;

/**
 * Class MigrationQuery
 */
class MigrationQuery
{
    /** @var array $columnNames 단위 */
    private array $columnNames = Config::COLUMN_NAMES;

    /** @var string[] $sensorColumnNames bems_sensor_ 에너지원별 컬럼 */
    private array $sensorColumnNames = Config::SENSOR_COLUMN_NAMES;

    /** @var array $sensorTableNames 센서테이블 */
    private array $sensorTableNames = Config::SENSOR_TABLES;

    /** @var array $sensorTypes 센서 이름 */
    private array $sensorTypes = Config::SENSOR_TYPES;

    /** @var array $monthTableNames 월통계 테이블  */
    private array $monthTableNames = Config::MONTH_TABLES;

    /** @var array $rawTableNames 미터 테이블  */
    private array $rawTableNames = Config::RAW_TABLES;

    /** @var array $dayTableNames 일통계 */
    private array $dayTableNames = Config::DAILY_TABLES;

    /** @var array $limitValColumnsNames 기준값 컬렴 */
    private array $limitValColumnsNames = Config::LIMIT_COLUMN_NAMES;

    /** @var array $closingDayColumns 마감일 컬럼 */
    private array $closingDayColumns = Config::CLOSING_DAY_COLUMN_NAMES;

    /** @var array $ntekTables 엔텍 테이블 정보 */
    private array $ntekTables = Config::NTEK_TABLES;

    /** @var array $cncTables CNC 데이터 테이블 정보  */
    private array $cncTables = Config::CNC_TABLES;

    /**
     * MigrationQuery constructor.
     */
    public function __construct()
    {
    }

    /**
     * MigrationQuery destructor.
     */
    public function __destruct()
    {
    }


    //------------------------------------------------------------------------------------------------------------
    // 공통
    //------------------------------------------------------------------------------------------------------------
    /**
     * 해당일자에 최대 누적량 조회
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $start
     * @param string $end
     *
     * @return string
     */
    public function getQueryUsedByDate(int $option, string $sensorNo, string $start, string $end): string
    {
        $rawTables = $this->rawTableNames;
        $columns = $this->columnNames;

        $rawTable = $rawTables[$option];
        $column = $columns[$option];

        $query = "SELECT MAX(`meter`.`{$column}`) AS `max`,
                         MIN(`meter`.`{$column}`) AS `min`,
                         MAX(`meter`.`{$column}`) - MIN(`meter`.`{$column}`) AS `val`
                  FROM `{$rawTable}` AS `meter`
                  WHERE `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `meter`.`val_date` >= '{$start}000000'
                  AND `meter`.`val_date` <= '{$end}235959'
                  GROUP BY `meter`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * RAW DATA 조회
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $startDateTime
     * @param string $endDateTime
     *
     * @return string
     */
    public function getQuerySelectRawData(int $option, string $sensorNo, string $startDateTime, string $endDateTime) : string
    {
        $rawTableNames = $this->rawTableNames;
        $columnNames = $this->columnNames;

        $rawTable = $rawTableNames[$option];
        $column = $columnNames[$option];

        $query = "SELECT `sensor_sn`,
                         `val_date`, 
                         `current_w`,
                         `error_code`,
                         `{$column}` AS `val`   
                  FROM `{$rawTable}`
                  WHERE `sensor_sn` = '{$sensorNo}' 
                  AND `val_date` >= '{$startDateTime}'
                  AND `val_date` <= '{$endDateTime}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 일 통계 1일 누적량 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 일 통계 테이블에서 전체 데이터 모두 조회
     *
     * @param int $option
     *
     * @return string
     */
    public function getQueryDailyTableAllData(int $option): string
    {
        $sensorTables = $this->sensorTableNames;
        $dailyTables = $this->dayTableNames;

        $dailyTable = $dailyTables[$option];
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `sensor`.`complex_code_pk`,
                         `daily`.`sensor_sn`,
                         `daily`.`val_date`
                  FROM `{$sensorTable}` AS `sensor`
                       LEFT JOIN `{$dailyTable}` AS `daily`
                          ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
				  WHERE `sensor`.`complex_code_pk` <> '2001' 
				  AND `daily`.`val_date` >= '20210822'
                  AND `daily`.`val_date` <= '20220412'
                  ORDER BY `daily`.`val_date` ASC
                 ";

        return $query;
    }

    /**
     * 일 통계 1일 누적량 업데이트
     *
     * @param int $option
     * @param int $val
     * @param int $totalUsed
     * @param string $sensorNo
     * @param string $date
     *
     * @return string
     */
    public function getQueryUpdateDailyTable(int $option, int $val, int $totalUsed, string $sensorNo, string $date): string
    {
        $dailyTables = $this->dayTableNames;
        $dailyTable = $dailyTables[$option];

        $query = "UPDATE `{$dailyTable}` SET 
                    `val` = '{$val}',
                    `total_val` = '{$totalUsed}'
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `val_date` = '{$date}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 월 통계 1개월 누적량 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 월 통계 테이블에서 전체 데이터 모두 조회
     *
     * @param int $option
     *
     * @return string
     */
    public function getQueryMonthTableAllData(int $option): string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];

        $query = "SELECT `sensor`.`complex_code_pk`,
                         `month`.`sensor_sn`,
                         `month`.`ym`,
                         `month`.`closing_day`,
                         `month`.`st_date`,
                         `month`.`ed_date`
                  FROM `{$sensorTable}` AS `sensor`
                     LEFT JOIN `{$monthTable}` AS `month`
                        ON `sensor`.`sensor_sn` = `month`.`sensor_sn`
                  WHERE `month`.`ym` >= '202108'
                  AND `month`.`ym` <= '202204'
                  ORDER BY `month`.`ym` ASC
                 ";

        return $query;
    }

    /**
     * 월 통계 1개월누적량 업데이트
     *
     * @param int $option
     * @param int $startUsed
     * @param int $endUsed
     * @param int $totalUsed
     * @param string $sensorNo
     * @param string $ym
     *
     * @return string
     */
    public function getQueryUpdateMonthTable(int $option, int $startUsed, int $endUsed, int $totalUsed, string $sensorNo, string $ym): string
    {
        $monthTables = $this->monthTableNames;
        $monthTable = $monthTables[$option];

        $query = "UPDATE `{$monthTable}` SET
                    `val_st` = '{$startUsed}',
                    `val_ed` = '{$endUsed}',
                    `val` = '{$totalUsed}'
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `ym` = '{$ym}'
                 ";

        return $query;
    }

    /**
     * bems_weather 테이블에서 키 정보 조회
     *
     * @return string
     */
    public function getQuerySelectWheatherComplexData(): string
    {
        $query = "SELECT `weath`.`complex_code_pk`,
                         `complex`.`name` 
                  FROM `bems_weather` AS `weath`
                    INNER JOIN `bems_complex` AS `complex`
                        ON `weath`.`complex_code_pk` = `complex`.`complex_code_pk`
                  ORDER BY `complex`.`complex_code_pk` asc
                  ";

        return $query;
    }

    /**
     * 날씨 정보  암호화 처리 후 변경
     *
     * @param string $complexCodePk
     * @param string $name
     *
     * @return string
     */
    public function getQueryUpdateWeatherData(string $complexCodePk, string $name): string
    {
        $query = "UPDATE `bems_weather` 
                  SET `name` = '{$name}'
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                  ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 로그인 로그 중 아이피 컬럼  암호화 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 로그인 시 아이피 컬럼 추출
     *
     * @return string
     */
    public function getQuerySelectLoginIp(): string
    {
        $query = "SELECT `log_pk`, `ip_addr` FROM `bems_admin_login_log`";

        return $query;
    }

    /**
     * 로그인 시 아이피를 암호화 하여 변경
     *
     * @param int $logPk
     * @param string $ip
     *
     * @return string
     */
    public function getQueryUpdateLoginIpEncryption(int $logPk, string $ip): string
    {
        $query = "UPDATE `bems_admin_login_log` 
                  SET `ip_addr` = '{$ip}'
                  WHERE `log_pk` = '{$logPk}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // ntek 데이터 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 시작일,종료일을 기준으로 하여 ntek 테이블에서 데이터 마이그레이션
     *
     * @param string $sensorNo
     * @param string $startDate
     * @param string $endDate
     *
     * @return string
     */
    public function getQueryNtekMeterByDateData(string $sensorNo, string $startDate, string $endDate): string
    {
        $ntekTables = $this->ntekTables;

        $sensorTable = $ntekTables['sensor_table'];
        $meterTable = $ntekTables['meter_table'];

        $query = "SELECT `meter`.`sensor_sn`,
                         `meter`.`val_date`,
                         `meter`.`watt`,
                         `meter`.`kwh_imp`,
                         `meter`.`error_code`
                  FROM `{$sensorTable}` AS `sensor`
                     LEFT JOIN `{$meterTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `sensor`.`fg_use` = 'y'
                  AND `meter`.`val_date` >= '{$startDate}000000'
                  AND `meter`.`val_date` <= '{$endDate}235959'
                  AND MOD(SUBSTR(`meter`.`val_date`, 11, 2), 5) = 0
                ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // cnc 데이터 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 시작일,종료일을 기준으로 하여 cnc 테이블에서 데이터 마이그레이션
     *
     * @param string $sensorNo
     * @param string $startDateTime
     * @param string $endDateTime
     *
     * @return string
     */
    public function getQueryCncMeterByDateData(string $sensorNo, string $startDateTime, string $endDateTime): string
    {
        $cncTables = $this->cncTables;

        $sensorTable = $cncTables['sensor_table'];
        $meterTable = $cncTables['meter_table'];

        $query = "SELECT `meter`.`sensor_sn`,
                         `meter`.`val_date`,
                         IFNULL(`meter`.`ch1_pulse_val`, 0) AS `ch1_pulse_val`,
                         IFNULL(`sensor`.`ch1_unit`, 0) AS `ch1_unit`,
                         `meter`.`error_code`
                  FROM `{$sensorTable}` AS `sensor`
                     LEFT JOIN `{$meterTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `sensor`.`fg_use` = 'y'
                  AND `meter`.`val_date` >= '{$startDateTime}'
                  AND `meter`.`val_date` <= '{$endDateTime}'
                  AND MOD(SUBSTR(`meter`.`val_date`, 11, 2), 5) = 0
                ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 효율 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 효율 마이그레이션을 위한 데이터 조회
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $startDate
     * @param string $endDate
     *
     * @return string
     */
    public function getQuerySelectEfficiencyMonthData(int $option, string $sensorNo, string $startDate, string $endDate) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];

        $query = "SELECT IFNULL(MIN(`meter`.`pf`), 0) AS `efficiency_st`,
                         IFNULL(MAX(`meter`.`pf`), 0) AS `efficiency_ed`,
                         IFNULL(AVG(`meter`.`pf`), 0) AS `efficiency`
                  FROM `bems_home` AS `home`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                     LEFT JOIN `{$rawTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `meter`.`val_date` >= '{$startDate}000000'
                  AND `meter`.`val_date` <= '{$endDate}235959'      
                  GROUP BY `meter`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * 효율 마이그레이션
     *
     * @param int $option
     * @param string $ym
     * @param string $sensorNo
     * @param int $minEfficiency
     * @param int $maxEfficiency
     * @param int $efficiency
     *
     * @return string
     */
    public function getQueryUpdateEfficiencyMonth(int $option, string $ym, string $sensorNo, int $minEfficiency, int $maxEfficiency, int $efficiency) : string
    {
        $monthTables = $this->monthTableNames;
        $monthTable = $monthTables[$option];

        $query = "UPDATE `{$monthTable}`
                  SET `efficiency_st` = '{$minEfficiency}',
                      `efficiency_ed` = '{$maxEfficiency}',
                      `efficiency` = '{$efficiency}'
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `ym` = '{$ym}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 일 통계 부하량(경, 중, 최대부하)에 대한 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * 일 통계 테이블에서 전체 데이터 모두 조회
     *
     * @param int $option
     * @param string $date
     *
     * @return string
     */
    public function getQueryDailyTableAllStatusData(int $option, string $date): string
    {
        $sensorTables = $this->sensorTableNames;
        $dailyTables = $this->dayTableNames;

        $dailyTable = $dailyTables[$option];
        $sensorTable = $sensorTables[$option];

        // 1년 단위로 검색해서 할 것
        $query = "SELECT `sensor`.`complex_code_pk`,
                         `daily`.`sensor_sn`, 
                         `daily`.`val_date`
                  FROM `bems_complex` AS `complex`
                      LEFT JOIN `bems_home` AS `home`   
                         ON `complex`.`complex_code_pk` = `home`.`complex_code_pk`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                      LEFT JOIN `{$dailyTable}` AS `daily`
                         ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                  WHERE `daily`.`val_date` < '{$date}'
                  AND `complex`.`electricType` IN ('N','S')
                  AND `complex`.`typeGubun` IN ('type2', 'type3')
                  AND `complex`.`fg_del` = 'n'
                  AND `sensor`.`fg_use` = 'y'
                  ORDER BY `daily`.`val_date` ASC
                 ";

        return $query;
    }

    /**
     * 월 통계 테이블에서 전체 데이터 모두 조회
     *
     * @param int $option
     *
     * @return string
     */
    public function getQueryMonthTableAllStatusData(int $option) : string
    {
        $monthTableNames = $this->monthTableNames;
        $monthTable = $monthTableNames[$option];

        $sensorTableNames = $this->sensorTableNames;
        $sensorTable = $sensorTableNames[$option];

        $query = "SELECT `month`.`sensor_sn`,
                         `month`.`ym`,
                         `month`.`st_date`,
                         `month`.`ed_date`
                  FROM `bems_home` AS `home`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                     LEFT JOIN `{$monthTable}` AS `month`
                        ON `sensor`.`sensor_sn` = `month`.`sensor_sn`
                  WHERE `sensor`.`fg_use` = 'y'  
                  ORDER BY `month`.`ym` ASC
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 암호화 마이그레이션
    //------------------------------------------------------------------------------------------------------------
    /**
     * bems_admin에서 암호화 컬럼 조회
     *
     * @return string
     */
    public function getQuerySelectAdminEncryptionColumn() : string
    {
        $query = "SELECT `admin_pk`,
                         `name`,
                         `email`,
                         `hp`
                  FROM `bems_admin`
                 ";

        return $query;
    }

    /**
     * bems_admin 암호화 항목 마이그레이션
     *
     * @param int $pk
     * @param string $name
     * @param string $email
     * @param string $hp
     *
     * @return string
     */
    public function getQueryUpdateAdminEncryptionColumn(int $pk, string $name, string $email, string $hp) : string
    {
        $query = "UPDATE `bems_admin`
                  SET `name` = '{$name}',
                      `email` = '{$email}',
                      `hp` = '{$hp}'
                  WHERE `admin_pk` = '{$pk}'
                 ";

        return $query;
    }

    /**
     * bems_complex 에서 암호화 컬럼 조회
     *
     * @return string
     */
    public function getQuerySelectComplexEncryptionColumn() : string
    {
        $query = "SELECT `complex_code_pk`,
                         `name`,
                         `addr`,
                         `tel`,
                         `fax`,
                         `manager`
                  FROM `bems_complex` 
                 ";

        return $query;
    }

    /**
     * bems_complex 암호화 항목 마이그레이션
     *
     * @param string $complexCodePk
     * @param string $name
     * @param string $addr
     * @param string $tel
     * @param string $fax
     * @param string $manager
     *
     * @return string
     */
    public function getQueryUpdateComplexEncryptionColumn(string $complexCodePk, string $name, string $addr, string $tel, string $fax, string $manager) : string
    {
        $query = "UPDATE `bems_complex`
                  SET `name` = '{$name}',
                      `addr` = '{$addr}',
                      `tel` = '{$tel}',
                      `fax` = '{$fax}',
                      `manager` = '{$manager}'
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }
}