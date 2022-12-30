<?php
namespace EMS_Module;

/**
 * EMSQuery EMS 쿼리
 */
class EMSQuery
{
    /** @var CommonQuery|null $commonQuery 공통 쿼리  */
    private ?CommonQuery $commonQuery = null;

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
     * EMSQuery constructor.
     */
    public function __construct()
    {
        $this->commonQuery = new CommonQuery();
    }

    /**
     * EMSQuery destructor.
     */
    public function __destruct()
    {
    }

    //------------------------------------------------------------------------------------------------------------
    // Login
    //------------------------------------------------------------------------------------------------------------
    /**
     * 로그인 확인
     * @param string $id
     *
     * @return string
     */
    public function getQueryLogin(string $id) : string
    {
        $query = "SELECT `complex`.`fg_mobile`,
                         `adm`.`admin_pk`,
						 `adm`.`admin_id`, 
						 `adm`.`name`, 
						 `adm`.`login_level`, 
						 `adm`.`hp` AS `phone`, 
						 `adm`.`complex_code_pk`,
                         `adm`.`password`,
                         `adm`.`first_login_date`,
                         IFNULL(`adm`.`login_fail_cnt`, 0) AS `login_fail_cnt`
				  FROM `bems_complex` AS `complex`
				  	 LEFT JOIN `bems_admin` AS `adm` 
                        ON `complex`.`complex_code_pk` = `adm`.`complex_code_pk`
				  WHERE `adm`.`admin_id` = '{$id}'
				  AND `complex`.`fg_del` = 'n'
				  AND `adm`.`fg_del` = 'n'
				  AND `adm`.`fg_connect` = 'y'
				 ";

        return $query;
    }

    /**
     * 자동 로그인 확인
     *
     * @param string $id
     * @param string $deviceKey
     * @param string $loginKey
     *
     * @return string
     */
    public function getQueryAutoLogin(string $id, string $deviceKey, string $loginKey) : string
    {
        $query = "SELECT `admin`.`admin_pk`, 
						 `admin`.`admin_id`, 
						 `admin`.`name`, 
						 `admin`.`login_level`, 
						 `admin`.`hp` as `phone`, 
						 `admin`.`complex_code_pk`, 
                         `admin`.`password`,
						 `auto`.`device_key`, 
						 `auto`.`login_key`
					FROM `bems_admin` AS `admin`
						LEFT JOIN `bems_autologin` AS `auto` 
							ON `admin`.`admin_pk` = `auto`.`admin_pk` 
							AND `auto`.`fg_del` = 'n'
					WHERE `admin`.`admin_id` ='{$id}' 
					AND `admin`.`fg_del` = 'n' 
					AND `admin`.`fg_connect` = 'y'
					AND `auto`.`device_key` = '{$deviceKey}' 
					AND `auto`.`login_key` = '{$loginKey}'
				   ";

        return $query;
    }

    /**
     * 최근 접속일 변경
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getQueryUpdateLastLogin(int $adminPk) : string
    {
        $query = "UPDATE `bems_admin` 
                   SET last_login_date = now() 
                   WHERE admin_pk = '{$adminPk}'
                  ";

        return $query;
    }

    /**
     * 자동로그인 추가
     *
     * @param int $adminPk
     * @param string $deviceKey
     * @param string $loginKey
     * @param string $fgMobileWhere
     *
     * @return string
     */
    public function getQueryUpdateAutologin(int $adminPk, string $deviceKey, string $loginKey, string $fgMobileWhere) : string
    {
        $query = "INSERT INTO `bems_autologin`
                    SET `admin_pk` = '{$adminPk}',
                        `device_key` = '{$deviceKey}',
                        `login_key` = '{$loginKey}'
                        {$fgMobileWhere}
                    ON DUPLICATE KEY UPDATE 
                        `admin_pk` = '{$adminPk}',
                        `device_key` = '{$deviceKey}',
                        `login_key` = '{$loginKey}'
                 ";

        return $query;
    }

    /**
     * 자동로그인 해제
     *
     * @param int $adminPk
     * @param string $deviceKey
     * @param string $loginKey
     *
     * @return string
     */
    public function getQueryUpdateAutologinOff(int $adminPk, string $deviceKey, string $loginKey) : string
    {
        $query = "UPDATE bems_autologin 
                   SET fg_del='y' 
                   WHERE admin_pk='{$adminPk}' 
                   AND device_key='{$deviceKey}' 
                   AND login_key='{$loginKey}'
                  ";

        return $query;
    }

    /**
     * 관리자 로그인 시 로그 추가
     *
     * @param string $id
     * @param string $ip
     * @param string $browser
     * @param string $deviceColumn
     * @param string $isSuccess
     *
     * @return string
     */
    public function getQueryLoginLog(string $id, string $ip, string $browser, string $deviceColumn, string $isSuccess) : string
    {
        $query = "INSERT INTO `bems_admin_login_log` 
                   SET `admin_id` = '{$id}',
                       `ip_addr` = '{$ip}',
                       `user_agent` = '{$browser}',
                       `{$deviceColumn}` = '{$isSuccess}'
                  ";

        return $query;
    }

    /**
     * 관리자 비밀번호 조회
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getQuerySelectPassword(int $adminPk) : string
    {
        $query = "SELECT `password`, 
                         `auth_valid_time` 
                  FROM `bems_admin` 
                  WHERE `admin_pk` = '{$adminPk}'
                 ";

        return $query;
    }

    /**
     * 비밀번호 인증번호 받을 때 실제 존재하는 계정인지 체크
     *
     * @param string $name
     * @param string $email
     *
     * @return string
     */
    public function getQuerySelectAdminAccountExist(string $name, string $email) : string
    {
        $query = "SELECT COUNT(`admin_pk`) AS `cnt`,
                         `admin_pk`,
                         `admin_id`,
                         `name`,
                         CASE WHEN `first_login_date` IS NULL THEN 1 ELSE 0 END `is_first_login`
                  FROM `bems_admin`
                  WHERE `email` = '{$email}'
                  AND `name` = '{$name}'
                  -- AND `first_login_date` IS NOT NULL
                 ";

        return $query;
    }

    /**
     * 비밀번호 변경
     *
     * @param int $adminPk
     * @param string $password
     *
     * @return string
     */
    public function getQueryUpdatePassword(int $adminPk, string $password) : string
    {
        $query = "UPDATE `bems_admin` 
                    SET `password` = '{$password}',
                        `updator` = '{$adminPk}',
                        `update_date` = NOW()
                    WHERE `admin_pk` = '{$adminPk}'
                 ";

        return $query;
    }

    /**
     * 비밀번호 찾기 시 발송된 인증번호 저장 / 삭제
     *
     * @param int $adminPK
     * @param string $authNumber
     * @param string $authValidTime
     *
     * @return string
     */
    public function getQueryUpdateAuthNumber(int $adminPK, string $authNumber, string $authValidTime) : string
    {
        $query = "UPDATE `bems_admin`
                    SET `auth_num` = '{$authNumber}',
                        `auth_valid_time` = '{$authValidTime}'
                    WHERE `admin_pk` = '{$adminPK}'
                  ";

        return $query;
    }

    /**
     * 비밀번호 인증 정보 성공 여부 체크
     *
     * @param string $name
     * @param string $email
     *
     * @return string
     */
    public function getQuerySelectAuthSuccessCheck(string $name, string $email) : string
    {
        $query = "SELECT `admin_pk`,
                         `auth_num`, 
                         `auth_valid_time`
                  FROM `bems_admin`
                  WHERE `name` = '{$name}'
                  AND `email` = '{$email}'
                 ";

        return $query;
    }

    /**
     * 인증정보와 로그인 실패 횟수 초기화
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getQueryUpdateAccountDataInitialize(int $adminPk) : string
    {
        $query = "UPDATE `bems_admin`
                   SET `auth_num` = NULL,
                       `login_fail_cnt` = 0,
                       `auth_valid_time` = NULL
                   WHERE `admin_pk` = '{$adminPk}'
                 ";

        return $query;
    }

    /**
     * 로그인 실패횟수 증가
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getQueryUpdateLoginFailCnt(int $adminPk) : string
    {
        $query = "UPDATE `bems_admin`
                   SET `login_fail_cnt` = `login_fail_cnt` + 1
                   WHERE `admin_pk` = '{$adminPk}'
                  ";

        return $query;
    }

    /**
     * 기본 비밀번호 변경 시  현재 날짜로  변경
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getQueryUpdateFirstLoginDate(int $adminPk) : string
    {
        $query = "UPDATE `bems_admin`
                  SET `first_login_date` = NOW()
                  WHERE `admin_pk` = '{$adminPk}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 공통
    //------------------------------------------------------------------------------------------------------------
    /**
     * 단지 코드 조회
     *
     * @return string $query
     */
    public function getQuerySelectComplex() : string
    {
        $query = "SELECT `complex_code_pk` FROM `bems_complex` WHERE `fg_del` = 'n'";

        return $query;
    }

    /**
     * 단지명 조회
     *
     * @param string $complexCode
     *
     * @return string
     */
    public function getComplexNameByCode(string $complexCode) : string
    {
        $query = "SELECT `name`
                  FROM bems_complex
                  WHERE `complex_code_pk` = '{$complexCode}'
                ";

        return $query;
    }

    /**
     * 단지에 상관없이 모든 에너지원 센서번호 조회 (미세먼지 테이블은 참조 안함)
     *
     * @param int $option
     *
     * @return string
     */
    public function getQueryAllSensorData(int $option) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `sensor`.`complex_code_pk`,
                         `sensor`.`sensor_sn`
                  FROM `{$sensorTable}` AS `sensor`
                     LEFT JOIN `bems_complex` AS `complex`
		                ON `sensor`.`complex_code_pk` = `complex`.`complex_code_pk`
                  WHERE `sensor`.`fg_use` = 'y'
                  AND `complex`.`fg_del` = 'n'
                 ";

        return $query;
    }

    /**
     * 모든 층, 룸 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string $query
     */
    public function getQueryComplexInfoAll(string $complexCodePk) : string
    {
        $query = "SELECT `complex`.`home_dong_cnt`,
                         `home`.`home_dong_pk`,
                         `home`.`home_ho_pk`,
                         `home`.`home_ho_nm`,
                         `home`.`home_grp_pk`,
                         CASE WHEN `home`.`home_grp_pk` LIKE 'B%' THEN 1
                              WHEN `home`.`home_grp_pk` = 'PH' THEN 3
                              ELSE 2 END `home_grp_seq` 
                   FROM `bems_complex` AS `complex`
                      LEFT JOIN `bems_home` AS `home`   
                         ON `complex`.`complex_code_pk` = `home`.`complex_code_pk`
                   WHERE `home`.`complex_code_pk`='{$complexCodePk}' 
                   AND `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
                   GROUP BY `home`.`home_dong_pk`, `home`.`home_grp_pk`, `home`.`home_ho_nm`
                   ORDER BY `home`.`home_dong_pk` ASC,  `home_grp_seq` ASC, `home`.`home_grp_pk` * 1 ASC, `home`.`home_ho_pk` ASC
                 ";

        return $query;
    }

    /**
     * 센서정보에 의한 층, 룸 정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $floorString
     *
     * @return string
     */
    public function getQueryBuildingInfoSensor(string $complexCodePk, int $option, string $floorString) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $floorQuery = '';

        if (empty($floorString) === false) {
            $floorString = "AND `home_grp_pk` IN ({$floorString})";
        }

        $query = "SELECT `complex`.`home_dong_cnt`,
                         `home`.`home_dong_pk`,
                         `home`.`home_ho_pk`,
                         `home`.`home_ho_nm`,
                         `home`.`home_grp_pk`,
                         CASE WHEN `home`.`home_grp_pk` LIKE 'B%' THEN 1
                              WHEN `home`.`home_grp_pk` = 'PH' THEN 3
                              ELSE 2 END `home_grp_seq`
				   FROM `bems_complex` AS `complex`
				        LEFT JOIN `bems_home` AS `home`
				            ON `complex`.`complex_code_pk` = `home`.`complex_code_pk`
					    LEFT JOIN `{$sensorTable}` AS `sensor`
						    ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
						    AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
						    AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
				   WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}' 
				   AND `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
                   {$floorString}
                   GROUP BY `home`.`home_dong_pk`, `home`.`home_grp_pk`, `home`.`home_ho_nm`
				   ORDER BY `home`.`home_dong_pk` ASC, `home_grp_seq` ASC, `home`.`home_grp_pk` * 1 ASC, `home`.`home_ho_pk` ASC
				  ";

        return $query;
    }

    /**
     * 모든 동, 호  조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQueryHomeInfoAll(string $complexCodePk) : string
    {
        $query = "SELECT `home_dong_pk`,
                         `home_ho_pk`,
                         `home_key_pk`,
                         `home_type`,
                         `home_ho_nm`
                  FROM `bems_home`
                  WHERE `complex_code_pk` ='{$complexCodePk}'
                  AND `home_grp_pk` NOT IN ('ALL', '0M')
                  ORDER BY `home_type`, `home_dong_pk`,`home_ho_pk` ASC
                 ";

        return $query;
    }

    /**
     * 센서를 기준으로 동,호 정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @return string
     */
    public function getQueryHomeInfoSensor(string $complexCodePk, int $option) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `home`.`home_dong_pk`,
                         `home`.`home_ho_pk`,
                         `home`.`home_type`,
                         `home`.`home_key_pk`,
                         `home`.`home_ho_nm`
                  FROM `bems_home` AS `home`
                    LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')
                  ORDER BY `home`.`home_type` ASC, `home`.`home_dong_pk` ASC, `home`.`home_ho_pk` ASC
                ";

        return $query;
    }

    /**
     * 마감일 조회
     *
     * @param int $option
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getDueday(int $option, string $complexCodePk) : string
    {
        $closingDayColumns = $this->closingDayColumns;
        $col = $closingDayColumns[$option];

        $query = "SELECT IFNULL(`{$col}`, 10) AS `closing_day` 
                  FROM `bems_complex` 
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }

    /**
     * 건축면적, 단위면적 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQueryComplexLandArea(string $complexCodePk) : string
    {
        $query = "SELECT `land_area`,
                         `building_area` 
                  FROM `bems_complex` 
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }

    /**
     * 건물명, 위도,경도 조회
     *
     * @return string
     */
    public function getQueryBemsInfos() :string
    {
        $query = "SELECT `complex_code_pk`,
                         `name`, 
                         `lat`, 
                         `lon` 
                  FROM `bems_complex`
                ";

        return $query;
    }

    /**
     * 기준값 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string|null $column
     *
     * @return string
     */
    public function getQueryReference(string $complexCodePk, int $option, string $column = null) : string
    {
        $limitValColumns = $column;
        if ($column === null) {
            $limitValColumns = $this->limitValColumnsNames[$option];
        }

        $query = "SELECT `{$limitValColumns}` AS `val` 
				  FROM `bems_complex` 
				  WHERE `complex_code_pk` = '{$complexCodePk}'
				";

        return $query;
    }

    /**
     * 기준값 변경
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $value
     * @param string|null $column
     *
     * @return string
     */
    public function updateStandardValue(string $complexCodePk, int $option, string $value, string $column = null) : string
    {
        $limitColumn = $column;
        if ($column === null) {
            $limitColumns = $this->limitValColumnsNames;
            $limitColumn = $limitColumns[$option];
        }

        $query = "UPDATE `bems_complex` SET 
                    `{$limitColumn}` = '{$value}'
                   WHERE `complex_code_pk` = '{$complexCodePk}'
                  ";

        return $query;
    }

    /**
     * 센서 테이블에서 건물코드에 의한 센서번호만 추출 (에너지 센서와 미세먼지 테이블 구분할것- $option, $isUseEnergyTable)
     *
     * @param string $complexCodePk
     * @param bool $isUseEnergyTable
     * @param int $option
     *
     * @return string
     */
    public function getQuerySensorData(string $complexCodePk, bool $isUseEnergyTable = true, int $option = 0) : string
    {
        $column = 'sensor_sn';
        if ($isUseEnergyTable === false) {
            $sensorTable = Config::CO2_TABLE_INFO['sensor']['table'];
            $column = Config::CO2_TABLE_INFO['sensor']['column'];
        } else {
            $sensorTables = $this->sensorTableNames;
            $sensorTable = $sensorTables[$option];
        }

        $query = "SELECT `{$column}` AS `sensor`
                  FROM `{$sensorTable}` 
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * 건물에 층 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQuerySelectFloor(string $complexCodePk) : string
    {
        $query = "SELECT `home_grp_pk`
				  FROM `bems_home`
				  WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `home_grp_pk` NOT IN ('0M', 'ALL')
                  GROUP BY `home_grp_pk`
				  ORDER BY `home_grp_pk` ASC
				 ";

        return $query;
    }

    /**
     * 건물에 층 정보 조회 (센서테이블을 기준으로 조회)
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @return string
     */
    public function getQuerySelectFloorBySensor(string $complexCodePk, int $option) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `home`.`home_grp_pk`
                  FROM `bems_home` AS `home`
                     INNER JOIN `{$sensorTable}` AS `sensor`
                        on `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        and `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        and `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `home`.`complex_code_pk` = '{$complexCodePk}'
                  AND `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
                  GROUP BY `home`.`home_grp_pk`
                  ORDER BY `home`.`home_grp_pk` ASC
                 ";

        return $query;
    }

    /**
     * 월통계 테이블에서 해당 월에 대한 마감일 시작일 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $ym
     *
     * @return string
     */
    public function getQuerySelectStartDueDate(string $complexCodePk, int $option, string $ym) : string
    {
        $monthTables = $this->monthTableNames;
        $sensorTables = $this->sensorTableNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];

        $query = "SELECT `month`.`st_date`
                  FROM `{$sensorTable}` AS `sensor`
                    LEFT JOIN `{$monthTable}` AS `month`
                       ON `sensor`.`sensor_sn` = `month`.`sensor_sn`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `month`.`ym` = '{$ym}'
                  GROUP BY `month`.`ym`
                 ";

        return $query;
    }

    /**
     * 한전 요금 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQuerySelectComplexPriceInfo(string $complexCodePk) : string
    {
        $query = "SELECT `electricType`,
                         `typeGubun`,
                         `typeGubun2`,
                         `typeSelect`,
                         `contractUseVal`
                  FROM `bems_complex`
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }

    /**
     * 이상증후 상태 변경
     *
     * @param int $option
     * @param string $sensorNo
     * @param int $anomaly
     * @param float $anomalyScore
     *
     * @return string
     */
    public function getQueryUpdateAnomalyData(int $option, string $sensorNo, int $anomaly, float $anomalyScore) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "UPDATE `{$sensorTable}` 
                  SET `fg_anomaly` = '{$anomaly}',
                      `anomaly_score` = '{$anomalyScore}'
                  WHERE `sensor_sn` = '{$sensorNo}'
                 ";

        return $query;
    }

    /**
     * 서비스 시작일 조회
     * 
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQuerySelectServiceStartDate(string $complexCodePk) : string
    {
        $query = "SELECT `service_start_date`
                  FROM `bems_complex`
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }

    /**
     * insert, update 를 여러개 실행하는 쿼리
     *
     * @param int $option
     * @param array $insertColumns
     * @param array $updateColumns
     * @param string $insertValueStr
     *
     * @return string
     */
    public function getQueryInsertOrUpdateMulti(int $option, array $insertColumns, array $updateColumns, string $insertValueStr) : string
    {
        $rawTables = $this->rawTableNames;
        $rawTable = $rawTables[$option];

        $insertColumnStr = Utility::getInstance()->makeImplodeString(
            Utility::getInstance()->addCharacter($insertColumns, "`")
        );

        $updateColumns = Utility::getInstance()->addCharacter($updateColumns, "`");

        $query = "INSERT INTO `{$rawTable}` ({$insertColumnStr}) VALUES
                  {$insertValueStr}
                  ON DUPLICATE KEY UPDATE 
                 ";

        $updateColumnStr = "";

        for ($i = 0; $i < count($updateColumns); $i++) {
            $k = $updateColumns[$i];

            if (empty($updateColumnStr) === false) {
                $updateColumnStr .= ",";
            }

            $updateColumnStr .= "{$k} = VALUES({$k})";
        }

        $query .= $updateColumnStr;

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 통계 데이터 생성 (효율)
    //------------------------------------------------------------------------------------------------------------
    /**
     * 일 통계 데이터 카운트 조회
     *
     * @param int $option
     * @param string $date
     *
     * @return string
     */
    public function getQuerySelectEfficiencyCountDailyData(int $option, string $date) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $dayTableNames = $this->dayTableNames;
        $dayTable = $dayTableNames[$option];

        $query = "SELECT COUNT(`sensor`.`sensor_sn`) AS `count`
                  FROM `{$sensorTable}` AS `sensor`
                       LEFT JOIN `{$dayTable}` AS `daily`
                            ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                  WHERE `sensor`.`maker` = 'ntek'
                  AND `daily`.`val_date` = '{$date}'
                 ";

        return $query;
    }

    /**
     * 현재 시간 데이터 추출
     *
     * @param int $option
     * @param string $dateHour
     *
     * @return string
     */
    public function getQuerySelectEfficiencyCurrentTime(int $option, string $dateHour) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;

        $rawTable = $rawTables[$option];
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `home`.`complex_code_pk`, 
                         `sensor`.sensor_sn,
                         SUBSTR(`meter`.`val_date`, 1, 8) AS `date`,
                         SUBSTR(`meter`.`val_date`, 9, 2) AS `hour`,
                         IFNULL(AVG(`meter`.`pf`), 0) AS `val`
                  FROM `bems_home` AS `home`
                    LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                    LEFT JOIN `{$rawTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `sensor`.`maker` = 'ntek'
                  AND `meter`.`val_date` >= '{$dateHour}0000'
                  AND `meter`.`val_date` <= '{$dateHour}5959'
                  GROUP BY `meter`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * 시간 데이터 일통계 테이블에 업데이트
     *
     * @param int $option
     * @param string $date
     * @param string $sensorNo
     * @param array $columnInfo
     *
     * @return string
     */
    public function getQueryUpdateEfficiencyTime(int $option, string $date, string $sensorNo, array $columnInfo) : string
    {
        $dayTables = $this->dayTableNames;
        $dayTable = $dayTables[$option];

        $columnStr = "";
        foreach ($columnInfo AS $column => $value) {
            if (empty($columnStr) === true) {
                $columnStr .= "`{$column}` = '{$value}'";
            } else {
                $columnStr .= ",`{$column}` = '{$value}'";
            }
        }

        $query = "UPDATE `{$dayTable}`
                  SET {$columnStr}
                  WHERE `val_date` = '{$date}'
                  AND `sensor_sn` = '{$sensorNo}'
                  ";

        return $query;
    }

    /**
     * 시간 데이터 일통계 테이블에 추가
     *
     * @param int $option
     * @param string $date
     * @param string $sensorNo
     * @param array $columnInfo
     *
     * @return string
     */
    public function getQueryInsertEfficiencyTime(int $option, string $date, string $sensorNo, array $columnInfo) : string
    {
        $dayTables = $this->dayTableNames;
        $dayTable = $dayTables[$option];

        $query = "INSERT INTO `{$dayTable}`
                    SET `val_date` = '{$date}',
                        `sensor_sn` = '{$sensorNo}'
                  ";

        foreach ($columnInfo AS $column => $value) {
            $query .= ", `{$column}` = '{$value}'";
        }

        return $query;
    }

    /**
     * 일통계 조회 (1일 시간대별 평균값 조회)
     *
     * @param int $option
     * @param int $day
     *
     * @return string $query
     */
    public function getQueryEfficiencyAvgDayData(int $option, int $day) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];

        $query = "SELECT `sensor`.`complex_code_pk`,
                         `meter`.`sensor_sn`, 
						 SUBSTRING(`meter`.`val_date`, 1, 10) AS `val_date`, 
						 IFNULL(AVG(`meter`.`pf`), 0) AS `val`
				  FROM `{$sensorTable}` AS `sensor`
				     LEFT JOIN `{$rawTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
				  WHERE `sensor`.`maker` = 'ntek'
				  AND `meter`.`val_date` LIKE '{$day}%'
				  GROUP BY `meter`.`sensor_sn`, SUBSTRING(`meter`.`val_date`, 1, 10)
				  ORDER BY `meter`.`sensor_sn`, SUBSTRING(`meter`.`val_date`, 1, 10)
				";

        return $query;
    }

    /**
     * 통계 조회 (1일 평균값)
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryEfficiencyAvgSumData(string $complexCodePk, int $option, string $date, string $sensorQuery) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;

        $rawTable = $rawTables[$option];
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `meter`.`sensor_sn`,
						 IFNULL(MIN(`meter`.`pf`), 0) AS `val_st`,
						 IFNULL(MAX(`meter`.`pf`), 0) AS `val_ed`,
						 IFNULL(AVG(`meter`.`pf`), 0) AS `val`
				  FROM `{$sensorTable}` AS `sensor`
					INNER JOIN `{$rawTable}` AS `meter`
						ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
				  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
				  AND `sensor`.`maker` = 'ntek'
				  AND `meter`.`val_date` >= '{$date}000000'
				  AND `meter`.`val_date` <= '{$date}235959'
                  {$sensorQuery}
				  GROUP BY `sensor`.`sensor_sn`
				";

        return $query;
    }

    /**
     * 일 통계 데이터 추가
     *
     * @param int $option
     * @param int $day
     * @param array $info
     *
     * @return string
     */
    public function getQueryUpdateOrInsertEfficiencyDay(int $option, int $day, array $info) : string
    {
        $dayTables = $this->dayTableNames;
        $dayTable = $dayTables[$option];

        $insTables = "";
        $insValue  = "";
        $updValue  = "";

        foreach ($info as $key => $value) {
            $insTables = $insTables.",".$key;
            $insValue  = $insValue.","."'{$value}'";
            $temp = "{$key}='{$value}'";
            $updValue  = $updValue.",".$temp;
        }

        $query = "INSERT INTO `{$dayTable}` (`val_date` {$insTables}) VALUES ('{$day}' {$insValue})
		ON DUPLICATE KEY UPDATE val_date='{$day}' {$updValue}";

        return $query;
    }

    /**
     * 월 통계(1달- 마감일 다음날 ~ 다음달 마감일까지) 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $start
     * @param string $end
     *
     * @return string $query
     */
    public function getQueryEfficiencyAvgMonthData(string $complexCodePk, int $option, string $start, string $end) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;

        $rawTable = $rawTables[$option];
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `meter`.`sensor_sn`,
						 IFNULL(MIN(`meter`.`pf`), 0) AS `val_st`,
						 IFNULL(MAX(`meter`.`pf`), 0) AS `val_ed`,
						 IFNULL(AVG(`meter`.`pf`), 0) AS `val`
				  FROM `{$sensorTable}` AS `sensor`
					INNER JOIN `{$rawTable}` AS `meter`
						ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
				  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'  
				  AND `meter`.`val_date` >= '{$start}000000'
				  AND `meter`.`val_date` <= '{$end}235959'
				  GROUP BY `sensor`.`sensor_sn`
				";

        return $query;
    }

    /**
     * 월 통계 데이터 추가
     *
     * @param int $option
     * @param string $start
     * @param string $end
     * @param string $sensorNo
     * @param int $val
     * @param int $valSt
     * @param int $valEd
     * @param int $dueday
     *
     * @return string
     */
    public function getQueryUpdateOrInsertEfficiencyMonth(int $option, string $start, string $end, string $sensorNo, int $val, int $valSt, int $valEd, int $dueday) : string
    {
        $monthTables = $this->monthTableNames;
        $monthTable = $monthTables[$option];

        $ym = substr($end, 0, 6);

        $query = "INSERT INTO {$monthTable} (sensor_sn, ym, st_date, ed_date, closing_day, efficiency_st, efficiency_ed, efficiency) 
                  VALUES('{$sensorNo}', '{$ym}', '{$start}', '{$end}', '{$dueday}', '{$valSt}', '{$valEd}', '{$val}') 
                  ON DUPLICATE KEY UPDATE 
                    `sensor_sn` = '{$sensorNo}', 
                    `ym` = '{$ym}', 
                    `st_date` = '{$start}', 
                    `ed_date` = '{$end}', 
                    `closing_day` = '{$dueday}', 
                    `efficiency_st` = '{$valSt}', 
                    `efficiency_ed` = '{$valEd}', 
                    `efficiency` = '{$val}', 
                    `update_date` = now()
                  ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 통계 데이터 생성 (미세먼지 제외)
    //------------------------------------------------------------------------------------------------------------
    /**
     * 현재 시간 데이터 추출
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $dateHour
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQuerySelectCurrentTime(string $complexCodePk, int $option, string $dateHour, string $sensorNo) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;
        $columns = $this->columnNames;

        $rawTable = $rawTables[$option];
        $sensorTable = $sensorTables[$option];
        $column = $columns[$option];

        $query = "SELECT `sensor`.`sensor_sn`,
                         CASE WHEN IFNULL(MIN(`meter`.`{$column}`), 0) = 0 
                              THEN 0 
                              ELSE IFNULL(MAX(`meter`.`{$column}`) - MIN(`meter`.`{$column}`), 0) 
                              END `val`,
                         IFNULL(MAX(`meter`.`{$column}`), 0) AS `total_val`
                  FROM `bems_home` AS `home`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`        
                      LEFT JOIN `{$rawTable}` AS `meter`
                         ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}' 
                  AND `meter`.`collect_type` IS NULL  
                  AND `meter`.`sensor_sn` = '{$sensorNo}' 
                  AND `meter`.`val_date` >= '{$dateHour}0000'
                  AND `meter`.`val_date` <= '{$dateHour}5959'
                  GROUP BY `meter`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * 일통계 조회 (1일 시간대별 조회)
     *
     * @param int $option
     * @param string $day
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQueryMeterDayData(int $option, string $day, string $sensorNo) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;
        $cols = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];

        $query = "SELECT `sensor`.`complex_code_pk`,
                         `meter`.`sensor_sn`,
                         SUBSTRING(`meter`.`val_date`, 1, 8) AS `val_date`, 
                         SUBSTRING(`meter`.`val_date`, 9, 2) AS `hour`,
                         CASE WHEN IFNULL(MIN(`meter`.`{$col}`), 0) = 0 
                              THEN 0 
                              ELSE IFNULL(MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`), 0) 
                              END `val`, 
                         IFNULL(MAX(`meter`.`{$col}`), 0) AS `maxval`,
                         IFNULL(MIN(`meter`.`{$col}`), 0) AS `minval`
                  FROM `bems_home` AS `home`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk` 
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`  
                      LEFT JOIN `{$rawTable}` AS `meter`
                         ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `meter`.`val_date` >= '{$day}000000'
                  AND `meter`.`val_date` <= '{$day}235959'
                  GROUP BY `meter`.sensor_sn, SUBSTRING(`meter`.val_date, 1, 10)
                  ORDER BY `meter`.sensor_sn, SUBSTRING(`meter`.val_date, 1, 10)
                 ";

        return $query;
    }

    /**
     * 통계 조회 (1일 사용량 합계)
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQueryMeterDaySumData(string $complexCodePk, int $option, string $startDate, string $endDate, string $sensorNo) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;
        $cols = $this->columnNames;

        $rawTable = $rawTables[$option];
        $sensorTable = $sensorTables[$option];
        $col = $cols[$option];

        $startDateValue = strlen($startDate) === 14 ? $startDate : "{$startDate}000000";

        $query = "SELECT `meter`.`sensor_sn`,
						 IFNULL(MIN(`meter`.`{$col}`), 0) AS `val_st`,
						 IFNULL(MAX(`meter`.`{$col}`), 0) AS `val_ed`,
						 IFNULL(MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`), 0) AS `val`
				  FROM `bems_home` AS `home`
				     LEFT JOIN `{$sensorTable}` AS `sensor`
				        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
				        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
				        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
					 LEFT JOIN `{$rawTable}` AS `meter`
						ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
				  WHERE `sensor`.`fg_use` = 'y' 
				  AND `sensor`.`complex_code_pk` = '{$complexCodePk}' 
				  AND `meter`.`collect_type` IS NULL  
				  AND `meter`.`sensor_sn` = '{$sensorNo}'
				  AND `meter`.`val_date` >= '{$startDateValue}'  
				  AND `meter`.`val_date` <= '{$endDate}235959'
				  GROUP BY `sensor`.`sensor_sn`
				";

        return $query;
    }

    /**
     * 월 통계 미터 조회- 센서 테이블과 월 통계 테이블을 이용하여 조회
     * 계산식 = 최근사용량 - 전월사용량
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $previousMonth
     * @param string $startDate
     * @param string $endDate
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQueryMonthDataBySensorTable(string $complexCodePk, int $option, string $previousMonth, string $startDate, string $endDate, string $sensorNo) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;
        $rawTables = $this->rawTableNames;
        $columns = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];
        $rawTable = $rawTables[$option];
        $column = $columns[$option];

        $query = "SELECT `sensor`.`sensor_sn`,
                         IFNULL(`month`.`val_ed`, 0) AS `val_st`,
                         IFNULL(MIN(`meter`.`{$column}`), 0) AS `min_val`, 
                         IFNULL(MAX(`meter`.`{$column}`), 0) AS `val_ed`,
                         IFNULL(MAX(`meter`.`{$column}`) - `month`.`val_ed`, 0) AS `val`
                  FROM `bems_home` AS `home`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                     LEFT JOIN `{$rawTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                     LEFT JOIN `{$monthTable}` AS `month`
                        ON `sensor`.`sensor_sn` = `month`.`sensor_sn`
                        AND `month`.`ym` = '{$previousMonth}'
                        AND `month`.`sensor_sn` = '{$sensorNo}'
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `meter`.`collect_type` IS NULL  
                  AND `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `meter`.`val_date` >= '{$startDate}000000'
                  AND `meter`.`val_date` <= '{$endDate}235959'  
                  GROUP BY `sensor`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * 월 통계(1달- 마감일 다음날 ~ 다음달 마감일까지) 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQueryMeterMonthData(string $complexCodePk, int $option, string $startDate, string $endDate, string $sensorNo) : string
    {
        $rawTables = $this->rawTableNames;
        $sensorTables = $this->sensorTableNames;
        $cols = $this->columnNames;

        $rawTable = $rawTables[$option];
        $sensorTable = $sensorTables[$option];
        $col = $cols[$option];

        if (strlen($startDate) === 8) {
            $startDate .= '000000';
        }

        $query = "SELECT `meter`.`sensor_sn`,
						 IFNULL(MIN(`meter`.`{$col}`), 0) AS `val_st`,
						 IFNULL(MAX(`meter`.`{$col}`), 0) AS `val_ed`,
						 IFNULL(MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`), 0) AS `val`
				  FROM `bems_home` AS `home`
				      LEFT JOIN `{$sensorTable}` AS `sensor`
				         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
					  LEFT JOIN `{$rawTable}` AS `meter`
					     ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
				  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}' 
				  AND `meter`.`collect_type` IS NULL  
				  AND `meter`.`sensor_sn` = '{$sensorNo}'
				  AND `meter`.`val_date` >= '{$startDate}'
				  AND `meter`.`val_date` <= '{$endDate}235959'
				  GROUP BY `sensor`.`sensor_sn`
				";

        return $query;
    }

    /**
     * 월 통계 데이터 추가
     *
     * @param int $option
     * @param string $start
     * @param string $end
     * @param string $sensorNo
     * @param string $dueDate
     * @param array $useData
     *
     * @return string
     */
    public function getQueryUpdateOrInsertMeterMonth(int $option, string $start, string $end, string $sensorNo, string $dueDate, array $useData) : string
    {
        $monthTables = $this->monthTableNames;
        $monthTable = $monthTables[$option];

        $ym = substr($end, 0, 6);

        $insTables = '';
        $insValue = '';
        $updValue = '';

        foreach ($useData as $key => $value) {
            $insTables = $insTables . "," . $key;
            $insValue = $insValue . "," . "'{$value}'";
            $temp = "{$key}='{$value}'";
            $updValue = $updValue . "," . $temp;
        }

        $query = "INSERT INTO ${monthTable} (sensor_sn, ym, st_date, ed_date, closing_day, reg_date {$insTables}) 
                  VALUES('{$sensorNo}', '{$ym}', '{$start}', '{$end}', '{$dueDate}', NOW() {$insValue}) 
                  ON DUPLICATE KEY UPDATE 
                    sensor_sn = '{$sensorNo}', 
                    ym = '{$ym}', 
                    st_date = '{$start}', 
                    ed_date = '{$end}', 
                    closing_day = '{$dueDate}',
                    reg_date = NOW() {$updValue}
                  ";

        return $query;
    }

    /**
     * 일 통계 데이터 추가
     *
     * @param int $option
     * @param int $day
     * @param array $info
     *
     * @return string
     */
    public function getQueryUpdateOrInsertMeterDay(int $option, int $day, array $info) : string
    {
        $dayTables = $this->dayTableNames;
        $dayTable = $dayTables[$option];

        $insTables = '';
        $insValue  = '';
        $updValue  = '';

        foreach ($info as $key => $value) {
            $insTables = $insTables.",".$key;
            $insValue= $insValue.","."'${value}'";
            $temp = "${key}='${value}'";
            $updValue = $updValue.",".$temp;
        }

        $query = "INSERT INTO {$dayTable} (val_date, reg_date {$insTables}) VALUES ('{$day}', NOW() {$insValue})
		ON DUPLICATE KEY UPDATE reg_date = NOW(), val_date='{$day}' {$updValue}";

        return $query;
    }

    /**
     * 일통계 생성 시 사용 - 마감일부터 다음달 마감일까지 사용량 조회
     *
     * @param string $date
     * @param string $closingDate
     * @param string $sensorSn
     *
     * @return string
     */
    public function getQueryElectricUsedForMonth(string $date, string $closingDate, string $sensorSn) : string
    {
        $query = "select meter.sensor_sn, IFNULL(
						SUBSTRING_INDEX( GROUP_CONCAT(CAST(meter.total_wh AS CHAR) ORDER BY meter.val_date DESC), ',', 1) -
						SUBSTRING_INDEX( GROUP_CONCAT(CAST(meter.total_wh AS CHAR) ORDER BY meter.val_date ASC), ',', 1)
						, 0) AS val
						from  bems_meter_electric meter
						WHERE meter.sensor_sn='${sensorSn}' AND meter.val_date >= '${closingDate}' AND meter.val_date <= '${date}'
						group by meter.sensor_sn";

        return $query;
    }

    /**
     * bems_meter_ 데이터 추가 (계산해서 meter를 넣는 경우, ntek에 해당하지 않음)
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param int $val
     *
     * @return string $query
     */
    public function getQueryInsertMeterTable(int $option, string $sensorNo, string $valDate, int $val) : string
    {
        $rawTables = $this->rawTableNames;
        $rawTable = $rawTables[$option];

        $query = "INSERT INTO `{$rawTable}` (sensor_sn, val_date, total_wh, error_code) VALUES('{$sensorNo}', '{$valDate}', '{$val}', 0) ON DUPLICATE KEY UPDATE sensor_sn = '{$sensorNo}', val_date = '{$valDate}', total_wh = '{$val}', error_code=0";

        return $query;
    }

    /**
     * bems_sensor_ 업데이트
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $complexCodePk
     * @param string $dong
     * @param string $ho
     * @param string $valDate
     * @param int $val
     *
     * @return string $query
     */
    public function getQueryUpdateSensorTable(int $option, string $sensorNo, string $complexCodePk, string $dong, string $ho, string $valDate, int $val) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "UPDATE `{$sensorTable}` SET val_date = '{$valDate}', val = '{$val}' WHERE sensor_sn = '{$sensorNo}' AND complex_code_pk = '{$complexCodePk}' AND home_dong_pk = '{$dong}' AND home_ho_pk = '{$ho}'";

        return $query;
    }

    /**
     * 미터 데이터를 추가 하기 위해 센서 사용량 조회 (전열)
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sensorNo
     *
     * @return string $query
     */
    public function getQueryCurrentMeterUsedBySensor(string $complexCodePk, int $option, string $sensorNo) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT IFNULL(`val`, 0) AS `val`
                  FROM `{$sensorTable}`
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `sensor_sn` = '{$sensorNo}'
                 ";

        return $query;
    }

    /**
     * 시간 데이터 일통계 테이블에 추가
     *
     * @param int $option
     * @param string $date
     * @param string $sensorNo
     * @param array $columnInfo
     *
     * @return string
     */
    public function getQueryInsertMeterTime(int $option, string $date, string $sensorNo, array $columnInfo) : string
    {
        $columnStr = '';

        $dayTables = $this->dayTableNames;
        $dayTable = $dayTables[$option];

        $query = "INSERT INTO `{$dayTable}`
                    SET `val_date` = '{$date}',
                        `sensor_sn` = '{$sensorNo}'
                  ";

        foreach ($columnInfo AS $column => $value) {
            $columnStr .= ", `{$column}` = '{$value}'";
        }

        $query .= "{$columnStr} 
                   ON DUPLICATE KEY UPDATE 
                   `val_date` = '{$date}', 
                   `sensor_sn` = '{$sensorNo}' 
                   {$columnStr}
                  ";

        return $query;
    }

	/**
     * 계측기 초기화 되었는지 확인
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sensorNo
     * @param string $startDate
     * @param string $endDate
     *
     * @return string $query
     */
    public function getQuerySelectSensorReplaceDate(string $complexCodePk, int $option, string $sensorNo, string $startDate, string $endDate) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $columns = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $column = $columns[$option];

        $query = "SELECT DATE_FORMAT(MAX(`meter`.`val_date`), '%Y%m%d%H%i%s') AS `replace_date`
                  FROM `bems_home` AS `home`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                      LEFT JOIN `{$rawTable}` AS `meter`
                         ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `sensor`.`fg_use` = 'y' 
                  AND `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `meter`.`collect_type` IS NULL  
                  AND `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `meter`.`val_date` >= '{$startDate}000000'
                  AND `meter`.`val_date` <= '{$endDate}235959'
                  AND `meter`.`{$column}` = 0
                  GROUP BY `meter`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * 예측 정보 업데이트
     *
     * @param int $option
     * @param string $previousDate
     * @param string $sensorNo
     * @param string $previousColumn
     * @param int $previousUsed
     *
     * @return string
     */
    public function getQueryUpdatePredict(int $option, string $previousDate, string $sensorNo, string $previousColumn, int $previousUsed) : string
    {
        $dayTableNames = $this->dayTableNames;
        $dayTable = $dayTableNames[$option];

        $query = "UPDATE `{$dayTable}`
                  SET `{$previousColumn}` = '{$previousUsed}'
                  WHERE `val_date` = '{$previousDate}'
                  AND `sensor_sn` = '{$sensorNo}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 통계 데이터 생성  (경부하,중부하,최대부하)
    //------------------------------------------------------------------------------------------------------------
    /**
     * 일통계 부하량을 계산을 위한 하당 센서번호 조회
     *
     * @param int $option
     * @param string $date
     *
     * @return string
     */
    public function getQuerySelectDailySensorData(int $option, string $date) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTableNames = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTableNames[$option];

        $query = "SELECT `sensor`.`complex_code_pk`,
                         `daily`.`sensor_sn`,
                         `daily`.`val_date`
                  FROM `bems_complex` AS `complex`
                      LEFT JOIN `bems_home` AS `home`   
                          ON `complex`.`complex_code_pk` = `home`.`complex_code_pk`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND  `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                      LEFT JOIN `{$dayTable}` AS `daily`
                         ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                  WHERE `daily`.`val_date` = '{$date}'
                  AND `complex`.`electricType` IN ('N','S')
                  AND `complex`.`typeGubun` IN ('type2', 'type3')
                  AND `complex`.`fg_del` = 'n'
                  AND `sensor`.`fg_use` = 'y'
                  ORDER BY `sensor`.`complex_code_pk` ASC
                 ";

        return $query;
    }

    /**
     * 시간대별로 RAW DATA 데이터 검새  => 키 값 주의 할 것
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $startDateTime
     * @param string $endDateTime
     *
     * @return string
     */
    public function getQuerySelectStatusRawData(int $option, string $sensorNo, string $startDateTime, string $endDateTime) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $columnNames = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $column = $columnNames[$option];

        $query = "SELECT MAX(`meter`.`{$column}`) - MIN(`meter`.`{$column}`) AS `val`
                  FROM `bems_home` AS `home`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk` 
                     LEFT JOIN `{$rawTable}` AS `meter`
                        ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `meter`.`sensor_sn` = '{$sensorNo}'
                  AND `meter`.`val_date` >= '{$startDateTime}'
                  AND `meter`.`val_date` <= '{$endDateTime}'
                 ";

        return $query;
    }

    /**
     * 경부하, 중부하, 최대부하 일 데이터 생성
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $date
     * @param int $lowStatus
     * @param int $midStatus
     * @param int $maxStatus
     *
     * @return string
     */
    public function getQueryUpdateDailyStatusType(int $option, string $sensorNo, string $date, int $lowStatus, int $midStatus, int $maxStatus) : string
    {
        $dayTableNames = $this->dayTableNames;
        $dayTable = $dayTableNames[$option];

        $query = "UPDATE `{$dayTable}` 
                  SET `low_status` = '{$lowStatus}',
                      `mid_status` = '{$midStatus}',
                      `max_status` = '{$maxStatus}'
                  WHERE `val_date` = '{$date}'
                  AND `sensor_sn` = '{$sensorNo}'
                 ";

        return $query;
    }

    /**
     * 생성된 월 데이터 조회
     *
     * @param int $option
     * @param string $ym
     * @param string $date
     *
     * @return string
     */
    public function getQuerySelectStatusMonthData(int $option, string $date) : string
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
                  WHERE `month`.`ed_date` = '{$date}'
                  ORDER BY `home`.`complex_code_pk` ASC
                 ";

        return $query;
    }

    /**
     * 경부하, 중부하, 최대부하 월통계 생성을 위한 조회
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $startDate
     * @param string $endDate
     *
     * @return string
     */
    public function getQuerySelectDayStatusSumData(int $option, string $sensorNo, string $startDate, string $endDate) : string
    {
        $sensorTableNames = $this->sensorTableNames;
        $sensorTable = $sensorTableNames[$option];

        $dayTableNames = $this->dayTableNames;
        $dayTable = $dayTableNames[$option];

        $query = "SELECT IFNULL(SUM(`daily`.`low_status`), 0) AS `low_status`,
                         IFNULL(SUM(`daily`.`mid_status`), 0) AS `mid_status`,
                         IFNULL(SUM(`daily`.`max_status`), 0) AS `max_status`
                  FROM `bems_home` AS `home`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` =  `sensor`.`home_ho_pk`
                      LEFT JOIN `{$dayTable}` AS `daily`
                         ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                  WHERE `daily`.`sensor_sn` = '{$sensorNo}' 
                  AND `daily`.`val_date` >= '{$startDate}'
                  AND `daily`.`val_date` <= '{$endDate}'
                  GROUP BY `daily`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * 경부하, 중부하, 최대부하 월 데이터 생성
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $ym
     * @param int $lowStatus
     * @param int $midStatus
     * @param int $maxStatus
     *
     * @return string
     */
    public function getQueryUpdateMonthStatusType(int $option, string $sensorNo, string $ym, int $lowStatus, int $midStatus, int $maxStatus) : string
    {
        $monthTableNames = $this->monthTableNames;
        $monthTable = $monthTableNames[$option];

        $query = "UPDATE `{$monthTable}`
                  SET `low_status` = '{$lowStatus}',
                      `mid_status` = '{$midStatus}',
                      `max_status` = '{$maxStatus}'
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `ym` = '{$ym}'
                ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // Weather
    //------------------------------------------------------------------------------------------------------------
    /**
     * 날씨 정보 추가
     *
     * @param string $tempHour
     * @param string $humiHour
     * @param string $complex_pk
     * @param string $name
     * @param string $valDate
     * @param string $temp_humi
     * @param string $updateTempHumi
     *
     * @return string
     */
    public function getQueryWeatherInfos(string $tempHour, string $humiHour, string $complex_pk, string $name, string $valDate, string $temp_humi, string $updateTempHumi) : string
    {
        $query = "INSERT INTO bems_weather (complex_code_pk, name, val_date, ${tempHour}, ${humiHour}) VALUES ('${complex_pk}', '${name}', '${valDate}', ${temp_humi}) ON DUPLICATE KEY UPDATE  ${updateTempHumi}";

        return $query;
    }

    /**
     * 일출,일몰 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQuerySunRiseSet(string $complexCodePk) : string
    {
        $date = date('Ymd');

        $query = "SELECT date_format(sunrise, '%H:%i') AS sunrise, 
                         date_format(sunset, '%H:%i') AS sunset
		          FROM `bems_weather`
                  WHERE val_date = '{$date}' 
                  AND complex_code_pk='{$complexCodePk}'
                 ";

        return $query;
    }

    /**
     * 온도, 습도 정보 조회 (외부 사이트  API)
     *
     * @param string $complexCodePk
     * @param string $date
     * @param string $hour
     *
     * @return string
     */
    public function getQueryTempHumiCurrent(string $complexCodePk, string $date, string $hour) : string
    {
        $temperatureColumn = 'temperature_' . $hour;
        $humidityColumn = 'humidity_' . $hour;
        $weatherColumn = 'weather_' . $hour;

        $query = "SELECT IFNULL(`{$temperatureColumn}`,'-') AS temp,
						 IFNULL(`{$humidityColumn}`,'-') AS humi,
						 IFNULL(`{$weatherColumn}`,'-') AS weat
				  FROM `bems_weather` 
				  WHERE complex_code_pk='{$complexCodePk}' 
				  AND val_date='{$date}'
				 ";

        return $query;
    }

    /**
     * 날씨 외부 API 정보 추가 (온도,습도 등)
     *
     * @param String $complexCodePk
     * @param String $tempCur
     * @param String $humiCur
     * @param String $sunRise
     * @param String $sunSet
     * @param String $weatherCur
     *
     * @return string
     */
    public function getQueryUpdateCurrentTempHumi(String $complexCodePk, String $tempCur, String $humiCur, String $sunRise, String $sunSet, String $weatherCur) : string
    {
        $hour = date('H');
        //$hour = (int)($hour - ($hour % 3));

        //if ($hour < 10) {
        //   $hour = '0' . $hour;
        //}
        //if ($hour != '-') {
        //	$hour .= "%";
        //}, '${sunSet}'

        $today = date("Ymd");

        $query = "INSERT INTO bems_weather (complex_code_pk, val_date, temperature_${hour}, humidity_${hour}, sunrise, sunset, weather_${hour}) VALUES ('{$complexCodePk}', '{$today}', '{$tempCur}', '{$humiCur}', '{$sunRise}', '{$sunSet}','{$weatherCur}') ON DUPLICATE KEY UPDATE complex_code_pk = '{$complexCodePk}', val_date = '{$today}', temperature_{$hour} = '{$tempCur}', humidity_{$hour} = '{$humiCur}', sunrise = '{$sunRise}', sunset = '{$sunSet}', weather_{$hour} = '{$weatherCur}'";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 사용량 검색
    //------------------------------------------------------------------------------------------------------------
    /**
     * 년도 조회 (1년 단위 | 월통계 조회 | 금월 이전 조회 | 마감일 기준 검색)
     *
     * @param int $option
     * @param string $date
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
    public function getQueryEnergyYearData(int $option, string $date, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];

        $query = "SELECT IFNULL(`ym`, '000000') AS `ym`,
						 IFNULL(SUM(`month`.`val`), 0) AS `val` 
				  FROM `bems_home` AS `home`
					LEFT JOIN `{$sensorTable}` AS `sensor`
						ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
						AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
						AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
					LEFT JOIN `{$monthTable}` AS `month` 
						ON `sensor`.`sensor_sn` = `month`.`sensor_sn` 
				  WHERE `sensor`.`fg_use` = 'y'
				  AND `month`.`ym` LIKE '{$date}%'
				  {$complexQuery}
				  {$dongQuery}      
				  {$floorQuery}
				  {$roomQuery}
				  {$sensorQuery}
				  {$solarQuery}
                  {$equipmentQuery}
				  GROUP BY `month`.`ym`
				";

        return $query;
    }

    /**
     * 년도 조회 (1년 단위 | 미터 조회 | 금월 조회 | 마감일 기준 검색)
     *
     * @param int $option
     * @param string $start
     * @param string $end
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return string
     */
    public function getQueryEnergyCurrentYearData(int $option, string $start, string $end, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $cols = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];

        $query = " SELECT SUBSTR(`aa`.`val_date`, 1, 6) AS `ym`,
                          SUM(`aa`.`{$col}`) AS `val`
                    FROM (SELECT MAX(`meter`.`val_date`) AS `val_date`,
                                 MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`) AS `{$col}`
                          FROM `bems_home` AS `home`
                                INNER JOIN `{$sensorTable}` AS `sensor`
                                    ON `sensor`.`complex_code_pk` = `home`.`complex_code_pk`
                                    AND `sensor`.`home_dong_pk` = `home`.`home_dong_pk`
                                    AND `sensor`.`home_ho_pk` = `home`.`home_ho_pk`
                                LEFT JOIN `{$rawTable}` AS `meter`
                                    ON `meter`.`sensor_sn` = `sensor`.`sensor_sn`
                          WHERE `sensor`.`fg_use` = 'y' 
                          AND `meter`.`val_date` >= '{$start}000000'
                          AND `meter`.`val_date` <= '{$end}235959'
                          {$complexQuery}
						  {$floorQuery}
						  {$roomQuery}
						  {$sensorQuery}
                          {$solarQuery}
                          GROUP BY `meter`.`sensor_sn`
                    ) aa
                  ";

        return $query;
    }

    /**
     * 월 조회 (1개월 단위 | 월 조회 | 금일 이전 조회 | 마감일 기준 검색)
     *
     * @param int $option
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
    public function getQueryEnergyMonthData(int $option, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $query = "SELECT IFNULL(`daily`.`val_date`, '00000000') AS `val_date`, 
						IFNULL(SUM(`daily`.`val`), 0) AS `val` 
				   FROM `bems_home` AS `home`
						LEFT JOIN `{$sensorTable}` AS `sensor`
							ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
							AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
							AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
						LEFT JOIN `{$dayTable}` AS `daily` 
							ON `sensor`.`sensor_sn` = `daily`.`sensor_sn` 
				   WHERE `sensor`.`fg_use` = 'y'
				   AND `daily`.`val_date` >= '{$startDate}'
				   AND `daily`.`val_date` <= '{$endDate}'
				   {$complexQuery}
				   {$dongQuery}         
				   {$floorQuery}
				   {$roomQuery}
				   {$sensorQuery}
				   {$solarQuery}
                   {$equipmentQuery}
				   GROUP BY `daily`.`val_date`
				";

        return $query;
    }

    /**
     * 월 조회 (일 단위 | 미터 조회 | 금일 조회 | 마감일 관련 없음: 1~말일)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return string
     */
    public function getQueryEnergyCurrentMonthData(int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $cols = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];

        $query = "SELECT IFNULL(`aaa`.`val_date`, '00000000') AS `val_date`,
						 IFNULL(SUM(`aaa`.`val`), 0) AS `val`,
						 SUBSTR(`aaa`.`val_date`, 7, 2) AS day
				  FROM (SELECT `meter`.`sensor_sn`,
						       SUBSTR(`meter`.`val_date`, 1, 8) AS `val_date`,
						       CONCAT('val_', CAST(SUBSTR(`meter`.`{$col}`, 7, 2) AS UNSIGNED)),
						       MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`) `val`
					    FROM `bems_home` AS `home`
						    INNER JOIN `{$sensorTable}` AS `sensor`
							    ON `sensor`.`complex_code_pk` = `home`.`complex_code_pk`
							    AND `sensor`.`home_dong_pk` = `home`.`home_dong_pk`
							    AND `sensor`.`home_ho_pk` = `home`.`home_ho_pk`
						    LEFT JOIN `{$rawTable}` AS `meter`
							    ON `meter`.`sensor_sn` = `sensor`.`sensor_sn`
					    WHERE `sensor`.`fg_use` = 'y' 
					    AND `meter`.`val_date` LIKE '{$date}%'
                        {$complexQuery}
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        GROUP BY `meter`.`sensor_sn`, SUBSTR(`meter`.`val_date`, 1, 8)
                  ) aaa
				  GROUP BY `aaa`.`val_date`
				";

        return $query;
    }

    /**
     * 월 조회 (기간별, 주간 단위 | 일 조회 | 금일이전 | 마감일 관련 없음)
     *
     * @param int $option
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
    public function getQueryEnergyMonthRangeData(int $option, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $query = "SELECT IFNULL(SUBSTR(`daily`.`val_date`, 1, 6), '00000000') AS `val_date`, 
						IFNULL(SUM(`daily`.`val`), 0) AS `val` 
				   FROM `bems_home` AS `home`
						LEFT JOIN `{$sensorTable}` AS `sensor`
							ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
							AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
							AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
						LEFT JOIN `{$dayTable}` AS `daily` 
							ON `sensor`.`sensor_sn` = `daily`.`sensor_sn` 
				   WHERE `sensor`.`fg_use` = 'y'
				   AND `daily`.`val_date` >= '{$startDate}'
				   AND `daily`.`val_date` <= '{$endDate}'
				   {$complexQuery}
				   {$dongQuery}      
				   {$floorQuery}
				   {$roomQuery}
				   {$sensorQuery}
				   {$solarQuery}
                   {$equipmentQuery}
				   GROUP BY SUBSTR(`daily`.`val_date`, 1, 6)
				";

        return $query;
    }

    /**
     * 일 조회 (1일 단위 | 일 조회 | 금일이전 | 마감일 관련 없음)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param sring $dongQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     * @param string $equipmentQuery
     *
     * @return string
     */
    public function getQueryEnergyDayData(int $option, string $date, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $select = "IFNULL(SUM(daily.val_0), 0) AS val_0";

        for($i = 1; $i < 24; $i++) {
            $select = $select.", IFNULL(SUM(daily.val_${i}), 0) AS val_${i} ";
        }

        $query = "SELECT IFNULL(`daily`.`val_date`, '00000000') AS `val_date`,
						 {$select}
				  FROM `bems_home` AS `home`
					 LEFT JOIN `{$sensorTable}` AS `sensor`
						ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
						AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
						AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
					LEFT JOIN `{$dayTable}` AS `daily`
						ON `sensor`.`sensor_sn` = `daily`.`sensor_sn` 
				  WHERE `sensor`.`fg_use` = 'y'
				  AND `daily`.`val_date` LIKE '${date}%'
				  {$complexQuery}
				  {$dongQuery}          
				  {$floorQuery}
				  {$roomQuery}
				  {$sensorQuery}
				  {$solarQuery}
                  {$equipmentQuery}
				  GROUP BY `daily`.`val_date`
				  ORDER BY `daily`.`val_date` DESC
				";

        return $query;
    }

    /**
     * 일 조회 (1일 단위 | 일 조회 | 금일 또는 1일 | 마감일 관련 없음) - 미터로 1달 이상 검색 금지
     * [참조] 이전시간 고려되지 않음
     *
     * @param int $option
     * @param string $date
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
    public function getQueryEnergyCurrentDayData(int $option, string $date, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $cols  = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];

        $query = "SELECT IFNULL(`aaa`.`val_date`, '00000000') AS `val_date`,
						 IFNULL(sum(`aaa`.`val`), 0) `val`, 
						 SUBSTR(`aaa`.`val_date`, 9, 2) AS `time` 
				  FROM (SELECT `meter`.`sensor_sn`, 
							   SUBSTR(`meter`.`val_date`, 1, 10) AS `val_date`, 
							   CONCAT('val_', cast(substr(`meter`.`val_date`, 9, 2) AS unsigned)),
							    IFNULL(
									SUBSTRING_INDEX(GROUP_CONCAT(CAST(`meter`.{$col} AS CHAR) ORDER BY `meter`.`val_date` DESC), ',', 1) -
									SUBSTRING_INDEX(GROUP_CONCAT(CAST(`meter`.{$col} AS CHAR) ORDER BY `meter`.`val_date` ASC), ',', 1)
								, 0) AS `val`			
						FROM `bems_home` AS `home`
							LEFT JOIN `{$sensorTable}` AS `sensor`
								ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
								AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
								AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
							LEFT JOIN `{$rawTable}` AS `meter` 
								ON `meter`.`sensor_sn` = `sensor`.`sensor_sn` 
						WHERE `sensor`.`fg_use` = 'y'
						AND `meter`.`val_date` LIKE '{$date}%'
						{$complexQuery}
						{$dongQuery}      
						{$floorQuery}
						{$roomQuery}
						{$sensorQuery}
						{$solarQuery}
				        {$equipmentQuery}
						GROUP BY `meter`.`sensor_sn`, substr(`meter`.`val_date`, 1, 10)
					) aaa
				  GROUP BY `aaa`.`val_date`
				";

        return $query;
    }

    /**
     * 일 조회 (1일 단위 | 일 조회 | 금일 또는 1일 | 마감일 관련 없음) - 미터로 1달 이상 검색 금지
     * [참조] 현재 시간 사용량 - 이전시간 최대 사용량
     *
     * @param int $option
     * @param string $previousHour
     * @param string $currentHour
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return string
     */
    public function getQueryEnergyCurrentDayByPreviousHourData(int $option, string $previousHour, string $currentHour, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $meterTables = $this->rawTableNames;
        $columns = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $meterTable = $meterTables[$option];
        $column = $columns[$option];

        $query = "SELECT `T`.`val_date`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`,
                         `T`.`day`
                  FROM (SELECT SUBSTR(MAX(`meter`.`val_date`), 1, 10) AS `val_date`,
                               MAX(`meter`.`{$column}`) - MIN(`meter`.`{$column}`) AS `val`,
                               SUBSTR(MAX(`meter`.`val_date`), 9, 2) AS `day`
                        FROM `bems_home` AS `home`
                           LEFT JOIN `{$sensorTable}` AS `sensor`
                              ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                              AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                              AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                           LEFT JOIN `{$meterTable}` AS `meter`
                              ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                        WHERE `meter`.`val_date` >= '{$previousHour}5500'
                        AND `meter`.`val_date` <= '{$currentHour}5559'
                        {$complexQuery} 
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        GROUP BY `sensor`.`sensor_sn`
                  ) T
                  GROUP BY `T`.`val_date`";

        return $query;
    }

    /**
     * 시간 조회 (5분단위 | 미터 조회 | 시 검색 | 마감일 관련 없음)
     *
     * @param int $option
     * @param string $date
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
    public function getQueryEnergyHourData(int $option, string $date, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery, int $receiveTime = 5) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $cols = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];
		
        $end = $date . '00';
        $start = Utility::getInstance()->addMinute($end, "-{$receiveTime}");

        $query = "SELECT `meter`.`sensor_sn`, 
						 IFNULL(`meter`.`val_date`, '00000000000000') AS `val_date`, 
						 IFNULL(`meter`.{$col}, 0) AS `val` 
			       FROM `bems_home` AS `home`
						LEFT JOIN `{$sensorTable}` AS `sensor`
							ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
							AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
							AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
						LEFT JOIN `{$rawTable}` AS `meter` 
							ON `sensor`.`sensor_sn` = `meter`.`sensor_sn` 
				   WHERE `sensor`.`fg_use` = 'y'
				   -- AND (`meter`.`val_date` like '{$date}%' OR (`meter`.`val_date` > '{$start}' AND `meter`.`val_date` < '{$end}'))
				   AND `meter`.`val_date` >= '{$start}00'
				   AND `meter`.`val_date` <= '{$date}5959'
				   {$complexQuery} 
				   {$dongQuery}      
				   {$floorQuery}
				   {$roomQuery}
				   {$sensorQuery}
				   {$solarQuery}
                   {$equipmentQuery}
				   ORDER BY `sensor_sn`, `val_date` ASC
				  ";

        return $query;
    }

    /**
     * 월, 일 조회 (기간별, 주간 단위 | 미터 조회 | 일, 금일 검색 | 마감일 관련 없음)
     *
     * @param int $option
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
    public function getQueryEnergyMonthMeterData(int $option, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $columns = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $column = $columns[$option];

        $query = "SELECT `meter`.`sensor_sn`,
                         SUBSTR(`meter`.`val_date`, 1, 8) AS `val_date`,
                         MAX(`meter`.`{$column}`) - MIN(`meter`.`{$column}`) AS `val`
                  FROM `bems_home` AS `home`
                       LEFT JOIN `{$sensorTable}` AS `sensor`
                           ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                           AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                           AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                       LEFT JOIN `{$rawTable}` AS `meter`
                           ON `sensor`.`sensor_sn` = `meter`.`sensor_sn`
                  WHERE `sensor`.`fg_use` = 'y' 
                  AND `meter`.`val_date` >= '{$startDate}000000'
                  AND `meter`.`val_date` <= '{$endDate}235959'
                  {$complexQuery}
                  {$dongQuery}      
                  {$floorQuery}
                  {$roomQuery}
                  {$sensorQuery}
                  {$solarQuery}
                  {$equipmentQuery}
                  GROUP BY SUBSTR(`meter`.`val_date`, 1, 8)
                 ";

        return $query;
    }

    /**
     * 일 통계 테이블에서 특정 일 누적량만 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $previousDay
     * @param string $sensorNo
     *
     * @return string $query
     */
    public function getQueryEnergyDailyDataByDate(string $complexCodePk, int $option, string $previousDay, string $sensorNo) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dailyTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dailyTable = $dailyTables[$option];

        $query = "SELECT `daily`.`val`
                  FROM `bems_home` AS `home`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                      LEFT JOIN `{$dailyTable}` AS `daily`
                         ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `daily`.`sensor_sn` = '{$sensorNo}'
                  AND `daily`.`val_date` = '{$previousDay}'
                  ";

        return $query;
    }

    /**
     * bems_home 기준으로 년도 사용량 요금 조회 (daily 테이블 기준으로 조회)
     *
     * @param int $option
     * @param string $date
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
    public function getQueryEnergyYearDataByHome(int $option, string $date, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];

        $homeQuery = "";
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $statusColumns = $this->commonQuery->getStatusColumn($option, 'month');

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                         {$statusColumns['group']}
                  FROM (SELECT `home`.`home_grp_pk`,
                               IFNULL(SUM(`month`.`val`), 0) AS `val` 
                               {$statusColumns['in']}
                        FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                                AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                                AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                            LEFT JOIN `{$monthTable}` AS `month` 
                                ON `sensor`.`sensor_sn` = `month`.`sensor_sn` 
                        WHERE `sensor`.`fg_use` = 'y' 
                        AND `month`.`ym` LIKE '" . $date . "%'
                        {$complexQuery} 
                        {$homeQuery}
                        {$dongQuery}
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        {$equipmentQuery}
                        GROUP BY `sensor`.`sensor_sn`
                  ) T 
                  GROUP BY `T`.`home_grp_pk`
				";

        return $query;
    }

    /**
     * bems_home 기준으로 기간별 사용량 요금 조회 (daily 테이블 기준으로 조회)
     *
     * @param int $option
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
     * @return string $query
     */
    public function getQueryEnergyMonthDataByHome(int $option, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $homeQuery = "";
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $statusColumns = $this->commonQuery->getStatusColumn($option, 'daily');

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                         {$statusColumns['group']}
                  FROM (
                       SELECT `home`.`home_grp_pk`,
                              IFNULL(SUM(`daily`.`val`), 0) AS `val`
                              {$statusColumns['in']}
                       FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                                AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                                AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                            LEFT JOIN `{$dayTable}` AS `daily` 
                                ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                       WHERE `sensor`.`fg_use` = 'y'
                       AND `daily`.`val_date` >= '{$startDate}'
                       AND `daily`.`val_date` <= '{$endDate}' 
                       {$complexQuery}
                       {$homeQuery}
                       {$dongQuery}      
                       {$floorQuery}
                       {$roomQuery}
                       {$sensorQuery}
                       {$solarQuery}
                       {$equipmentQuery}
                       GROUP BY `sensor`.`sensor_sn`
                  ) T 
                  GROUP BY `T`.`home_grp_pk`
				";

        return $query;
    }

    /**
     * bems_home 기준으로 금월/금일 날짜에 해당하는 데이터 조회 - 금월 조회 시 like이기 때문에 비교 오류 있음 (meter 조회)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return string $query
     */
    public function getQueryEnergyCurrentMonthDataByHome(int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $cols = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];

        $homeQuery = '';
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $query = "SELECT `T`.`home_grp_pk`,
		                 IFNULL(SUM(`T`.`val`), 0) AS `val`
				  FROM (SELECT `home`.`home_grp_pk`,
		                        MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`) `val`
					    FROM `bems_home` `home`
						    LEFT JOIN `{$sensorTable}` `sensor`
							    ON `sensor`.`complex_code_pk` = `home`.`complex_code_pk`
							    AND `sensor`.`home_dong_pk` = `home`.`home_dong_pk`
							    AND `sensor`.`home_ho_pk` = `home`.`home_ho_pk`
						    LEFT JOIN `{$rawTable}` `meter`
							    ON `meter`.`sensor_sn` = `sensor`.`sensor_sn`
							    AND `meter`.`val_date` LIKE '{$date}%'
					    WHERE `sensor`.`fg_use` = 'y'
                        {$complexQuery}
					    {$homeQuery}
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        GROUP BY `sensor`.`sensor_sn`
                    ) AS `T`
				  GROUP BY `T`.`home_grp_pk`
				";
        return $query;
    }

    /**
     * bems_home 기준으로 금월에 해당하는 데이터 조회 (날짜~날짜)
     *
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return string
     */
    public function getQueryEnergyCurrentYearDataByHome(int $option, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $rawTables = $this->rawTableNames;
        $cols = $this->columnNames;

        $sensorTable = $sensorTables[$option];
        $rawTable = $rawTables[$option];
        $col = $cols[$option];

        $homeQuery = '';
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $query = " SELECT `T`.`home_grp_pk`,
                          IFNULL(SUM(`T`.`val`), 0) AS `val`
                   FROM (SELECT `home`.`home_grp_pk`,
                                MAX(`meter`.`{$col}`) - MIN(`meter`.`{$col}`) AS `val`
                         FROM `bems_home` AS `home`
                               INNER JOIN `{$sensorTable}` AS `sensor`
                                   ON `sensor`.`complex_code_pk` = `home`.`complex_code_pk`
                                   AND `sensor`.`home_dong_pk` = `home`.`home_dong_pk`
                                   AND `sensor`.`home_ho_pk` = `home`.`home_ho_pk`
                               LEFT JOIN `{$rawTable}` AS `meter`
                                   ON `meter`.`sensor_sn` = `sensor`.`sensor_sn`
                         WHERE `sensor`.`fg_use` = 'y' 
                         AND `meter`.`val_date` >= '{$startDate}000000'
                         AND `meter`.`val_date` <= '{$endDate}235959'
                         {$complexQuery}
                         {$homeQuery}
					     {$floorQuery}
					     {$roomQuery}
					     {$sensorQuery}
                         {$solarQuery}
                         GROUP BY `sensor`.`sensor_sn`
                     ) T
                   GROUP BY `T`.`home_grp_pk`
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 사용량 검색- sensor 테이블과 통계 테이블을 구성한 쿼리
    //------------------------------------------------------------------------------------------------------------
    /**
     * 년도 조회 (1년 단위 | 센서 테이블과 월통계 테이블 | 금월 조회 | 마감일 기준 검색)
     *
     * @param int $option
     * @param string $previousMonth
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
    public function getQueryEnergyCurrentYearDataBySensorTable(int $option, string $previousMonth, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;
        $sensorColumns = $this->sensorColumnNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];
        $sensorColumn = $sensorColumns[$option];

        $query = "SELECT `T`.`ym`,
		                 IFNULL(SUM(`T`.`val`), 0)  AS `val`
                  FROM (
                      SELECT `month`.`ym`,
                             CASE WHEN IFNULL(`month`.`val_ed`, 0) = 0
                                  THEN 0   
                                  ELSE IFNULL(`sensor`.`{$sensorColumn}`, 0) - IFNULL(`month`.`val_ed`, 0)
                                  END `val`
                      FROM `bems_home` AS `home`
                          INNER JOIN `{$sensorTable}` AS `sensor`
                             ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                             AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                             AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                          INNER JOIN `{$monthTable}` AS `month`
                             ON `sensor`.`sensor_sn` = `month`.`sensor_sn`
                      WHERE `sensor`.`fg_use` = 'y' 
                      AND `month`.`ym` = '{$previousMonth}'
                      {$complexQuery}
                      {$dongQuery}      
                      {$floorQuery}
                      {$roomQuery}
                      {$sensorQuery}
                      {$solarQuery}
                      {$equipmentQuery}
                      GROUP BY `month`.`sensor_sn`
                  ) T";

        return $query;
    }

    /**
     * 월 조회 (일 단위 | 센서 테이블과 월통계 테이블 | 금일 조회 | 마감일 관련 없음: 1~말일)
     *
     * @param int $option
     * @param string $previousDate
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
    public function getQueryEnergyCurrentMonthDataBySensorTable(int $option, string $previousDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $dailyTables = $this->dayTableNames;
        $sensorTables = $this->sensorTableNames;
        $sensorColumnNames = $this->sensorColumnNames;

        $dailyTable = $dailyTables[$option];
        $sensorTable = $sensorTables[$option];
        $sensorColumn = $sensorColumnNames[$option];

        $query = "SELECT `T`.`val_date` AS `val_date`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`,
                         `T`.`day`
                  FROM (
                      SELECT IFNULL(`daily`.`val_date`, '00000000') AS `val_date`,
                             CASE WHEN IFNULL(`daily`.`total_val`, 0) = 0 
                                  THEN 0 
                                  ELSE IFNULL(`sensor`.`{$sensorColumn}`, 0) - IFNULL(`daily`.`total_val`, 0)
                                  END `val`,
                             SUBSTR(`daily`.`val_date`, 7, 2) AS `day`
                      FROM `bems_home` AS `home`
                         LEFT JOIN `{$sensorTable}` AS `sensor`
                            ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                            AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                            AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                         LEFT JOIN `{$dailyTable}` AS `daily`
                            ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                      WHERE `sensor`.`fg_use` = 'y' 
                      AND `daily`.`val_date` = '{$previousDate}'
                      {$complexQuery}
                      {$dongQuery}      
                      {$floorQuery}
                      {$roomQuery}
                      {$sensorQuery}
                      {$solarQuery}
                      {$equipmentQuery}
                      GROUP BY `daily`.`sensor_sn`
                  ) T";

        return $query;
    }

    /**
     * bems_home 기준으로 금월 데이터 조회 (sensor 테이블과 월 통계 테이블로 튜닝)
     *
     * @param int $option
     * @param string $previousMonth
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
    public function getQueryEnergyCurrentYearHomeDataBySensorTable(int $option, string $previousMonth, string $startDate, string $endDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;
        $sensorColumnNames = $this->sensorColumnNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];
        $sensorColumn = $sensorColumnNames[$option];

        $homeQuery = '';
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $statusColumns = $this->commonQuery->getStatusColumn($option, 'daily_status');
        $statusTable = $this->commonQuery->getStatusTableJoin($option, $startDate, $endDate);

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                         {$statusColumns['group']}
                  FROM (SELECT `home`.`home_grp_pk`,
                               CASE WHEN IFNULL(`month`.`val_ed`, 0) = 0
                                    THEN 0 
                                    ELSE IFNULL(`sensor`.`{$sensorColumn}`, 0) - IFNULL(`month`.`val_ed`, 0) 
                                    END `val`
                               {$statusColumns['in']}
                        FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                                AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                                AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                            LEFT JOIN `{$monthTable}` AS `month`
                                ON `sensor`.`sensor_sn` = `month`.`sensor_sn`
                            {$statusTable}
                        WHERE `sensor`.`fg_use` = 'y' 
                        AND `month`.`ym` = '{$previousMonth}'
                        {$complexQuery}
                        {$homeQuery}
                        {$dongQuery}      
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        {$equipmentQuery}
                        GROUP BY `sensor`.`sensor_sn`
                  ) T 
                  GROUP BY `T`.`home_grp_pk`
                 ";

        return $query;
    }

    /**
     * bems_home 기준으로 금월/금주 데이터 조회 (sensor 테이블과 일통계 테이블로 튜닝)
     *
     * @param int $option
     * @param string $previousDate
     * @param string $complexQuery
     * @param string $dongQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     * @param string $equipmentQuery
     *
     * @return string $query
     */
    public function getQueryEnergyCurrentHomeMonthDataBySensorTable(int $option, string $previousDate, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;
        $sensorColumnNames = $this->sensorColumnNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];
        $sensorColumn = $sensorColumnNames[$option];

        $homeQuery = '';
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $statusColumns = $this->commonQuery->getStatusColumn($option, 'daily');

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                         {$statusColumns['group']}
                  FROM (SELECT `home`.`home_grp_pk`,
                               CASE WHEN IFNULL(`daily`.`total_val`, 0) = 0 
                                    THEN 0 
                                    ELSE IFNULL(`sensor`.`{$sensorColumn}`, 0) - IFNULL(`daily`.`total_val`, 0)
                                    END `val`
                               {$statusColumns['in']}
                        FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `sensor`.`complex_code_pk` = `home`.`complex_code_pk`
                                AND `sensor`.`home_dong_pk` = `home`.`home_dong_pk`
                                AND `sensor`.`home_ho_pk` = `home`.`home_ho_pk`
                            LEFT JOIN `{$dayTable}` AS `daily`
                                ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                        WHERE `sensor`.`fg_use` = 'y' 
                        AND `daily`.`val_date` = '{$previousDate}'
                        {$complexQuery}
                        {$homeQuery}
                        {$dongQuery}      
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        {$equipmentQuery}
                        GROUP BY `sensor`.`sensor_sn`
                  ) AS `T`
                  GROUP BY `T`.`home_grp_pk`
                 ";

        return $query;
    }

    /**
     * bems_home 기준으로 금일 데이터 조회 (sensor 테이블과 일통계 테이블로 튜닝)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $dongQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     * @param string $equipmentQuery
     *
     * @return string $query
     */
    public function getQueryEnergyCurrentDayHomeDataBySensorTable(int $option, string $date, string $complexQuery, string $dongQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery, string $equipmentQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;
        $sensorColumns = $this->sensorColumnNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];
        $sensorColumn = $sensorColumns[$option];

        $baseDateInfo = Utility::getInstance()->getBaseDate();

        $homeQuery = '';
        $totalValExpression = "CASE WHEN IFNULL(`daily`.`total_val`, 0) = 0
                                    THEN 0 
                                    ELSE IFNULL(`sensor`.`{$sensorColumn}`, 0) - IFNULL(`daily`.`total_val`, 0)
                                    END   
                              ";

        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        if ($date === $baseDateInfo['date']) {
            $date = date('Ymd', strtotime($date . '-1 day'));
        } else {
            $totalValExpression = "IFNULL(`daily`.`val`, 0) ";
        }

        $statusColumns = $this->commonQuery->getStatusColumn($option, 'daily');

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                         {$statusColumns['group']}
                  FROM (SELECT `home`.`home_grp_pk`,
                               {$totalValExpression} AS `val`
                               {$statusColumns['in']}
                        FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                                AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                                AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                            LEFT JOIN `{$dayTable}` AS `daily`
                                ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                        WHERE `sensor`.`fg_use` = 'y' 
                        AND `daily`.`val_date` = '{$date}'
                        {$complexQuery} 
                        {$homeQuery}
                        {$dongQuery}      
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        {$solarQuery}
                        {$equipmentQuery}
                        GROUP BY `sensor`.`sensor_sn`
                  ) T
                  GROUP BY `T`.`home_grp_pk`
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 효율 조회
    //------------------------------------------------------------------------------------------------------------
    /**
     * 년도 조회 (1년 단위 | 월통계 조회 | 금월 이전 조회 | 마감일 기준 검색)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryEfficiencyYearData(int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];

        $query = "SELECT IFNULL(`ym`, '000000') AS `ym`,
						 IFNULL(SUM(`month`.`efficiency`), 0) AS `val` 
				  FROM `bems_home` AS `home`
					LEFT JOIN `{$sensorTable}` AS `sensor`
						ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
						AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
						AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
					LEFT JOIN `{$monthTable}` AS `month` 
						ON `sensor`.`sensor_sn` = `month`.`sensor_sn` 
				  WHERE `sensor`.`maker` = 'ntek'
				  AND `month`.`ym` LIKE '{$date}%'
				  {$complexQuery} 
				  {$floorQuery}
				  {$roomQuery}
				  {$sensorQuery}
				  GROUP BY `month`.`ym`
				";

        return $query;
    }

    /**
     * 년도 조회 (1년 단위 | 일통계 조회 | 금월 조회 | 마감일 기준 검색)
     *
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQuerySelectEfficiencyCurrentYearData(int $option, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $query = " SELECT SUBSTR(`aa`.`val_date`, 1, 6) AS `ym`,
                          `aa`.`efficiency` AS `val`
                    FROM (SELECT MAX(`day`.`val_date`) AS `val_date`,
                                 AVG(`day`.`efficiency`) AS `efficiency`
                          FROM `bems_home` AS `home`
                                INNER JOIN `{$sensorTable}` AS `sensor`
                                    ON `sensor`.`complex_code_pk` = `home`.`complex_code_pk`
                                    AND `sensor`.`home_dong_pk` = `home`.`home_dong_pk`
                                    AND `sensor`.`home_ho_pk` = `home`.`home_ho_pk`
                                LEFT JOIN `{$dayTable}` AS `day`
                                    ON `day`.`sensor_sn` = `sensor`.`sensor_sn`
                          WHERE `sensor`.`maker` = 'ntek' 
                          AND `day`.`val_date` >= '{$startDate}000000'
                          AND `day`.`val_date` <= '{$endDate}235959'
                          {$complexQuery}
						  {$floorQuery}
						  {$roomQuery}
						  {$sensorQuery}
                          GROUP BY `day`.`sensor_sn`
                    ) aa
                  ";

        return $query;
    }


    /**
     * 월 조회 (1개월 단위 | 월 조회 | 금일 이전 조회 | 마감일 기준 검색)
     *
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryEfficiencyMonthData(int $option, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $query = "SELECT IFNULL(`daily`.`val_date`, '00000000') AS `val_date`, 
						IFNULL(SUM(`daily`.`efficiency`), 0) AS `val` 
				   FROM `bems_home` AS `home`
						LEFT JOIN `{$sensorTable}` AS `sensor`
							ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
							AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
							AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
						LEFT JOIN `{$dayTable}` AS `daily` 
							ON `sensor`.`sensor_sn` = `daily`.`sensor_sn` 
				   WHERE `sensor`.`maker` = 'ntek'
				   AND `daily`.`val_date` >= '{$startDate}'
				   AND `daily`.`val_date` <= '{$endDate}'
				   {$complexQuery} 
				   {$floorQuery}
				   {$roomQuery}
				   {$sensorQuery}
				   GROUP BY `daily`.`val_date`
				";

        return $query;
    }

    /**
     * 일 조회 (1일 단위 | 일 조회 | 금일이전 | 마감일 관련 없음)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryEfficiencyDayData(int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $select = "IFNULL(SUM(`daily`.`efficiency_0`), 0) AS `val_0`";

        for($i = 1; $i < 24; $i++) {
            $select = $select.", IFNULL(SUM(daily.efficiency_{$i}), 0) AS val_${i} ";
        }

        $query = "SELECT IFNULL(`daily`.`val_date`, '00000000') AS `val_date`,
						 {$select}
				  FROM `bems_home` AS `home`
					LEFT JOIN `{$sensorTable}` AS `sensor`
						ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
						AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
						AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
					LEFT JOIN `{$dayTable}` AS `daily`
						ON `sensor`.`sensor_sn` = `daily`.`sensor_sn` 
				  WHERE `sensor`.`maker` = 'ntek' 
				  AND `daily`.`val_date` LIKE '{$date}%'
				  {$complexQuery} 
				  {$floorQuery}
				  {$roomQuery}
				  {$sensorQuery}
				  GROUP BY `daily`.`val_date`
				  ORDER BY `daily`.`val_date` DESC
				";

        return $query;
    }

    /**
     * 월 조회 (기간별, 주간 단위 | 일 조회 | 금일이전 | 마감일 관련 없음)
     *
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryEfficiencyMonthRangeData(int $option, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $query = "SELECT IFNULL(SUBSTR(`daily`.`val_date`, 1, 6), '00000000') AS `val_date`, 
						IFNULL(SUM(`daily`.`efficiency`), 0) AS `val` 
				   FROM `bems_home` AS `home`
						LEFT JOIN `{$sensorTable}` AS `sensor`
							ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
							AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
							AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
						LEFT JOIN `{$dayTable}` AS `daily` 
							ON `sensor`.`sensor_sn` = `daily`.`sensor_sn` 
				   WHERE `sensor`.`maker` = 'ntek'
				   AND `daily`.`val_date` >= '{$startDate}'
				   AND `daily`.`val_date` <= '{$endDate}'
				   {$complexQuery}
				   {$floorQuery}
				   {$roomQuery}
				   {$sensorQuery}
				   GROUP BY SUBSTR(`daily`.`val_date`, 1, 6)
				";

        return $query;
    }

    /**
     * bems_home 기준으로 년도 효율 조회 (daily 테이블 기준으로 조회)
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string
     */
    public function getQueryEfficiencyYearDataByHome(int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $monthTables = $this->monthTableNames;

        $sensorTable = $sensorTables[$option];
        $monthTable = $monthTables[$option];

        $homeQuery = "";
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                  FROM (SELECT `home`.`home_grp_pk`,
                               IFNULL(AVG(`month`.`efficiency`), 0) AS `val` 
                        FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                                AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                                AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                            LEFT JOIN `{$monthTable}` AS `month` 
                                ON `sensor`.`sensor_sn` = `month`.`sensor_sn` 
                        WHERE `sensor`.`maker` = 'ntek' 
                        AND `month`.`ym` LIKE '{$date}%'
                        {$complexQuery} 
                        {$homeQuery}
                        {$floorQuery}
                        {$roomQuery}
                        {$sensorQuery}
                        GROUP BY `sensor`.`sensor_sn`
                  ) T 
                  GROUP BY `T`.`home_grp_pk`
				";

        return $query;
    }

    /**
     * bems_home 기준으로 기간별 역률 조회
     *
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string $query
     */
    public function getQueryEfficiencyMonthDataByHome(int $option, string $startDate, string $endDate, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $homeQuery = "";
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                  FROM (
                       SELECT `home`.`home_grp_pk`,
                              IFNULL(AVG(`daily`.`efficiency`), 0) AS `val` 
                       FROM `bems_home` AS `home`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                                AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                                AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                            LEFT JOIN `{$dayTable}` AS `daily` 
                                ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                                AND `daily`.`val_date` >= '{$startDate}'
                                AND `daily`.`val_date` <= '{$endDate}' 
                       WHERE `sensor`.`maker` = 'ntek'
                       {$complexQuery}
                       {$homeQuery}
                       {$floorQuery}
                       {$roomQuery}
                       {$sensorQuery}
                       GROUP BY `sensor`.`sensor_sn`
                  ) T 
                  GROUP BY `T`.`home_grp_pk`
				";

        return $query;
    }

    /**
     * bems_home 기준으로 금일 데이터 조회
     *
     * @param int $option
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     *
     * @return string $query
     */
    public function getQueryEfficiencyCurrentDayHomeDataBySensorTable(int $option, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $homeQuery = '';
        if ($option !== 11 && empty($floorQuery) === true) {
            $homeQuery = "AND `home`.`home_grp_pk` NOT IN ('ALL', '0M')";
        }

        $query = "SELECT `T`.`home_grp_pk`,
                         IFNULL(SUM(`T`.`val`), 0) AS `val`
                  FROM (
                     SELECT `home`.`home_grp_pk`,
                            `daily`.`efficiency` AS `val`
                     FROM `bems_home` AS `home`
                        LEFT JOIN `{$sensorTable}` AS `sensor`
                            ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                            AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                            AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                        LEFT JOIN `{$dayTable}` AS `daily`
                            ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
                    WHERE `sensor`.`maker` = 'ntek' 
                    AND `daily`.`val_date` = '{$date}'
                    {$complexQuery} 
                    {$homeQuery}
                    {$floorQuery}
                    {$roomQuery}
                    {$sensorQuery}
                    GROUP BY `sensor`.`sensor_sn`
                  ) T
                  GROUP BY `T`.`home_grp_pk`
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 요금
    //------------------------------------------------------------------------------------------------------------
    /**
     * 전기 요금 조회
     *
     * @param string $complex
     * @param string $date
     *
     * @return string
     */
    public function getQueryElecCost(string $complex, string $date) : string
    {
        $query = "SELECT G_LEVEL,
						 MAX(UNIT_COST) AS UNIT_COST, 
						 MAX(BASE_PRICE) AS BASE_PRICE, 
						 MAX(USED) AS USED
				  FROM bems_unit_cost_electric
				  WHERE FG_DEL='n' 
				  AND COMPLEX_CODE_PK='${complex}' 
				  /*AND START_DATE = (SELECT MAX(START_DATE) 
									FROM bems_unit_cost_electric 
									WHERE COMPLEX_CODE_PK='${complex}' 
									AND START_DATE <= '${date}'
								)
				  */
				  GROUP BY G_LEVEL ORDER BY G_LEVEL
				";

        return $query;
    }

    /**
     * 전기 제외한 요금 조회
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @return string
     */
    public function getQueryCost(string $complexCodePk, int $option) : string
    {
        $query='';

        if ($option === 2) {
            // 수도
            $query = "select unit_cost from bems_unit_cost where fg_del='n' and complex_code_pk='{$complexCodePk}' and energy_type='water'";
        } elseif ($option === 1) {
            // gas
            $query = "select unit_cost from bems_unit_cost where fg_del='n' and complex_code_pk='{$complexCodePk}' and energy_type='gas'";
        } elseif ($option === 7) {
            // 급탕
            $query = "select unit_cost from bems_unit_cost where fg_del='n' and complex_code_pk='{$complexCodePk}' and energy_type='hotwater'";
        } elseif ($option == 8){
            // 난방
            $query = "select unit_cost from bems_unit_cost where fg_del='n' and complex_code_pk='{$complexCodePk}' and energy_type='heating'";
        }

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 미세먼지
    //------------------------------------------------------------------------------------------------------------
    /**
     * 미세먼지, 초미세먼지, co2 미터 정보 조회
     *
     * @param string $complexCodePk
     * @param string $today
     *
     * @return string
     */
    public function getQueryFinedust(string $complexCodePk, string $today) : string
    {
        $query = "SELECT IFNULL(MAX(`meter`.`pm10`) + MIN(`meter`.`pm10`), 0)/2 AS `pm10`, 
						 IFNULL(MAX(`meter`.`pm25`) + MIN(`meter`.`pm25`), 0)/2 AS `pm25`,
                         IFNULL(MAX(`meter`.`co2`) + MIN(`meter`.`co2`), 0)/2 AS `co2`
				  FROM `bems_sensor_finedust` AS `sensor`
					LEFT JOIN `bems_meter_finedust` AS `meter`
						ON `sensor`.`device_eui` = `meter`.`device_eui` 
				  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
				  AND `meter`.`w_date` LIKE '{$today}%'
                  GROUP BY `meter`.`device_eui`
				";

        return $query;
    }

    /**
     * 미세먼지 센서정보 조회
     *
     * @param string $complexQuery
     *
     * @return string $query
     */
    public function getQueryFinedustSensor(string $complexQuery) : string
    {
        $query = "SELECT `sensor`.`device_eui` 
                  FROM `bems_sensor_finedust` AS `sensor`
                  WHERE `fg_use` = 'y'
                  {$complexQuery}
                 ";

        return $query;
    }

    /**
     * bems_meter_finedust에 추가
     *
     * @param string $deviceEUI
     * @param string $valDate
     * @param int $pm25
     * @param int $co2
     * @param int $temperature
     * @param int $humidity
     *
     * @return string $query
     */
    public function getQueryInsertFinedustMeter(string $deviceEUI, string $valDate, int $pm25, int $co2, int $temperature, int $humidity) : string
    {
        $query = "INSERT INTO `bems_meter_finedust`
                  SET `device_eui` = '{$deviceEUI}',
                      `w_date` = '{$valDate}',
                      `pm25` = '{$pm25}',    
                      `temperature` = '{$temperature}',
                      `humidity` = '{$humidity}',    
                      `co2` = '{$co2}'
                  ON DUPLICATE KEY UPDATE 
                      `device_eui` = '{$deviceEUI}',
                      `w_date` = '{$valDate}'
                 ";

        return $query;
    }

    /**
     * bems_sensor_finedust 업데이트
     *
     * @param string $deviceEUI
     * @param string $valDate
     * @param int $pm25
     * @param int $co2
     * @param int $temperature
     * @param int $humidity
     *
     * @return string $query
     */
    public function getQueryUpdateFinedustSensor(string $deviceEUI, string $valDate, int $pm25, int $co2, int $temperature, int $humidity) : string
    {
        $query = "UPDATE `bems_sensor_finedust` 
                  SET `val_date` = '{$valDate}',
                      `pm25` = '{$pm25}',
                      `temperature` = '{$temperature}',
                      `humidity` = '{$humidity}',
                      `co2` = '{$co2}'
                  WHERE `device_eui` = '{$deviceEUI}'
                  ";

        return $query;
    }

    /**
     * 환경부 미세먼지 측정소 정보 조회
     *
     * @param string $complexQuery
     *
     * @return string
     */
    public function getQueryAirStationByComplex(string $complexQuery) : string
    {
        $query = "SELECT `complex`.`complex_code_pk`, 
						 `complex`.`air_station_name`, 
						 IFNULL(`complex`.`air_pm10`, 0) AS `air_pm10`,
                         IFNULL(`complex`.`air_pm25`, 0) AS `air_pm25`
				  FROM `bems_complex` AS `complex` 
				  WHERE 1
				  {$complexQuery}
				 ";

        return $query;
    }

    /**
     * 환경부 Api 정보 변경
     *
     * @param string $complexCodePk
     * @param int $pm10
     * @param int $pm25
     *
     * @return String $query
     */

    public function getQueryUpdateFinedust(string $complexCodePk, int $pm10, int $pm25) : string
    {
        $query = "UPDATE `bems_complex`
                   SET `air_pm10` = '{$pm10}',
                       `air_pm25` = '{$pm25}',
                       `air_upd_date` = NOW()
                   WHERE `complex_code_pk` = '{$complexCodePk}'
                  ";

        return $query;
    }

    /**
     * 미세먼지 일데이터 생성
     *
     * @param array $params
     *
     * @return string
     */
    public function insertInfoFineDustDaily(array $params) : string
    {
        $deviceEui = $params['device_eui'];
        $preDay = $params['pre_day'];
        $table = $params['table'];
        $data = $params['data'];

        $val = 0;
        for ($zz = 0; $zz < count($data); $zz++) {
            $valName = 'val_' . $zz;

            if ($data[$valName] < 0) {
                $val += 0;
            } else {
                $val += $data[$valName];
            }
        }

        // 시간데이터 합을 24로 나눈다.
        $val = $val/24;

        $query = "INSERT INTO `{$table}` SET 
					`sensor_sn` = '{$deviceEui}',
					`val_date` = '{$preDay}',
					`val` = '{$val}',
					`val_0` = '{$data['val_0']}',
					`val_1` = '{$data['val_1']}',
					`val_2` = '{$data['val_2']}',
					`val_3` = '{$data['val_3']}',
					`val_4` = '{$data['val_4']}',
					`val_5` = '{$data['val_5']}',
					`val_6` = '{$data['val_6']}',
					`val_7` = '{$data['val_7']}',
					`val_8` = '{$data['val_8']}',
					`val_9` = '{$data['val_9']}',
					`val_10` = '{$data['val_10']}',
					`val_11` = '{$data['val_11']}',
					`val_12` = '{$data['val_12']}',
					`val_13` = '{$data['val_13']}',
					`val_14` = '{$data['val_14']}',
					`val_15` = '{$data['val_15']}',
					`val_16` = '{$data['val_16']}',
					`val_17` = '{$data['val_17']}',
					`val_18` = '{$data['val_18']}',
					`val_19` = '{$data['val_19']}',
					`val_20` = '{$data['val_20']}',
					`val_21` = '{$data['val_21']}',
					`val_22` = '{$data['val_22']}',
					`val_23` = '{$data['val_23']}'
				";

        return $query;
    }

    /**
     * 월 데이터 생성시 데이터 조회
     *
     * @param array $params
     *
     * @return string
     */
    public function getInfoFineDustDaily(array $params) : string
    {
        $statTable = $params['table'];
        $startDate = $params['start_date'];
        $endDate = $params['end_date'];

        $query = "SELECT `alias1`.`sensor_sn`,
						 `alias1`.`val_date`,
						 SUM(`alias1`.`val`) `val`,
						 MIN(`alias1`.`val`) `min_val`,
						 MAX(`alias1`.`val`) `max_val`
					FROM `{$statTable}` `alias1`
						INNER JOIN `bems_sensor_finedust` `alias2`
							ON `alias1`.`sensor_sn` = `alias2`.`device_eui`
				   WHERE `alias1`.`val_date` >= '{$startDate}'
				   AND `alias1`.`val_date` <= '{$endDate}'
				   GROUP BY `alias1`.`sensor_sn`
				   ORDER BY `alias1`.`val_date`";

        return $query;
    }

    /**
     * 미세먼지 월별 데이터 추가
     *
     * @param array $params
     *
     * @return string
     */
    public function insertInfoFineDustMonth(array $params) : string
    {
        $table = $params['table'];
        $ym = $params['ym'];
        $startDate = $params['start_date'];
        $endDate = $params['end_date'];
        $closingDay = $params['closing_day'];
        $val = $params['val'];
        $minVal = $params['min_val'];
        $maxVal = $params['max_val'];
        $sensorSN = $params['sensor_sn'];

        $query = "INSERT INTO `{$table}` 
					SET `sensor_sn` = '{$sensorSN}',
						`ym` = '{$ym}',
						`st_date` = '{$startDate}',
						`ed_date` = '{$endDate}',
						`closing_day` = '{$closingDay}',
						`val_st` = '{$minVal}',
						`val_ed` = '{$maxVal}',
						`val` = '{$val}'
				   ";
        return $query;
    }

    /**
     * 미세먼지 시간별 데이터/ 일통계 생성 조회 함
     *
     * @param string $date
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getInfoFineDustMeter(string $date, string $complexCodePk = '') : string
    {
        $searchQuery = "";

        if (empty($complexCodePk) == false) {
            $searchQuery .= " AND `complex`.`complex_code_pk` = '{$complexCodePk}'";
        }

        $query = "SELECT `meter`.`device_eui`,
						 IFNULL((MAX(`meter`.`pm10`) + MIN(`meter`.`pm10`))/2, 0) AS `pm10`,
						 IFNULL((MAX(`meter`.`pm25`) + MIN(`meter`.`pm25`))/2, 0) AS `pm25`,
						 IFNULL((MAX(`meter`.`pm1_0`) + MIN(`meter`.`pm1_0`))/2, 0) AS `pm1_0`,      
                         IFNULL((MAX(`meter`.`co2`) + MIN(`meter`.`co2`))/2, 0) AS `co2`
				   FROM `bems_complex` AS `complex`
                        LEFT JOIN `bems_sensor_finedust` AS `sensor`
                            ON `complex`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        LEFT JOIN `bems_meter_finedust` AS `meter`
                            ON `sensor`.`device_eui` = `meter`.`device_eui`
                   WHERE `meter`.`w_date` like '{$date}%'
                   {$searchQuery}
				   GROUP BY `meter`.`device_eui`
				 ";

        return $query;
    }

    /**
     * 미세먼지 일별 데이터 조회
     *
     * @param array $params
     *
     * @return string
     */
    public function getInfoFineDustMonth(array $params) : string
    {
        $dailyTable = $params['table'];
        $startDate = $params['start_date'];
        $endDate = $params['end_date'];
        $complexCodePk = $params['complex_code_pk'];

        $query = "SELECT `alias1`.`sensor_sn`,
						 `alias1`.`val_date`,
						 `alias1`.`val`
				  FROM `{$dailyTable}` AS `alias1`
					 INNER JOIN `bems_sensor_finedust` AS `alias2`
					    ON `alias1`.`sensor_sn` = `alias2`.`device_eui`
				  WHERE `alias1`.`val_date` >= '{$startDate}'
				  AND `alias2`.`val_date` <= '{$endDate}'
				  AND `alias2`.`complex_code_pk` = '{$complexCodePk}'
				  ORDER BY `alias1`.`val_date` ASC
				 ";

        return $query;
    }

    /**
     * 미세먼지 월별 데이터 조회
     *
     * @param array $params
     *
     * @return string
     */
    public function getInfoFineDustYear(array $params) : string
    {
        $monthTable = $params['table'];
        $startYear = $params['start_year'];
        $endYear = $params['end_year'];
        $complexCodePk = $params['complex_code_pk'];

        $query = "SELECT `alias1`.`sensor_sn`,
						 `alias1`.`ym`,
						 `alias1`.`val`
				  FROM `{$monthTable}` `alias1`
					INNER JOIN `bems_sensor_finedust` `alias2`
						ON `alias1`.`sensor_sn` = `alias2`.`device_eui`
				  WHERE `alias1`.`ym` >= '{$startYear}'
				  AND `alias1`.`ym` <= '{$endYear}'
				  AND `alias2`.`complex_code_pk` = '{$complexCodePk}'
				  ORDER BY `alias1`.`ym` ASC";
        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // finedust 테이블  통계 데이터 생성
    //------------------------------------------------------------------------------------------------------------
    /**
     * 통계 테이블에서 데이터가 등록 되어있는지 확인
     *
     * @param int $dateType
     * @param string $date
     * @param string $type
     *
     * @return string
     */
    public function getCheckStatisticsDataSaved(int $dateType, string $date, string $type = 'co2') : string
    {
        $tableName = '';

        switch ($type)
        {
            case 'co2':
                $tableName = Config::CO2_TABLE_INFO['data'][$dateType];
                $column = Config::CO2_TABLE_INFO['column'][$dateType];
                break;
        }

        $query = "SELECT COUNT(`sensor_sn`) AS `sensor_count`
                  FROM `{$tableName}` 
                  WHERE `{$column}` = '{$date}'
                 ";

        return $query;
    }

    /**
     * bems_meter_finedust 테이블에서 시간대별 데이터 추출
     *
     * @param string $complexCodePk
     * @param string $date
     * @param string $sensor
     * @param string $type
     *
     * @return string
     */
    public function getQueryFinedustMeterHourData(string $complexCodePk, string $date, string $sensor, string $type = 'co2') : string
    {
        $meterTable = '';
        $sensorTable = '';
        $column = '';

        switch ($type)
        {
            case 'co2':
                $meterTable = Config::CO2_TABLE_INFO['data'][2];
                $sensorTable = Config::CO2_TABLE_INFO['sensor']['table'];
                $column = Config::CO2_TABLE_INFO['sensor']['column'];
                break;
        }

        $query = "SELECT `T`.`w_date` AS `hour`,
                         CASE WHEN `T`.`w_date` < 10 
                             THEN CONCAT('val_', SUBSTR(`T`.`w_date`, 2)) 
                             ELSE CONCAT('val_', `T`.`w_date`) 
                             END `column`,
                         `T`.`val`
                   FROM (SELECT SUBSTR(`meter`.`w_date`,12, 2) AS `w_date`,
                                `meter`.`device_eui`, 
                                IFNULL((MAX(`meter`.`co2`) + MIN(`meter`.`co2`))/2, 0) AS `val`
                         FROM `bems_complex` AS `complex`
                            LEFT JOIN `{$sensorTable}` AS `sensor`
                                ON `complex`.`complex_code_pk` = `sensor`.`complex_code_pk`
                            LEFT JOIN `{$meterTable}` AS `meter`
                                ON `sensor`.`{$column}` = `meter`.`{$column}`
                        WHERE `complex`.`complex_code_pk` = '{$complexCodePk}'
                        AND `meter`.`{$column}` = '{$sensor}'
                        AND `meter`.`w_date` LIKE '{$date}%'
                        GROUP BY SUBSTR(`meter`.`w_date`,1, 13)
                   ) T        
                  ";

        return $query;
    }

    /**
     * 기간에 대한 총 평균값, 최대, 최소값
     *
     * @param string $complexCodePk
     * @param string $date
     * @param string $sensor
     * @param string $type
     *
     * @return string
     */
    public function getQueryFinedustMeterTotalData(string $complexCodePk, string $date, string $sensor, string $type = 'co2') : string
    {
        $meterTable = '';
        $sensorTable = '';
        $column = '';

        switch ($type)
        {
            case 'co2':
                $meterTable = Config::CO2_TABLE_INFO['data'][2];
                $sensorTable = Config::CO2_TABLE_INFO['sensor']['table'];
                $column = Config::CO2_TABLE_INFO['sensor']['column'];
                break;
        }

        $query = "SELECT `meter`.`device_eui`,
                         IFNULL(MIN(`meter`.`co2`), 0) AS `min_val`,
      	                 IFNULL(MAX(`meter`.`co2`), 0) AS `max_val`,
                         IFNULL((MAX(`meter`.`co2`) + MIN(`meter`.`co2`))/2, 0) AS `val`
                  FROM `bems_complex` AS `complex`
                    LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `complex`.`complex_code_pk` = `sensor`.`complex_code_pk`
                    LEFT JOIN `{$meterTable}` AS `meter`
                        ON `sensor`.`{$column}` = `meter`.`{$column}`
                  WHERE `complex`.`complex_code_pk` = '{$complexCodePk}'
                  AND `meter`.`{$column}` = '{$sensor}'
                  AND `meter`.`w_date` LIKE '{$date}%'
                  GROUP BY `meter`.`device_eui`
               ";

        return $query;
    }

    /**
     * finedust 일 통계 생성
     *
     * @param string $deviceEUI
     * @param string $valDate
     * @param array $hourData
     * @param int $total
     * @param string $type
     *
     * @return string
     */
    public function getQueryInsertFinedustDaily(string $deviceEUI, string $valDate, array $hourData, int $total, $type = 'co2') : string
    {
        $dailyTable = '';

        switch ($type)
        {
            case 'co2':
                $dailyTable = Config::CO2_TABLE_INFO['data'][1];
                break;
        }

        $columnString = '';
        foreach($hourData as $k => $v) {
            $columnString .= ",`{$v['column']}` = '{$v['val']}'";
        }

        $query = "INSERT INTO `{$dailyTable}` 
                    SET `sensor_sn` = '{$deviceEUI}'
                        ,`val_date` = '{$valDate}'
                        ,`val` = '{$total}'
                        {$columnString}
                  ";

        return $query;
    }

    /**
     * finedust 월 통계 생성
     *
     * @param string $sensor
     * @param string $ym
     * @param string $endDay
     * @param string $startDate
     * @param string $endDate
     * @param int $minVal
     * @param int $maxVal
     * @param int $total
     * @param string $type
     *
     * @return string
     */
    public function getQueryInsertFinedustMonth(string $sensor, string $ym, string $endDay, string $startDate, string $endDate, int $minVal, int $maxVal, int $total, string $type = 'co2') : string
    {
        $monthTable = '';

        switch ($type)
        {
            case 'co2':
                $monthTable = Config::CO2_TABLE_INFO['data'][0];
                break;
        }

        $query = "INSERT INTO `{$monthTable}`
                    SET `sensor_sn` = '{$sensor}',
                        `ym` = '{$ym}',
                        `st_date` = '{$startDate}',
                        `ed_date` = '{$endDate}',
                        `closing_day` = '{$endDay}',
                        `val_st` = '{$minVal}',
                        `val_ed` = '{$maxVal}',
                        `val` = '{$total}'
                  ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 예측
    //------------------------------------------------------------------------------------------------------------
    /**
     * 센서별 예측 정보 조회
     *
     * @param int $option
     * @param string $predictColumn
     * @param string $date
     * @param string $complexQuery
     * @param string $floorQuery
     * @param string $roomQuery
     * @param string $sensorQuery
     * @param string $solarQuery
     *
     * @return string
     */
    public function getPredictDataBySensor(int $option, string $predictColumn, string $date, string $complexQuery, string $floorQuery, string $roomQuery, string $sensorQuery, string $solarQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $dayTables = $this->dayTableNames;

        $sensorTable = $sensorTables[$option];
        $dayTable = $dayTables[$option];

        $query = "SELECT `T`.`home_grp_pk`,
					     `T`.`sensor_sn`,
					     SUM(`T`.`val`) AS `val`
				  FROM (SELECT `home`.`home_grp_pk`,
							   `sensor`.`sensor_sn`,
							   IFNULL(`daily`.`{$predictColumn}`, 0) AS `val`
						FROM `bems_home` AS `home`
							LEFT JOIN `{$sensorTable}` AS `sensor`
								ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
								AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
								AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
							LEFT JOIN `{$dayTable}` AS `daily`
								ON `sensor`.`sensor_sn` = `daily`.`sensor_sn`
						WHERE `daily`.`val_date` = '{$date}' 
						{$complexQuery}
						{$floorQuery}
						{$roomQuery}
						{$sensorQuery}
						{$solarQuery}
						GROUP BY `home`.`home_grp_pk`
					) T
				  GROUP BY `T`.`home_grp_pk`
				  ORDER BY `T`.`home_grp_pk` ASC 
				 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 태양광
    //------------------------------------------------------------------------------------------------------------
    /**
     * 실시간 발전 효율을 계산하기 위해 현재시간에 해당하는 태양광 발전량 조회
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @return string
     */
    public function getQuerySolarCurrentTime(string $complexCodePk, int $option) : string
    {
        $sensorTables = $this->sensorTableNames;
        $meterTables = $this->rawTableNames;

        $meterTable = $meterTables[$option];
        $sensorTable = $sensorTables[$option];

        $query = "SELECT `all_data`
		          FROM `{$sensorTable}`
		          WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `inout` = 'I'
		         ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 장애
    //------------------------------------------------------------------------------------------------------------
    /**
     * 센서테이블에서 장애 발생 내역 추출
     *
     * @param int $option
     * @param string $mode
     *
     * @return string
     */
    public function getQueryCurrentAlarmFromSensorTable(int $option, string $mode) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $errorWhere = "";

        if ($mode === 'error_code') {
            $errorWhere = " AND IFNULL(`sensor`.`error_code`, 0) <> 0";
        }

        /*
         * 시간 비교
         * and ((SYSDATE() - date_format(val_date, '%Y%m%d%H%i%s'))) >= 1800
         */

        $query = "SELECT `complex`.`home_dong_cnt`,
                         `home`.`home_grp_pk`,
                         `sensor`.`complex_code_pk`,
                         `sensor`.`sensor_sn`,
                         `sensor`.`home_dong_pk`,
                         `sensor`.`home_ho_pk`,
                         `sensor`.`val_date`,
                         `toc`.`arch_type`,
                         IFNULL(`toc`.`sensor_sn`, '') AS `toc_sensor_sn`
                  FROM `bems_complex` AS `complex`
                     LEFT JOIN `bems_home` AS `home`
                        ON `complex`.`complex_code_pk` = `home`.`complex_code_pk`
                     LEFT JOIN `toc_info` AS `toc`  
                        ON `home`.`complex_code_pk` = `toc`.`complex_code_pk`
                        AND `home`.`home_type` = `toc`.`type`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`  
                  WHERE `sensor`.`val_date` IS NOT NULL
                  {$errorWhere}
                  AND `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
                  AND `complex`.`fg_del` = 'n'
                  AND `sensor`.`fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * 장애가 등록되어있는지 조회
     *
     * @param int $option
     * @param string $sensorSn
     * @param string $alarmCode
     * @param string $alarmOn
     * @param string $mode
     *
     * @return string
     */
    public function getQueryIsExistAlarm(int $option, string $sensorSn, string $alarmCode, string $alarmOn, string $mode) : string
    {
        $sensorTypes = $this->sensorTypes;
        $sensorType = $sensorTypes[$option];

        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        if ($mode === 'alarm_log') {
            $query = "SELECT * FROM bems_alarm_log where sensor_type='${sensorType}' and sensor_sn='${sensorSn}' and alarm_code='${alarmCode}' and alarm_on_off='${alarmOn}'";
        } else {
            //$query = "SELECT * FROM ${sensorTable} where sensor_sn='${sensorSn}' and error_code != 0 and val_date = (select max(val_date) from ${sensorTable} where sensor_sn='${sensorSn}')"; // 최신 정보에서 error_code != 0 값 확인
            if ($alarmCode === '0001') {
                $query = "SELECT * FROM ${sensorTable} where sensor_sn='${sensorSn}' and error_code != 0";
            } else if(($alarmCode === '0002')) {
                $query = "SELECT 'TRUE' AS TIMEOVER, complex_code_pk, sensor_sn, complex_code_pk, home_dong_pk, home_ho_pk FROM ${sensorTable} WHERE fg_use='y' and sensor_sn='${sensorSn}' and ((SYSDATE() - date_format(val_date, '%Y%m%d%H%i%s'))) >= 1800";
            }
        }

        return $query;
    }

    /**
     * 장애가 해제 되었는지 조회
     *
     * @param int $option
     * @param string $sensorSn
     * @param string $alarmCode
     *
     * @return string
     */
    public function getQueryIsNotExistAlarm(int $option, string $sensorSn, string $alarmCode) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        //$errorWhere = " AND SYSDATE() - DATE_FORMAT(`sensor`.`val_date`, '%Y%m%d%H%i%s') < 1800";
        $errorWhere = "";

        if ($alarmCode === '0001') {
            $errorWhere = " AND IFNULL(`sensor`.`error_code`, 0) = '0'";
        }

        $query = "SELECT `complex`.`home_dong_cnt`,
                         `home`.`home_grp_pk`,
                         `sensor`.`sensor_sn`,
                         `sensor`.`val_date`,
                         `toc`.`arch_type`,
                         IFNULL(`toc`.`sensor_sn`, '') AS `toc_sensor_sn`
                  FROM `bems_complex` AS `complex`
                     LEFT JOIN `bems_home` AS `home`
                        ON `complex`.`complex_code_pk` = `home`.`complex_code_pk`
                     LEFT JOIN `toc_info` AS `toc`   
                        ON `home`.`complex_code_pk` = `toc`.`complex_code_pk`
                        AND `home`.`home_type` = `toc`.`type`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `sensor`.`sensor_sn` = '{$sensorSn}'
                  {$errorWhere}
                  AND `complex`.`fg_del` = 'n'
                  AND `sensor`.`fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * 장애 로그 추가
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sensorSn
     * @param string $alarmCode
     * @param string $alarmOnOff
     * @param string $alarmTime
     * @param string $homeDongPk
     * @param string $homeHoPk
     * @param string $alarmCodeMsg
     *
     * @return string
     */
    public function getQueryInsertAlarm(string $complexCodePk, int $option, string $sensorSn, string $alarmCode, string $alarmOnOff, string $alarmTime, string $homeDongPk, string $homeHoPk, string $alarmCodeMsg) : string
    {
        $sensorTypes = $this->sensorTypes;
        $sensorType  = $sensorTypes[$option];

        $query = "INSERT INTO `bems_alarm_log` SET
					`complex_code_pk` = '{$complexCodePk}',
					`sensor_type` = '{$sensorType}',
					`sensor_sn` = '{$sensorSn}',
					`alarm_code` = '{$alarmCode}',
					`alarm_on_off` = '{$alarmOnOff}',
					`alarm_on_time` = '{$alarmTime}',
					`home_dong_pk` = '{$homeDongPk}',
					`home_ho_pk` = '{$homeHoPk}',
					`alarm_msg` = '{$alarmCodeMsg}'";

        return $query;
    }

    /**
     * 장애 상태 변경
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sensorSn
     * @param string $alarmCode
     * @param string $alarmOff
     * @param string $alarmReleaseTime
     * @param string $noAlarmLogPk
     *
     * @return string
     */
    public function getQueryAlarmRelease(string $complexCodePk, int $option, string $sensorSn, string $alarmCode, string $alarmOff, string $alarmReleaseTime, string $noAlarmLogPk) : string
    {
        $sensorTypes = $this->sensorTypes;
        $sensorType  = $sensorTypes[$option];

        $query = "UPDATE `bems_alarm_log` 
                    SET `alarm_on_off` = '${alarmOff}', 
                        `alarm_off_time` = '${alarmReleaseTime}' 
                    WHERE `complex_code_pk` = '{$complexCodePk}' 
                    AND `sensor_type` = '${sensorType}' 
                    AND `sensor_sn` = '${sensorSn}' 
                    AND `alarm_code` = '${alarmCode}' 
                    AND `no_alarm_log_pk` = '${noAlarmLogPk}'
                   ";

        return $query;
    }

    /**
     * 장애 테이블에서 상태에 따른 장애 데이터 조회
     *
     * @param int $option
     * @param string $alarmOn
     *
     * @return string
     */
    public function getQueryCurrentAlarmFromAlarmLogTable(int $option, string $alarmOn) : string
    {
        $sensorTypes = $this->sensorTypes;
        $sensorType  = $sensorTypes[$option];

        $query = "SELECT `log`.* 
                  FROM bems_alarm_log AS `log`
                  WHERE `log`.`sensor_type` = '{$sensorType}' 
                  AND `log`.`alarm_on_off` = '{$alarmOn}'
                 ";

        return $query;
    }

    /**
     * 장애 발생 코드 조회
     *
     * @param string $alarmCode
     *
     * @return string
     */
    public function getQueryAlarmCodeMsg(string $alarmCode) : string
    {
        $query = "SELECT `alarm_msg` FROM `bems_alarm_code` WHERE `alarm_code` = '{$alarmCode}'";

        return $query;
    }

    /**
     * 장애 검색조건에 따라 얼마나 있는지 조회
     *
     * @param string $complexCodePk
     * @param array $params
     *
     * @return string
     */
    public function getHindranceAlarmCount(string $complexCodePk, array $params) : string
    {
        $p = $params;

        $searchQuery = '';
        $alarmTimeColumn = '`log`.`alarm_on_time`';

        // 알람 발생 상태
        if (!empty($p['frame_error_status'])) {
            $searchQuery .= " AND `log`.`alarm_on_off` = '{$p['frame_error_status']}'";
        } else {
            $searchQuery .= " AND `log`.`alarm_on_off` IN ('on','off')";
        }

        // 층별 검색
        if (empty($p['frame_floor_type']) === false) {
            $searchQuery .= " AND (`home`.`home_grp_pk` = '{$p['frame_floor_type']}' OR `log`.`sensor_sn` LIKE '%_{$p['frame_floor_type']}')";
        }

        // 알람 발생 여부에 따른 컬럼명 지정
        if ($p['frame_error_status'] == 'off') {
            $alarmTimeColumn = '`log`.`alarm_off_time`';
        }

        // 날짜 검색
        if ($p['frame_error_status'] !== 'on'
            && (empty($p['frame_start_date']) === false && empty($p['frame_end_date']) === false)) {
            $frameStartDate = str_replace('-', '', $p['frame_start_date']);
            $frameEndDate = str_replace('-', '', $p['frame_end_date']);

            $searchQuery .= " AND date_format({$alarmTimeColumn}, '%Y%m%d') >= '{$frameStartDate}'";
            $searchQuery .= " AND date_format({$alarmTimeColumn}, '%Y%m%d') <= '{$frameEndDate}'";
        }

        // 에너지 검색
        if (!empty($p['frame_energy_type'])) {
            $searchQuery .= " AND `log`.`sensor_type` = '{$p['frame_energy_type']}'";
        }

        if (empty($p['sensor_sn']) === false) {
            $searchQuery .= " AND `log`.`sensor_sn` = '{$p['sensor_sn']}'";
        }

        $query = "SELECT COUNT(`T`.`no_alarm_log_pk`) AS `cnt`
                  FROM (SELECT `log`.`no_alarm_log_pk`
				        FROM  `bems_home` AS `home`
				            LEFT JOIN `bems_alarm_log` AS `log`
				                ON `home`.`complex_code_pk` = `log`.`complex_code_pk`
				                AND `home`.`home_dong_pk` = `log`.`home_dong_pk`
				                AND `home`.`home_ho_pk` = `log`.`home_ho_pk`
				        WHERE `log`.`complex_code_pk` = '{$complexCodePk}'
				        {$searchQuery}
				        group by `log`.`no_alarm_log_pk`
				    ) T
				 ";

        return $query;
    }

    /**
     * 장애 검색조건에 따라 데이터 조회
     *
     * @param string $complexCodePk
     * @param array $params
     * @param int $startPage
     * @param int $endPage
     *
     * @return string
     */
    public function getHindranceAlarmLog(string $complexCodePk, array $params, int $startPage, int $endPage) : string
    {
        $p = $params;

        $searchQuery = '';
        $alarmTimeColumn = '`log`.`alarm_on_time`';

        // 알람 발생 상태
        if (empty($p['frame_error_status']) === false) {
            $searchQuery .= " AND `log`.`alarm_on_off` = '{$p['frame_error_status']}'";
        } else {
            $searchQuery .= " AND `log`.`alarm_on_off` IN ('on','off')";
        }

        // 층별 검색
        if (empty($p['frame_floor_type']) === false) {
            $searchQuery .= " AND (`home`.`home_grp_pk` = '{$p['frame_floor_type']}' OR `log`.`sensor_sn` LIKE '%_{$p['frame_floor_type']}')";
        }

        // 알람 발생 여부에 따른 컬럼명 지정
        if ($p['frame_error_status'] == 'off') {
            $alarmTimeColumn = '`log`.`alarm_off_time`';
        }

        // 날짜 검색
        if ($p['frame_error_status'] !== 'on'
            && (empty($p['frame_start_date']) === false && empty($p['frame_end_date']) === false)) {
            $frameStartDate = str_replace('-', '', $p['frame_start_date']);
            $frameEndDate = str_replace('-', '', $p['frame_end_date']);

            $searchQuery .= " AND date_format({$alarmTimeColumn}, '%Y%m%d') >= '{$frameStartDate}'";
            $searchQuery .= " AND date_format({$alarmTimeColumn}, '%Y%m%d') <= '{$frameEndDate}'";
        }

        // 에너지 검색
        if (empty($p['frame_select_energy_type']) === false) {
            $searchQuery .= " AND `log`.`sensor_type` = '{$p['frame_select_energy_type']}'";
        }

        // 센서 검색
        if (empty($p['sensor_sn']) === false) {
            $searchQuery .= " AND `log`.`sensor_sn` = '{$p['sensor_sn']}'";
        }

        $query = "SELECT `home`.`home_grp_pk`,
                         `log`.`sensor_type`,
                         `log`.`alarm_msg`,
                         `log`.`sensor_sn`,
                         `log`.`home_dong_pk`,
                         `log`.`home_ho_pk`,
                         date_format(`log`.`alarm_on_time`, '%Y-%m-%d %H:%i:%s') `alarm_on_time`,
						 date_format(`log`.`alarm_off_time`, '%Y-%m-%d %H:%i:%s') `alarm_off_time`,
						  `log`.`reg_date`,
						 CASE WHEN `log`.`alarm_on_off` = 'on' THEN '발생' ELSE '해제' END `alarm_on_off`,
						 `building`.`name`
		          FROM `bems_complex` AS `building`
		            LEFT JOIN `bems_home` AS `home`
		                ON `building`.`complex_code_pk` = `home`.`complex_code_pk`
		            LEFT JOIN `bems_alarm_log` AS `log`
		                ON `home`.`complex_code_pk` = `log`.`complex_code_pk`
		                AND `home`.`home_dong_pk` = `log`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `log`.`home_ho_pk`
		          WHERE `log`.`complex_code_pk` = '{$complexCodePk}'
		          {$searchQuery}
		          GROUP BY `log`.`no_alarm_log_pk`
				  ORDER BY `log`.`alarm_off_time` ASC, {$alarmTimeColumn} DESC, `log`.`sensor_type` ASC
				  LIMIT {$startPage}, {$endPage}
		         ";

        return $query;
    }

    /**
     * 장애 발생 건수 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getHindranceAlarmExistCount(string $complexCodePk) : string
    {
        $query = "SELECT COUNT(`alarm_on_off`) `alarm_on_off`
				  FROM `bems_alarm_log`
				  WHERE `complex_code_pk` = '{$complexCodePk}'
				  AND `alarm_on_off` = 'on'";

        return $query;
    }

    /**
     * 장애 엑셀 다운로드 조회
     *
     * @param string $complexCodePk
     * @param array $params
     *
     * @return string
     */
    public function getHindranceAlarmExcel(string $complexCodePk, array $params) : string
    {
        $p = $params;

        $searchQuery = '';
        $alarmTimeColumn = '`log`.`alarm_on_time`';

        // 알람 발생 상태
        if (!empty($p['frame_error_status'])) {
            $searchQuery .= " AND `log`.`alarm_on_off` = '{$p['frame_error_status']}'";
        } else {
            $searchQuery .= " AND `log`.`alarm_on_off` IN ('on','off')";
        }

        // 층별 검색
        if (empty($p['frame_floor_type']) === false) {
            $searchQuery .= " AND (`home`.`home_grp_pk` = '{$p['frame_floor_type']}' OR `log`.`sensor_sn` LIKE '%_{$p['frame_floor_type']}')";
        }

        // 알람 발생 여부에 따른 컬럼명 지정
        if ($p['frame_error_status'] == 'off') {
            $alarmTimeColumn = '`log`.`alarm_off_time`';
        }

        // 날짜 검색
        if ($p['frame_error_status'] !== 'on'
            && (empty($p['frame_start_date']) === false && empty($p['frame_end_date']) === false)) {
            $frameStartDate = str_replace('-', '', $p['frame_start_date']);
            $frameEndDate = str_replace('-', '', $p['frame_end_date']);

            $searchQuery .= " AND date_format({$alarmTimeColumn}, '%Y%m%d') >= '{$frameStartDate}'";
            $searchQuery .= " AND date_format({$alarmTimeColumn}, '%Y%m%d') <= '{$frameEndDate}'";
        }

        // 에너지 검색
        if (!empty($p['frame_select_energy_type'])) {
            $searchQuery .= " AND `log`.`sensor_type` = '{$p['frame_select_energy_type']}'";
        }

        // 센서 검색
        if (empty($p['sensor_sn']) === false) {
            $searchQuery .= " AND `log`.`sensor_sn` = '{$p['sensor_sn']}'";
        }

        $query = "SELECT `home`.`home_grp_pk`,
                         `log`.`sensor_type`,
                         `log`.`alarm_msg`,
                         `log`.`sensor_sn`,
                         `log`.`home_dong_pk`,
                         `log`.`home_ho_pk`,
                         date_format(`log`.`alarm_on_time`, '%Y-%m-%d %H:%i:%s') `alarm_on_time`,
						 date_format(`log`.`alarm_off_time`, '%Y-%m-%d %H:%i:%s') `alarm_off_time`,
						  `log`.`reg_date`,
						 CASE WHEN `log`.`alarm_on_off` = 'on' THEN '발생' ELSE '해제' END `alarm_on_off`,
						 `building`.`name`
		          FROM `bems_complex` AS `building`
		            LEFT JOIN `bems_home` AS `home`
		                ON `building`.`complex_code_pk` = `home`.`complex_code_pk`
		            LEFT JOIN `bems_alarm_log` AS `log`
		                ON `home`.`complex_code_pk` = `log`.`complex_code_pk`
		                AND `home`.`home_dong_pk` = `log`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `log`.`home_ho_pk`
		          WHERE `log`.`complex_code_pk` = '{$complexCodePk}'
		          {$searchQuery}
		          GROUP BY `log`.`no_alarm_log_pk`
				  ORDER BY `log`.`alarm_off_time` ASC, {$alarmTimeColumn} DESC, `log`.`sensor_type` ASC
		         ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 분석 - 종합분석 (실내 온/습도 추이)
    //------------------------------------------------------------------------------------------------------------
    /**
     * 온습도 기간별 데이터 조회 (금일)
     *
     * @param string $complexCodePk
     * @param string $startDate
     * @param string $endDate
     * @param string $finedustType
     *
     * @return string
     */
    public function getFinedustDailyData(string $complexCodePk, string $startDate, string $endDate, string $finedustType) : string
    {
        $query = "SELECT `sensor`.`complex_code_pk`,
                         `sensor`.`device_eui`,
                         SUBSTR(`meter`.`w_date`, 1, 13) AS `w_date`,
                         SUBSTR(`meter`.`w_date`, 12, 2) AS `date_str`,
                         IFNULL((MAX(`meter`.`{$finedustType}`) + MIN(`meter`.`{$finedustType}`))/2, 0) AS `{$finedustType}`
                  FROM `bems_sensor_finedust` AS `sensor`
                    LEFT JOIN `bems_meter_finedust` AS `meter`
                        ON `sensor`.`device_eui` = `meter`.`device_eui`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `meter`.`w_date` >= '{$startDate}'
                  AND `meter`.`w_date` <= '{$endDate}'
                  GROUP BY `meter`.`device_eui`,  SUBSTR(`meter`.`w_date`, 1, 13)
                 ";

        return $query;
    }

    /**
     * 온습도 기간별 데이터 조회 (금월)
     *
     * @param string $complexCodePk
     * @param string $startDate
     * @param string $endDate
     * @param string $finedustType
     *
     * @return string
     */
    public function getFinedustMonthData(string $complexCodePk, string $startDate, string $endDate, string $finedustType) : string
    {
        $query = "SELECT `sensor`.`complex_code_pk`,
                         `sensor`.`device_eui`,
                         SUBSTR(`meter`.`w_date`, 1, 10) AS `w_date`,
                         SUBSTR(`meter`.`w_date`, 9, 2) AS `date_str`,
                         IFNULL((MAX(`meter`.`{$finedustType}`) + MIN(`meter`.`{$finedustType}`))/2, 0) AS `{$finedustType}`
                  FROM `bems_sensor_finedust` AS `sensor`
                    LEFT JOIN `bems_meter_finedust` AS `meter`
                        ON `sensor`.`device_eui` = `meter`.`device_eui`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `meter`.`w_date` >= '{$startDate}'
                  AND `meter`.`w_date` <= '{$endDate}'
                  GROUP BY `meter`.`device_eui`,  SUBSTR(`meter`.`w_date`, 1, 10)
                ";

        return $query;
    }

    /**
     * 온습도 기간별 데이터 조회 (금년)
     *
     * @param string $complexCodePk
     * @param string $startDate
     * @param string $endDate
     * @param string $finedustType
     *
     * @return string
     */
    public function getFinedustYearData(string $complexCodePk, string $startDate, string $endDate, string $finedustType) : string
    {
        $query = "SELECT `sensor`.`complex_code_pk`,
                         `sensor`.`device_eui`,
                         SUBSTR(`meter`.`w_date`, 1, 7) AS `w_date`,
                         SUBSTR(`meter`.`w_date`, 6, 2) AS `date_str`,
                         IFNULL((MAX(`meter`.`{$finedustType}`) + MIN(`meter`.`{$finedustType}`))/2, 0) AS `{$finedustType}`
                  FROM `bems_sensor_finedust` AS `sensor`
                    LEFT JOIN `bems_meter_finedust` AS `meter`
                        ON `sensor`.`device_eui` = `meter`.`device_eui`
                  WHERE `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `meter`.`w_date` >= '{$startDate}'
                  AND `meter`.`w_date` <= '{$endDate}'
                  GROUP BY `meter`.`device_eui`,  SUBSTR(`meter`.`w_date`, 1, 7);
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 설정
    //------------------------------------------------------------------------------------------------------------
    /**
     * 설정 > 정보관리에 필요한  building 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getComplexData(string $complexCodePk) : string
    {
        $query = "SELECT `name`,
						 `home_cnt`,
						 `home_dong_cnt`,
                         `floor_cnt`,
						 `complex_code_pk`,
						 `addr`,
						 `tel`,
						 `fax`,
						 `closing_day_electric`,
						 `hp`,
                         `building_area`,
						 `limit_val_solar`,
						 `limit_val_electric`,
						 `limit_val_gas`,
						 `limit_val_water`,
						 `limit_val_electric_light`,
						 `limit_val_electric_cold`,
						 `limit_val_electric_elechot`,
						 `limit_val_electric_vent`,
						 `limit_val_electric_boiler`,
						 `limit_val_electric_water`,
						 `limit_val_electric_heating`,
						 `limit_val_electric_hotwater`,
						 `limit_val_electric_elevator`, 
                         `limit_val_electric_equipment`,   
						 `limit_val_finedust`,
                         `limit_val_heating`,
                         `limit_val_co2`
				  FROM `bems_complex`
				  WHERE `complex_code_pk` = '{$complexCodePk}'
				";

        return $query;
    }

    /**
     * 설정 > 정보관리에 필요한  이메일 조회 (업체관리자)
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getComplexEmail(string $complexCodePk) : string
    {
        $query = "SELECT `ba`.`email`,
						 `ba`.`admin_pk`
				  FROM `bems_complex` `bc`
					LEFT JOIN `bems_admin` `ba`
						ON `bc`.`complex_code_pk` = `ba`.`complex_code_pk`
						AND `bc`.`manager` = `ba`.`name` 
				  WHERE `ba`.`complex_code_pk` = '{$complexCodePk}'";

        return $query;
    }

    /**
     * 설정 > 정보관리에서 입력가능한 이메일 수정
     *
     * @param int $adminPk
     * @param string $email
     * @param int $ssPK
     *
     * @return string
     */
    public function updateComplexEmail(int $adminPk, string $email, int $ssPK) : string
    {
        $query = "UPDATE `bems_admin`
				  SET `email` = '{$email}',
					  `updator` = '{$ssPK}',
					  `update_date` = NOW()
				  WHERE `admin_pk` = '{$adminPk}'
				";

        return $query;
    }

    /**
     * 설정 > 정보관리에서 입력가능한 기본정보 수정
     *
     * @param string $complexCodePk
     * @param array $data
     *
     * @return string
     */
    public function updateComplexInfo(string $complexCodePk, array $data) : string
    {
        $query = "UPDATE `bems_complex` 
                  SET `name` = '{$data['name']}',
				      `addr` = '{$data['addr']}',
				      `tel` = '{$data['tel']}',
				      `fax` = '{$data['fax']}'
				  WHERE `complex_code_pk` = '{$complexCodePk}'
				 ";

        return $query;
    }

    /**
     * 마감일 변경
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $closingDay
     *
     * @return string $query
     */
    public function updateClosingDay(string $complexCodePk, int $option, int $closingDay) : string
    {
        $closingDayColumns = $this->closingDayColumns;
        $closingDayColumn = $closingDayColumns[$option];

        $query = "UPDATE `bems_complex` 
                  SET `{$closingDayColumn}` = '{$closingDay}'
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 설정 - 에너지 단가 관리
    //------------------------------------------------------------------------------------------------------------
    /**
     * 설정 > 에너지 단가 관리 (전기)
     *
     * @param string $complexCodePk
     * @param int $startPage
     * @param int $endPage
     * @param string $type
     *
     * @return string
     */
    public function getUnitCosts(string $complexCodePk, int $startPage, int $endPage, string $type = '') : string
    {
        if  ($type == "electric") {
            $query = "SELECT '전기' `energy_type_name`,
							 'electric' `energy_type`,
							 'kWh' `energy_type_unit`,
							 `buc`.`cost_pk`,
							 IFNULL(DATE_FORMAT(`buc`.`start_date`, '%Y-%m-%d'), '-') `start_date`,
							 IFNULL(DATE_FORMAT(`buc`.`end_date`, '%Y-%m-%d'), '-') `end_date`,
							 `buc`.`g_level`,
							 `buc`.`used`,
							 IFNULL(`buc`.`base_price`, 0) `base_price`,
							 IFNULL(`buc`.`unit_cost`, 0) `unit_cost`,
							 `buc`.`complex_code_pk`,
							 `bc`.`closing_day_electric`,
							 `bc`.`closing_day_gas`,
							 `bc`.`closing_day_water`,
							 `bc`.`closing_day_heating`,
							 `bc`.`closing_day_hotwater`
					  FROM `bems_unit_cost_electric` `buc`
						INNER JOIN `bems_complex` `bc`
							ON `buc`.`complex_code_pk` = `bc`.`complex_code_pk`
					  WHERE `buc`.`complex_code_pk` = '{$complexCodePk}'
					  AND `buc`.`fg_del` = 'n'
					  ORDER BY `buc`.`g_level` ASC
					  LIMIT {$startPage}, {$endPage}";
        } else {
            $query = "SELECT CASE WHEN `buc`.`energy_type` = 'gas' THEN '가스' 
								  WHEN `buc`.`energy_type` = 'water' THEN '수도'
								  WHEN `buc`.`energy_type` = 'heating' THEN '급탕 ' 
								  WHEN `buc`.`energy_type` = 'hotwater' THEN '난방'
								  ELSE '에러' END `energy_type_name`,
							 CASE WHEN `buc`.`energy_type` = 'gas' THEN 'm3' 
								  WHEN `buc`.`energy_type` = 'water' THEN 'm3'
								  WHEN `buc`.`energy_type` = 'heating' THEN 'm3' 
								  WHEN `buc`.`energy_type` = 'hotwater' THEN 'kWh'
								  ELSE '에러' END `energy_type_unit`,
							 `energy_type`,
							 `buc`.`cost_pk`,
							 `buc`.`complex_code_pk`,
							 IFNULL(DATE_FORMAT(`buc`.`start_date`, '%Y-%m-%d'), '-') `start_date`,
							 IFNULL(DATE_FORMAT(`buc`.`end_date`, '%Y-%m-%d'), '-') `end_date`,
							 '-' `g_level`,
							 '-' `used`,
							 IFNULL(ROUND(`buc`.`base_price`,1), 0) `base_price`,
							 IFNULL(ROUND(`buc`.`unit_cost`,1), 0) `unit_cost`,
							 `bc`.`closing_day_electric`,
							 `bc`.`closing_day_gas`,
							 `bc`.`closing_day_water`,
							 `bc`.`closing_day_heating`,
							 `bc`.`closing_day_hotwater`
					  FROM `bems_unit_cost` `buc`
						INNER JOIN `bems_complex` `bc`
							ON `buc`.`complex_code_pk` = `bc`.`complex_code_pk`
					  WHERE `buc`.`complex_code_pk` = '{$complexCodePk}'
					  AND `buc`.`fg_del` = 'n'
					  ORDER BY `buc`.`energy_type` ASC
					  LIMIT {$startPage}, {$endPage}";
        }

        return $query;
    }

    /**
     * 설정 > 에너지 단가 관리 데이터 카운트
     *
     * @param string $complexCodePk
     * @param int $startPage
     * @param int $endPage
     * @param string $type
     *
     * @return string
     */
    public function getUnitCostDataCount(string $complexCodePk, int $startPage, int $endPage, string $type = '') : string
    {
        if ($type == "electric") {
            $query = "SELECT COUNT(`complex_code_pk`) `cnt` 
					  FROM `bems_unit_cost_electric`
					  WHERE `complex_code_pk` = '{$complexCodePk}'
					  AND `fg_del` = 'n'
					  LIMIT {$startPage}, {$endPage}";
        } else {
            $query = "SELECT COUNT(`complex_code_pk`) `cnt` 
					  FROM `bems_unit_cost`
					  WHERE `complex_code_pk` = '{$complexCodePk}'
					  AND `fg_del` = 'n'
					  LIMIT {$startPage}, {$endPage}";
        }

        return $query;
    }

    /**
     * 설정 > 에너지 단가 관리 삭제
     *
     * @param string $complexCodePk
     * @param string $energyType
     * @param int $costPk
     *
     * @return string
     */
    public function deleteUnitPrice(string $complexCodePk, string $energyType, int $costPk) : string
    {
        if ($energyType == 'electric') {
            $query = "update `bems_unit_cost_electric` 
						SET `fg_del` = 'y'
						WHERE `cost_pk` = '{$costPk}'
						AND `fg_del` = 'n'
						AND `complex_code_pk` = '{$complexCodePk}'";
        } else {
            $query = "update `bems_unit_cost` 
						SET `fg_del` = 'y'
						WHERE `cost_pk` = '{$costPk}'
						AND `fg_del` = 'n'
						AND `complex_code_pk` = '{$complexCodePk}'";
        }

        return $query;
    }

    /**
     * 설정 > 에너지 단가 관리 조회
     *
     * @param string $complexCodePk
     * @param string $energyType
     * @param int $costPk
     *
     * @return string
     */
    public function getUnitPrice(string $complexCodePk, string $energyType, int $costPk) : string
    {
        if ($energyType == 'electric') {
            $query = "SELECT DATE_FORMAT(`start_date`, '%Y-%m-%d') `start_date`,
							 DATE_FORMAT(`end_date`,  '%Y-%m-%d') `end_date`,
							 `g_level`,
							 `used`,
							 `base_price`,
							 `unit_cost`,
							 `complex_code_pk`,
							 'electric' `energy_type`,
							 `cost_pk`
					  FROM `bems_unit_cost_electric` 
					  WHERE `fg_del` = 'n'
					  AND `cost_pk` = '{$costPk}'
					  AND `complex_code_pk` = '{$complexCodePk}'";
        } else {
            $query = "SELECT `energy_type`,
							 `cost_pk`,
							 `complex_code_pk`,
							 IFNULL(`base_price`, 0) `base_price`,
							 IFNULL(`unit_cost`, 0) `unit_cost`
					  FROM `bems_unit_cost` 
					  WHERE `fg_del` = 'n'
					  AND `cost_pk` = '{$costPk}'
					  AND `complex_code_pk` = '{$complexCodePk}'";
        }

        return $query;
    }

    /**
     * 설정 > 에너지 단가 관리 수정
     *
     * @param int $costPk
     * @param string $energyType
     * @param array $params
     *
     * @return string
     */
    public function updateUnitPrice(int $costPk, string $energyType, array $params) : string
    {
        if ($energyType == 'electric') {
            $startDate = str_replace('-', '', $params['start_date']);
            $endDate = str_replace('-', '', $params['end_date']);

            $query = "UPDATE `bems_unit_cost_electric`
					  SET `used` = '{$params['used']}',
						  `base_price` = '{$params['base_price']}',
						  `unit_cost` = '{$params['unit_cost']}',
						  `start_date` = '{$startDate}',
						  `end_date` = '{$endDate}',
						  `updator` = '{$params['ss_pk']}',
						  `update_date` = NOW()
					  WHERE `cost_pk` = '{$costPk}'
					 ";
        } else {
            $query = "UPDATE `bems_unit_cost`
					  SET `base_price` = '{$params['base_price']}',
						  `unit_cost` = '{$params['unit_cost']}',
						  `updator` = '{$params['ss_pk']}',
						  `update_date` = NOW()
					  WHERE `cost_pk` = '{$costPk}'
				     ";
        }

        return $query;
    }

    /**
     * 설정 > 에너지 단가 관리 추가 전 중복검사
     *
     * @param string $energyType
     * @param array $params
     *
     * @return string
     */
    public function getIsOverlapEnergyPrice(string $energyType, array $params) : string
    {
        if ($energyType == 'electric') {
            $query = "SELECT COUNT(`cost_pk`) `cnt`
					  FROM `bems_unit_cost_electric`
					  WHERE `complex_code_pk` = '{$params['complex_code_pk']}'
					  AND `g_level` = '{$params['g_level']}'
					  AND `fg_del` = 'n'";
        } else {
            $query = "SELECT COUNT(`cost_pk`) `cnt`
					  FROM `bems_unit_cost`
					  WHERE `complex_code_pk` = '{$params['complex_code_pk']}'
					  AND `energy_type` = '{$energyType}'
					  AND `fg_del` = 'n'";
        }

        return $query;
    }

    /**
     * 설정 > 에너지 단가 관리 추가
     *
     * @param string $energyType
     * @param array $params
     *
     * @return string
     */
    public function insertUnitPrice(string $energyType, array $params) : string
    {
        if ($energyType == 'electric') {
            $params['start_date'] = str_replace('-', '', $params['start_date']);
            $params['end_date'] = str_replace('-', '', $params['end_date']);

            $query = "INSERT INTO `bems_unit_cost_electric`
						SET `complex_code_pk` = '{$params['complex_code_pk']}',
							`admin_id` = '{$params['ss_id']}',
							`start_date` = '{$params['start_date']}',
							`end_date` = '{$params['end_date']}',
							`g_level` = '{$params['g_level']}',
							`used` = '{$params['used']}',
							`base_price` = '{$params['base_price']}',
							`unit_cost` = '{$params['unit_cost']}'";
        } else {
            $query = "INSERT INTO `bems_unit_cost`
						SET `complex_code_pk` = '{$params['complex_code_pk']}',
							`energy_type` = '{$energyType}',
							`admin_id` = '{$params['ss_id']}',
							`base_price` = '{$params['base_price']}',
							`unit_cost` = '{$params['unit_cost']}'";
        }

        return $query;
    }

    // ------------------------------------------------------------------------------------------------------------
    // 설정 > 계측기 현황  (BEMS 원격검침 시 필요)
    //------------------------------------------------------------------------------------------------------------
    /**
     * 층별 장애 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQuerySelectInstrumentStatusByFloor(string $complexCodePk) : string
    {
        $sensorTables = $this->sensorTableNames;

        $query = "SELECT `home`.`home_grp_pk`,
                         CASE WHEN `home`.`home_grp_pk` LIKE 'B%' THEN 1 ELSE 2 END `home_grp_seq`, 
		                 COUNT(`T`.`home_ho_pk`) AS `instrument_count` 		
                  FROM `bems_home` AS `home` 
	                 INNER JOIN (
	              ";

        foreach ($sensorTables as $option => $table) {
            $sensorTable = $sensorTables[$option];

            $query .= "SELECT `sensor_sn`,
                              `complex_code_pk`,
                              `home_dong_pk`,
                              `home_ho_pk`
                       FROM `{$sensorTable}` 
                       WHERE `complex_code_pk` = '{$complexCodePk}'
                      ";

            if ($option < count($sensorTables) - 1) {
                $query .= " UNION ALL ";
            }
        }

        $query .= ") T";

        $query .= "	
                ON `home`.`complex_code_pk` = `T`.`complex_code_pk`
                AND `home`.`home_dong_pk` = `T`.`home_dong_pk`
                AND `home`.`home_ho_pk` = `T`.`home_ho_pk`
            WHERE `home`.`complex_code_pk` = '{$complexCodePk}'
            AND `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
            GROUP BY `home`.`home_grp_pk`
            ORDER BY `home_grp_seq` ASC, `home`.`home_grp_pk` ASC
        ";

        return $query;
    }

    /**
     * 현재 발생 세부 장애 데이터 추출
     *
     * @param string $complexCodePk
     * @param string $floorQuery
     * @param int $startPage
     * @param int $endPage
     *
     * @return string
     */
    public function getQuerySelectInstrumentError(string $complexCodePk, string $floorQuery, int $startPage, int $endPage) : string
    {
        $pageQuery = '';
        if ($startPage > -1 && $endPage > -1) {
            $pageQuery = "LIMIT {$startPage},{$endPage}";
        }

        $query = "SELECT `log`.`home_dong_pk`,
                         `log`.`home_ho_pk`,
                         `search`.`home_grp_pk`,
                         `log`.`sensor_type`,
                         `log`.`sensor_sn`,
                         case when `log`.`alarm_on_off` = 'on' then '오류' ELSE '정상' END `alarm_on_off`,
                         IFNULL(`log`.`alarm_msg`, '') AS `alarm_msg`
                   FROM `bems_home` AS `search`
                      LEFT JOIN `bems_alarm_log` AS `log`
                          ON `search`.`complex_code_pk` = `log`.`complex_code_pk`
                          AND `search`.`home_dong_pk` = `log`.`home_dong_pk`
                          AND `search`.`home_ho_pk` = `log`.`home_ho_pk`
                   WHERE `log`.`complex_code_pk` = '{$complexCodePk}' 
                   AND `log`.`alarm_on_off` = 'on'
                   {$floorQuery}
                   ORDER BY `log`.`alarm_on_off` DESC, `search`.`home_grp_pk` ASC
                   {$pageQuery}
                 ";

        return $query;
    }

    /**
     * 장애 데이터 카운트
     *
     * @param string $complexCodePk
     * @param string $floorQuery
     * @param bool $isSelected
     *
     * @return string
     */
    public function getQuerySelectInstrumentErrorCount(string $complexCodePk, string $floorQuery, bool $isSelected = true) : string
    {
        $groupQuery = '';

        if ($isSelected === true) {
            $groupQuery = " GROUP BY `home`.`home_grp_pk`";
        }

        $query = "SELECT `home`.`home_grp_pk`,
 			             COUNT(`search`.`home_ho_pk`) AS `defect_count`
                  FROM `bems_home` AS `home`
                      LEFT JOIN `bems_alarm_log` AS `search`
                         ON `home`.`complex_code_pk` = `search`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `search`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `search`.`home_ho_pk`
                  WHERE `search`.`complex_code_pk` = '{$complexCodePk}' 
                  AND `search`.`alarm_on_off` = 'on'
                  {$floorQuery}
                  {$groupQuery}
                 ";

        return $query;
    }

    /**
     * 계측기 세부상태 리스트 조회
     *
     * @param string $complexCodePk
     * @param int $startPage
     * @param int $endPage
     * @param string $floorQuery
     *
     * @return string
     */
    public function getQuerySelectInstrumentDetailStatus(string $complexCodePk, int $startPage, int $endPage, string $floorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTypes = $this->sensorTypes;

        $pageQuery = '';
        if ($startPage > -1 && $endPage > -1) {
            $pageQuery = "LIMIT {$startPage}, {$endPage}";
        }

        $query = "SELECT `search`.`home_grp_pk`,
                         CASE WHEN `search`.`home_grp_pk` LIKE 'B%' THEN 1 ELSE 2 END `home_grp_seq`, 
                         IFNULL(`memo`, '') AS `memo`,
                         `T`.`sensor_type`,
                         `T`.`sensor_sn`,
                         IFNULL(`T`.`installed_date`, '0000-00-00') AS `installed_date`	
                  FROM `bems_home` AS `search` 
	                 INNER JOIN (
	              ";

        foreach ($sensorTables as $option => $table) {
            $sensorTable = $sensorTables[$option];
            $sensorType = $sensorTypes[$option];

            $query .= "SELECT `sensor_sn`,
                              `complex_code_pk`,
                              `home_dong_pk`,
                              `home_ho_pk`,
                              DATE_FORMAT(`installed_date`, '%Y-%m-%d') AS `installed_date`,
                              `memo`,
                              '{$sensorType}' AS `sensor_type`
                       FROM `{$sensorTable}` 
                       WHERE `complex_code_pk` = '{$complexCodePk}'
                      ";

            if ($option < count($sensorTables) -1) {
                $query .= " UNION ALL ";
            }
        }

        $query .= ") T";

        $query .= "	
                ON `search`.`complex_code_pk` = `T`.`complex_code_pk`
                AND `search`.`home_dong_pk` = `T`.`home_dong_pk`
                AND `search`.`home_ho_pk` = `T`.`home_ho_pk`
            WHERE `search`.`complex_code_pk` = '{$complexCodePk}'
            AND `search`.`home_grp_pk` <> 'all'
            {$floorQuery}
            ORDER BY `home_grp_seq` ASC , `search`.`home_grp_pk` ASC
            {$pageQuery}
        ";

        return $query;
    }

    /**
     * 등록된 계측기 총 갯수 조회
     *
     * @param string $complexCodePk
     * @param string $floorQuery
     *
     * @return string
     */
    public function getQuerySelectInstrumentCount(string $complexCodePk, string $floorQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTypes = $this->sensorTypes;

        $query = "SELECT `T`.`sensor_sn`	
                  FROM `bems_home` AS `search` 
	                 INNER JOIN (
	              ";

        foreach ($sensorTables as $option => $table) {
            $sensorTable = $sensorTables[$option];
            $sensorType = $sensorTypes[$option];

            $query .= "SELECT `sensor_sn`,
                              `home_dong_pk`,
                              `home_ho_pk`,
                              '{$sensorType}' AS `energy_type`
                       FROM `{$sensorTable}` 
                       WHERE `complex_code_pk` = '{$complexCodePk}'
                      ";

            if ($option < count($sensorTables) -1) {
                $query .= " UNION ALL ";
            }
        }

        $query .= ") T";

        $query .= "	
                ON `search`.`home_dong_pk` = `T`.`home_dong_pk`
                AND `search`.`home_ho_pk` = `T`.`home_ho_pk`
            WHERE `search`.`complex_code_pk` = '{$complexCodePk}'
            AND `search`.`home_grp_pk` <> 'all'
            {$floorQuery}
            ORDER BY `search`.`home_grp_pk` ASC
        ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 관리자설정
    //------------------------------------------------------------------------------------------------------------
    /**
     * 건물관리 리스트
     *
     * @param int $startPage
     * @param int $endPage
     *
     * @return string
     */
    public function getAllRorenList(int $startPage, int $endPage) : string
    {
        $query = "SELECT `bc`.`complex_code_pk`,
						 `bc`.`name`,
						 `bc`.`home_cnt`,
						 `bc`.`home_dong_cnt`,
						 `bc`.`addr`,
						 `bc`.`tel`,
						 `bc`.`fax`,
						 `bc`.`lat`,
						 `bc`.`lon`,
						 `bc`.`closing_day_electric`,
						 `bc`.`closing_day_gas`,
						 `bc`.`closing_day_water`,
						 IFNULL(`ba`.`email`, '') `email`,
						 IFNULL(`ba`.`admin_pk`, '') `admin_pk`
				  FROM `bems_complex` `bc`
					LEFT JOIN `bems_admin` `ba`
						ON `bc`.`complex_code_pk` = `ba`.`complex_code_pk`
						AND `bc`.`manager` = `ba`.`name`
				  WHERE `bc`.`fg_del` = 'n'
				  GROUP BY `bc`.`complex_code_pk`
				  ORDER BY `bc`.`complex_code_pk` ASC
				  LIMIT {$startPage}, {$endPage}";

        return $query;
    }

    /**
     * 건물관리 특정 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getRorenData(string $complexCodePk) : string
    {
        $query = "SELECT `bc`.`complex_code_pk`,
						 `bc`.`name`,
						 `bc`.`home_cnt`,
						 `bc`.`home_dong_cnt`,
                         `bc`.`floor_cnt`,
						 `bc`.`addr`,
						 `bc`.`tel`,
						 `bc`.`fax`,
						 `bc`.`lat`,
						 `bc`.`lon`,
						 `bc`.`closing_day_electric`,
						 `bc`.`closing_day_gas`,
						 `bc`.`closing_day_water`,
						 IFNULL(`ba`.`email`, '') `email`,
						 IFNULL(`ba`.`admin_pk`, '') `admin_pk`
				  FROM `bems_complex` `bc`
					LEFT JOIN `bems_admin` `ba`
						ON `bc`.`complex_code_pk` = `ba`.`complex_code_pk`
						AND `bc`.`manager` = `ba`.`name`
				  WHERE `bc`.`fg_del` = 'n'
				  AND `bc`.`complex_code_pk` = '{$complexCodePk}'
				";

        return $query;
    }

    /**
     * 건물관리 데이터 갯수 조회
     *
     * @return string
     */
    public function getAllRorenCount() : string
    {
        $query = "SELECT COUNT(`complex_code_pk`) `cnt`
				  FROM `bems_complex`
				  WHERE `fg_del` = 'n'
				 ";

        return $query;
    }

    /**
     * 건물 관리 삭제
     *
     * @param string $complexCodePk
     * @param int $adminPk
     *
     * @return string
     */
    public function deleteRorenData(string $complexCodePk, int $adminPk) : string
    {
        $query = "UPDATE `bems_complex`
					SET `fg_del` = 'y',
						`update_date` = NOW(),
						`updator` = '{$adminPk}'
					WHERE `complex_code_pk` = '{$complexCodePk}'
				";

        return $query;
    }

    /**
     *건물 관리 수정
     *
     * @param array $params
     *
     * @return string
     */
    public function updateComplexs(array $params) : string
    {
        $query = "UPDATE `bems_complex` SET 
				   `name` = '{$params['name']}',
				   `addr` = '{$params['addr']}',
				   `floor_cnt` = '{$params['home_dong_cnt']}',
				   `tel` = '{$params['tel']}',
				   `fax` = '{$params['fax']}',
				   `lat` = '{$params['lat']}',
				   `lon` = '{$params['lon']}',
				   `closing_day_electric` = '{$params['closing_day_electric']}',
				   `update_date` = NOW(),
				   `updator` = '{$params['ss_pk']}'
				  WHERE `complex_code_pk` = '{$params['complex_code_pk']}'
				";

        return $query;
    }

    /**
     * 건물 등록 시 중복 검사
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getIsOverlapComplexCode(string $complexCodePk) : string
    {
        $query = "SELECT COUNT(`complex_code_pk`) `cnt`
				  FROM `bems_complex`
				  WHERE `complex_code_pk` = '{$complexCodePk}'
				";

        return $query;
    }

    /**
     * 건물 추가
     *
     * @param array $params
     *
     * @return string
     */
    public function insertComplexs(array $params) : string
    {
        $query = "INSERT INTO `bems_complex` 
                    SET `complex_code_pk` = '{$params['complex_code_pk']}',
                        `name` = '{$params['name']}',
                        `addr` = '{$params['addr']}',
                        `floor_cnt` = '{$params['home_dong_cnt']}',
                        `tel` = '{$params['tel']}',
                        `fax` = '{$params['fax']}',
                        `lat` = '{$params['lat']}',
                        `lon` = '{$params['lon']}',
                        `closing_day_electric` = '{$params['closing_day_electric']}',
                        `creator` = '{$params['ss_pk']}'
                 ";

        return $query;
    }

    /**
     * [공통] 건물 목록  조회 후 selectBox에 표시
     *
     * @return string
     */
    public function getBuildingAllList() : string
    {
        $query = "SELECT `complex_code_pk`,
						 `name`
				  FROM `bems_complex`
				  WHERE `fg_del` = 'n'
				  ORDER BY `complex_code_pk` ASC
				 ";

        return $query;
    }

    /**
     * 로그인 로그 리스트
     *
     * @param string $complexCodePk
     * @param array $params
     * @param int $startPage
     * @param int $endPage
     *
     * @return string
     */
    public function getLoginLog(string $complexCodePk, array $params, int $startPage, int $endPage) : string
    {
        $p = $params;

        // 검색조건
        $searchQuery = "";

        // 로그인 성공여부 검색하기
        if (empty($p['search_status_select']) === false) {
            $searchQuery .= " AND `log`.`fg_login` = '{$p['search_status_select']}'";
        }

        // 컬럼 검색
        if (empty($p['search_select']) === false && empty($p['search_name']) === false) {
            if ($p['search_select'] == 1) {
                $searchQuery .= " AND `adm`.`admin_id` = '{$p['search_name']}'";
            }
        }

        $query = "SELECT `log`.`log_date`,
						 `log`.`ip_addr`,
						 `log`.`user_agent`,
						 CASE WHEN `log`.`fg_login` = 'y' THEN '성공' ELSE '실패' END `fg_login`,
						 `adm`.`admin_id`,
						 `adm`.`name`
				  FROM `bems_admin_login_log` AS `log`
				     LEFT JOIN `bems_admin` AS `adm`
						ON `log`.`admin_id` = `adm`.`admin_id`
				  WHERE DATE_FORMAT(`log`.`log_date`, '%Y-%m-%d') >= '{$params['start_date']}'
				  AND DATE_FORMAT(`log`.`log_date`, '%Y-%m-%d') <= '{$params['end_date']}'
				  AND `adm`.`complex_code_pk` = '{$complexCodePk}'
				  AND `log`.`fg_login` IS NOT NULL
				  {$searchQuery}
				  ORDER BY `log`.`log_pk` DESC
				  LIMIT {$startPage}, {$endPage}
				 ";

        return $query;
    }

    /**
     * 로그인 로그 카운트
     *
     * @param string $complexCodePk
     * @param array $params
     *
     * @return string
     */
    public function getLoginLogCount(string $complexCodePk, array $params) : string
    {
        $p = $params;

        // 검색조건
        $searchQuery = "";

        // 로그인 성공여부 검색하기
        if (!empty($p['search_status_select'])) {
            $searchQuery .= " AND `log`.`fg_login` = '{$params['search_status_select']}'";
        }

        // 컬럼 검색
        if (!empty($p['search_select']) && !empty($p['search_name'])) {
            if ($p['search_select'] == 1) {
                $searchQuery .= " AND `adm`.`admin_id` = '{$p['search_name']}'";
            }
        }

        $query = "SELECT COUNT(`log`.`log_pk`) 'cnt'
				  FROM `bems_admin_login_log` AS `log`
					LEFT JOIN `bems_admin` AS `adm`
						ON `log`.`admin_id` = `adm`.`admin_id`
				  WHERE DATE_FORMAT(`log`.`log_date`, '%Y-%m-%d') >= '{$params['start_date']}'
				  AND DATE_FORMAT(`log`.`log_date`, '%Y-%m-%d') <= '{$params['end_date']}'
				  AND `adm`.`complex_code_pk` = '{$complexCodePk}'
				  AND `log`.`fg_login` IS NOT NULL
				  {$searchQuery}";

        return $query;
    }

    /**
     * 권한관리 리스트
     *
     * @param array $params
     * @param int $startPage
     * @param int $endPage
     *
     * @return string
     */
    public function getAutority(array $params, int $startPage, int $endPage) : string
    {
        $p = $params;

        // 검색조건
        $searchQuery = "";

        // 권한 가져오기
        if (!empty($p['authority_type'])) {
            $searchQuery .= " AND `ba`.`login_level` = '{$params['authority_type']}'";
        }

        // 컬럼 검색
        if (!empty($p['search_select']) && !empty($p['search_name'])) {
            if ($p['search_select'] == 1) {
                $searchQuery .= " AND `ba`.`name` = '{$p['search_name']}'";
            }
        }

        $query = "SELECT `bc`.`name` `complex_name`, 
						 `ba`.`admin_pk`,
						 `ba`.`admin_id`,
						 `ba`.`name` `admin_name`,
						 IFNULL(`ba`.`hp`,' ') `hp`,
						 IFNULL(`ba`.`email`, ' ') `email`,
						 DATE_FORMAT(`ba`.`reg_date`, '%Y-%m-%d') `reg_date`,
						 CASE WHEN `ba`.`fg_connect` = 'y' THEN '허용' ELSE '거부' END `fg_connect`,
						 CASE WHEN `ba`.`fg_del` = 'y' THEN '삭제' ELSE '정상' END `fg_del`,
						 CASE WHEN `ba`.`login_level` = 70 THEN '게스트'
						      WHEN `ba`.`login_level` = 80 THEN '단지관리자'
							  WHEN `ba`.`login_level` = 90 THEN '업체관리자'
							  WHEN `ba`.`login_level` = 100 THEN '최고관리자' ELSE '미등록' END `login_level`
				  FROM `bems_admin` `ba`
					LEFT JOIN `bems_complex` `bc`
						ON `ba`.`complex_code_pk` = `bc`.`complex_code_pk`
				  WHERE `ba`.`complex_code_pk` = '{$params['roren_type']}'
				  AND `bc`.`fg_del` = 'n'
				  {$searchQuery}
				  ORDER BY `ba`.`fg_del` DESC, `ba`.`name` ASC, `ba`.`admin_pk` ASC";

        return $query;
    }

    /**
     * 권한관리 데이터 카운트
     *
     * @param array $params
     *
     * @return string
     */
    public function getAuthorityCount(array $params) : string
    {
        $p = $params;

        // 검색조건
        $searchQuery = "";

        // 권한 가져오기
        if (!empty($p['authority_type'])) {
            $searchQuery .= " AND `ba`.`login_level` = '{$params['authority_type']}'";
        }

        // 컬럼 검색
        if (!empty($p['search_select']) && !empty($p['search_name'])) {
            if ($p['search_select'] == 1) {
                $searchQuery .= " AND `ba`.`name` = '{$p['search_name']}'";
            }
        }

        $query = "SELECT COUNT(`ba`.`admin_pk`) `cnt`
				  FROM `bems_admin` `ba`
					LEFT JOIN `bems_complex` `bc`
						ON `ba`.`complex_code_pk` = `bc`.`complex_code_pk`
				  WHERE `ba`.`complex_code_pk` = '{$params['roren_type']}'
				  AND `bc`.`fg_del` = 'n'
				  AND `ba`.`fg_del` = 'n'
				  {$searchQuery}";

        return $query;
    }

    /**
     * 권한 관리에서  유저(관리자) 정보 조회
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getAuthorityData(int $adminPk) : string
    {
        $query = "SELECT `admin_pk`,
						 `name`,
						 `password`,
						 `complex_code_pk`,
						 `login_level`,
						 `fg_del`,
						 `fg_connect`,
						 `hp`,
						 `email`,
						 `admin_id`
				  FROM `bems_admin`
				  WHERE `admin_pk` = '{$adminPk}'
				";

        return $query;
    }

    /**
     * 로그인 유저의 레벨 정보 조회
     *
     * @param int $adminPk
     *
     * @return string
     */
    public function getAdminLoginLevel(int $adminPk) : string
    {
        $query = "SELECT `login_level`
                  FROM `bems_admin`
                  WHERE `admin_pk` = '{$adminPk}'
                  AND `first_login_date` IS NOT NULL
                 ";

        return $query;
    }

    /**
     * 권한 관리에서  유저(관리자) 정보 수정
     *
     * @param array $params
     *
     * @return string
     */
    public function updateAuthority(array $params) : string
    {
        $query = "UPDATE `bems_admin`
				  SET `name` = '{$params['name']}',
					  `login_level` = '{$params['login_level']}',
					  `hp` = '{$params['hp']}',
					  `email` = '{$params['email']}',
					  `fg_connect` = '{$params['fg_connect']}',
					  `fg_del` = '{$params['fg_del']}',
					  `updator` = '{$params['ss_pk']}',
					  `update_date` = NOW()
				  WHERE `admin_pk` = '{$params['admin_pk']}'
				 ";

        return $query;
    }

    /**
     * 권한 관리에서  유저(관리자) 생성 시 계정 중복검사
     *
     * @param string $id
     *
     * @return string
     */
    public function getIsOverlapAuthority(string $id) : string
    {
        $query = "SELECT COUNT(`admin_id`) `cnt`
				  FROM `bems_admin`
				  WHERE `admin_id` = '{$id}'";

        return $query;
    }

    /**
     * 권한 관리에서 유저(관리자) 정보 추가
     *
     * @param array $params
     * @param string $firstLoginDateQuery
     *
     * @return string
     */
    public function insertAuthority(array $params, string $firstLoginDateQuery) : string
    {
        $query = "INSERT INTO `bems_admin`
					SET `complex_code_pk` = '{$params['popup_roren_type']}',
						`admin_id` = '{$params['admin_id']}',
						`password` = '{$params['password']}',
						`name` = '{$params['name']}',
						`login_level` = '{$params['login_level']}',
						`hp` = '{$params['hp']}',
						`email` = '{$params['email']}',
						`fg_connect` = '{$params['fg_connect']}',
						`fg_del` = '{$params['fg_del']}',
						`creator` = '{$params['ss_pk']}',
						`reg_date` = NOW()
                        {$firstLoginDateQuery}
				";

        return $query;
    }

    /**
     * 권한 관리에서 유저(관리자) 정보 삭제 시 카운트 체크
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getIsDeleteAuthority(string $complexCodePk) : string
    {
        $query = "SELECT COUNT(`admin_pk`) cnt
				  FROM `bems_admin`
				  WHERE `fg_del` = 'n'
				  AND `complex_code_pk` = '{$complexCodePk}'";

        return $query;
    }

    /**
     * 권한 관리에서  유저(관리자) 정보 삭제
     *
     * @param int $adminPk
     * @param int $ssPk
     *
     * @return string
     */
    public function deleteAuthority(int $adminPk, int $ssPk) : string
    {
        $query = "UPDATE `bems_admin`
					SET `fg_del` = 'y',
						`updator` = '{$ssPk}',
						`update_date` = NOW()
					WHERE `admin_pk` IN ('{$adminPk}')
				";

        return $query;
    }

    /**
     * 장비관리 리스트
     *
     * @param int $option
     * @param int $startPage
     * @param int $endPage
     * @param string $complexQuery
     * @param string $fgUseQuery
     * @param string $startDateQuery
     * @param string $endDateQuery
     *
     * @return string
     */
    public function getQuerySelectEquipmentList(int $option, int $startPage, int $endPage, string $complexQuery, string $fgUseQuery, string $startDateQuery, string $endDateQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $anomalyTables = Config::ANOMALY_TABLES;
        $anomalyTable = $anomalyTables[$option];

        $anomalyColumn = '';
        if (empty($anomalyTable) === false) {
            $anomalyColumn = ", CASE WHEN `sensor`.`fg_anomaly` = 0 THEN '정상' 
                                     WHEN `sensor`.`fg_anomaly` = 1 THEN '비정상' 
                                     WHEN `sensor`.`fg_anomaly` = -1 THEN '진단 불가'
                                     ELSE ''
                                     END 'fg_anomaly_name'
                              , `sensor`.`anomaly_score`
                             ";
        }

        $query = "SELECT `home`.`home_type`,
                         `home`.`home_dong_pk`,
                         `home`.`home_ho_pk`,
                         `home`.`home_grp_pk`,
                         `sensor`.`sensor_sn`,
                         IFNULL(`sensor`.`detail_spec`, '') AS `detail_spec`,    
                         IFNULL(`sensor`.`manage_level`, '') AS `manage_level`,
                         IFNULL(`sensor`.`check_period`, '') AS `check_period`,
                         IFNULL(DATE_FORMAT(`sensor`.`replace_date`, '%Y-%m-%d'), '0000-00-00') AS `replace_date`,
                         IFNULL(DATE_FORMAT(`sensor`.`lastest_check_date`, '%Y-%m-%d'), '0000-00-00') AS `lastest_check_date`,
                         IFNULL(DATE_FORMAT(`sensor`.`installed_date`, '%Y-%m-%d'), '0000-00-00') AS `installed_date`,
                         {$option} AS 'option'
                         {$anomalyColumn}
                  FROM `bems_home` AS `home`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
                  {$fgUseQuery}
                  {$complexQuery}
                  {$startDateQuery}
                  {$endDateQuery}
                  LIMIT {$startPage}, {$endPage}
                 ";

        return $query;
    }

    /**
     * 장비 관리 데이터 카운트
     *
     * @param int $option
     * @param string $complexQuery
     * @param string $fgUseQuery
     * @param string $startDateQuery
     * @param string $endDateQuery
     *
     * @return string
     */
    public function getQuerySelectEquipmentCount(int $option, string $complexQuery, string $fgUseQuery, string $startDateQuery, string $endDateQuery) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT COUNT(`sensor_sn`) `cnt`
                  FROM `bems_home` AS `home`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `home`.`home_grp_pk` NOT IN ('0M', 'ALL')
                  {$fgUseQuery}
                  {$complexQuery}
                  {$startDateQuery}
                  {$endDateQuery}
                  ";

        return $query;
    }

    /**
     * 장비 관리 상세 데이터 조회
     *
     * @param int $option
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQueryEquipmentDataBySensorNo(int $option, string $sensorNo) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT '{$option}' AS `option`,
                         `sensor`.`sensor_sn`,
                         `sensor`.`complex_code_pk`,
                         IFNULL(DATE_FORMAT(`sensor`.`installed_date`, '%Y-%m-%d'), '') AS `installed_date`,
                         `home`.`home_dong_pk`,
                         `home`.`home_ho_pk`,
                         `home`.`home_ho_nm`,
                         `sensor`.`fg_use`,
                         `home`.`home_type`,
                         `home`.`home_grp_pk`,
                         IFNULL(`home`.`home_grp_pk`, '-') AS `home_grp_pk`,
                         IFNULL(`sensor`.`detail_spec`, '') AS `detail_spec`,
                         IFNULL(`sensor`.`manage_level`, '') AS `manage_level`,
                         IFNULL(`sensor`.`check_period`, '') AS `check_period`,
                         IFNULL(DATE_FORMAT(`sensor`.`lastest_check_date`, '%Y-%m-%d'), '') AS `lastest_check_date`,
                         IFNULL(DATE_FORMAT(`sensor`.`replace_date`, '%Y-%m-%d'), '') AS `replace_date`
                  FROM `bems_home` AS `home`
                    LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `sensor`.`sensor_sn` = '{$sensorNo}'
                ";

        return $query;
    }

    /**
     * 홈 정보에 있는 지 확인
     * 
     * @param string $complexCodePk
     * @param string $homeType
     * @param string $homeDongPk
     * @param string $homeHoPk
     * @param string $homeGrpPk
     *
     * @return string
     */
    public function getQueryHomeInfoValidate(string $complexCodePk, string $homeType, string $homeDongPk, string $homeHoPk, string $homeGrpPk) : string
    {
        $query = "SELECT count(*) as `cnt`
                  FROM `bems_home`
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `home_type` = '{$homeType}'
                  AND `home_dong_pk` = '{$homeDongPk}'
                  AND `home_ho_pk` = '{$homeHoPk}'
                  AND `home_grp_pk` = '{$homeGrpPk}'
                 ";

        return $query;
    }

    /**
     * 장비 관리에서 동, 호, 층 정보가 현재 사용 중 인지 확인
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $homeDongPk
     * @param string $homeHoPk
     *
     * @return string
     */
    public function getQueryOverlapSensor(string $complexCodePk, int $option, string $homeDongPk, string $homeHoPk) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT COUNT(`sensor`.`sensor_sn`) `cnt`
                  FROM `{$sensorTable}` AS `sensor`
                  WHERE `sensor`.`fg_use` = 'y'
                  AND `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `sensor`.`home_dong_pk` = '{$homeDongPk}'
                  AND `sensor`.`home_ho_pk` = '{$homeHoPk}'
                 ";

        return $query;
    }

    /**
     * 장비 등록 시 센서번호(S/N) 중복 검사
     *
     * @param int $option
     * @param string $sensorNo
     *
     * @return string
     */
    public function getQueryIsOverlapSensorNo(int $option, string $sensorNo) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $query = "SELECT COUNT(`sensor_sn`) `cnt`
                  FROM `{$sensorTable}`
                  WHERE `sensor_sn` = '{$sensorNo}'
                 ";

        return $query;
    }

    /**
     * 장비 정보 수정
     *
     * @param int $option
     * @param array $params
     *
     * @return string
     */
    public function updateEquipment(int $option, array $params) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $addColumns = '';
        if (isset($params['popup_fg_use']) === true && empty($params['popup_fg_use']) === false) {
            $addColumns .= ", `fg_use` = '{$params['popup_fg_use']}'";
        }

        if (isset($params['popup_installed_date']) === true && empty($params['popup_installed_date']) === false) {
            $addColumns .= ", `installed_date` = '{$params['popup_installed_date']}'";
        }

        if (isset($params['popup_lastest_check_date']) === true && empty($params['popup_lastest_check_date']) === false) {
            $addColumns .= ", `lastest_check_date` = '{$params['popup_lastest_check_date']}'";
        }

        if (isset($params['popup_replace_date']) === true && empty($params['popup_replace_date']) === false) {
            $addColumns .= ", `replace_date` = '{$params['popup_replace_date']}'";
        }

        $query = "UPDATE `{$sensorTable}`
                    SET `detail_spec` = '{$params['popup_detail_spec']}',
                        `manage_level` = '{$params['popup_manage_level']}',
                        `check_period` = '{$params['popup_check_period']}'
                        {$addColumns}
                    WHERE `sensor_sn` = '{$params['popup_sensor_sn']}'
                  ";

        return $query;
    }

    /**
     * 장비 정보 등록
     *
     * @param int $option
     * @param array $params
     *
     * @return string
     */
    public function insertEquipment(int $option, array $params) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $addColumns = '';

        $popupFgUse = 'y';
        if (isset($params['popup_fg_use']) === true && empty($params['popup_fg_use']) === false) {
            $popupFgUse = $params['popup_fg_use'];
        }

        $addColumns .=  ", `fg_use` = '{$popupFgUse}'";

        if (isset($params['popup_installed_date']) === true && empty($params['popup_installed_date']) === false) {
            $addColumns .= ", `installed_date` = '{$params['popup_installed_date']}'";
        }

        if (isset($params['popup_lastest_check_date']) === true && empty($params['popup_lastest_check_date']) === false) {
            $addColumns .= ", `lastest_check_date` = '{$params['popup_lastest_check_date']}'";
        }

        if (isset($params['popup_replace_date']) === true && empty($params['popup_replace_date']) === false) {
            $addColumns .= ", `replace_date` = '{$params['popup_replace_date']}'";
        }

        $query = "INSERT INTO `{$sensorTable}`
                  SET `sensor_sn` = '{$params['popup_sensor_sn']}',
                      `complex_code_pk` = '{$params['popup_building_type']}',
                      `home_dong_pk` = '{$params['popup_select_apt_dong']}',
                      `home_ho_pk` = '{$params['popup_select_apt_home']}',
                      `reg_date` = NOW(),
                      `detail_spec` = '{$params['popup_detail_spec']}',
                      `manage_level` = '{$params['popup_manage_level']}',
                      `check_period` = '{$params['popup_check_period']}'
                      {$addColumns}
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 메뉴
    //------------------------------------------------------------------------------------------------------------
    /**
     * 메뉴 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getMenuData(string $complexCodePk) : string
    {
        $query = "SELECT `group`.`group_id`,
                         `group`.`name` AS `group_menu_name`,
                         `group`.`menu_order`,
                         `group`.`authority`,
                         `group`.`icon`,
                         `group`.`is_use_dashboard`,
                         IFNULL(`menu`.`menu_id`, 0) AS `menu_id`,
                         `menu`.`menu_order`,
                         `menu`.`name` AS `menu_name`,
                         CASE WHEN `menu`.`menu_order` IS NULL 
                              THEN `group`.`url` 
                              ELSE `menu`.`url` 
                              END `url`
                  FROM `bems_menu_groups` AS `group`
                     LEFT JOIN `bems_menus` AS `menu`
                        ON `group`.`group_id` = `menu`.`group_id`
                        AND `menu`.`is_use` = 'Y'
                  WHERE `group`.`complex_code_pk` = '{$complexCodePk}'
                  AND `group`.`is_use` = 'Y'
                  ORDER BY `group`.`menu_order`, `menu`.`menu_order`
                 ";

        return $query;
    }

    /**
     * 설정된 메뉴가 없는 경우 기본 메뉴 조회
     *
     * @return string
     */
    public function getDefaultMenuData() : string
    {
        $query = "SELECT `group`.`group_id`,
                         `group`.`name` `group_menu_name`,
                         `group`.`menu_order`,
                         `group`.`authority`,
                         `group`.`icon`,
                         `group`.`is_use_dashboard`,
                         IFNULL(`menu`.`menu_id`, 0) AS `menu_id`,
                         `menu`.`menu_order`,
                         `menu`.`name` `menu_name`,
                         CASE WHEN `group`.`url` IS NOT NULL THEN  `group`.`url` ELSE `menu`.`url` END `url`
                  FROM `bems_menu_groups` AS `group`
                    LEFT JOIN `bems_menus` AS `menu`
                        ON `group`.`group_id` = `menu`.`group_id`
                        AND `menu`.`is_use` = 'Y'
                  WHERE `group`.`is_default_menu` = 'Y'
                  AND `group`.`is_use` = 'Y'
                  GROUP BY `group`.`group_id`, `menu`.`menu_id`
                  ORDER BY `group`.`menu_order`, `menu`.`menu_order`
                ";

        return $query;
    }

    /**
     * 에너지원 버튼 메뉴 조회
     *
     * @param string $complexCodePk
     * @param int $groupId
     * @param int $menuId
     *
     * @return string
     */
    public function getEnergyButtonByMenu(string $complexCodePk, int $groupId, int $menuId) : string
    {
        $query = "SELECT `button_order`,
                         `reference_index`,
                         `button_id`,
                         `name`,
                         `key`
                  FROM `bems_energy_buttons`
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `group_id` = '{$groupId}'
                  AND `menu_id` = '{$menuId}'
                  AND `is_use` = 'Y'
                  ORDER BY `button_order` ASC";

        return $query;
    }

    /**
     * 단지마다 메뉴 그룹이 몇 개 존재하는지 조회
     *
     * @param string $complexCodePk
     * @return string
     */
    public function getMenuGroupCount(string $complexCodePk) : string
    {
        $query = "SELECT COUNT(`group_id`) AS `count`
                  FROM `bems_menu_groups`
                  WHERE `complex_code_pk` = '{$complexCodePk}'";

        return $query;
    }

    /**
     * 사이트 메인에 보여줄 대시보드 리스트 출력 (대시보드가 두개인 경우) 
     * - 메인에 보여주어야 하는 경우 order를 가장 낮은 번호로 할 것
     * - 대시보드가 여러개 일 수 있어서 is_use_dashboard 주석처리
     *
     * @param string $complexCodePk
     * @param string $baseMenuName
     *
     * @return string
     */
    public function getDashboardList(string $complexCodePk, string $baseMenuName = '대시보드') : string
    {
        $query = "SELECT IFNULL(`group`.`group_id`, 0) AS `group_id`,
                         IFNULL(`menu`.`menu_id`, 0) AS `menu_id`,
                         IFNULL(`menu`.`menu_order`, 1) AS `menu_order`,
                         CASE WHEN `group`.`url` IS NULL THEN `menu`.`url` ELSE `group`.`url` END `url`
                  FROM `bems_menu_groups` AS `group`
                     LEFT JOIN `bems_menus` AS `menu`
                        ON `group`.`group_id` = `menu`.`group_id`
                        AND `menu`.`url` = `group`.`url`       
                        AND `menu`.`is_use` = 'Y'
                  WHERE `group`.`complex_code_pk` = '{$complexCodePk}'
                  AND `group`.`name` = '{$baseMenuName}'
                  AND `group`.`is_use` = 'Y'
                  -- AND `group`.`is_use_dashboard` = 'Y'
                  ORDER BY `menu`.`menu_order` ASC
                ";

        return $query;
    }

    /**
     * 메뉴 그룹 테이블에서 아이디 정보 조회
     *
     * @param string $complexCodePk
     * @param string $menuName
     *
     * @return string
     */
    public function getMenuGroupIdx(string $complexCodePk, string $menuName) : string
    {
        $query = "SELECT `group_id`,
                         0 AS `menu_id`,
                         `url` 
                  FROM `bems_menu_groups` 
                  WHERE `complex_code_pk` = '{$complexCodePk}' 
                  AND `name` = '{$menuName}'
                 ";

        return $query;
    }

    /**
     * 메뉴 테이블에서 아이디 정보 조회
     *
     * @param string $complexCodePk
     * @param string $filePath
     *
     * @return string
     */
    public function getMenuIdx(string $complexCodePk, string $filePath) : string
    {
        $query = "SELECT `group_id`,
                         `menu_id`,
                         `url`
                  FROM `bems_menus`
                  WHERE `complex_code_pk` = '{$complexCodePk}'
                  AND `url` = '{$filePath}'
                ";

        return $query;
    }

    /**
     * 메뉴 그룹의 권한을 조회
     *
     * @param int $groupId
     *
     * @return string
     */
    public function getMenuGroupInfo(int $groupId) : string
    {
        $query = "SELECT `name`,
                         `authority` 
                  FROM `bems_menu_groups`
                  WHERE `group_id` = '{$groupId}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // System
    //------------------------------------------------------------------------------------------------------------
    /**
     * bems_db에 테이블에 존재하는지 확인
     *
     * @param string $dbname
     * @param string $findTableName
     *
     * @return string
     */
    public function getTableExist(string $dbname, string $findTableName) : string
    {
        $query = "SELECT `TABLE_NAME` `name`
                  FROM `information_schema`.`TABLES`
                  WHERE `TABLE_SCHEMA` = '{$dbname}'
                  AND `TABLE_NAME` LIKE '{$findTableName}%'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // ntek
    //------------------------------------------------------------------------------------------------------------
    /**
     * 모든 센서 조회 하기
     *
     * @param int $option
     * @param string $maker
     *
     * @return string $query
     */
    public function getQuerySensorNo(int $option, string $maker = 'ntek') : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $cncReferenceTables = Config::CNC_REFERENCE_TABLES;

        $makers = ['ntek', 'arklabs'];

        $solarQuery = '';
        $solarColumn = '';
        $preValColumn = '';

        if ($option === 11) {
            //ntek, arklabs 로 비교해서 가져옴
            //$solarQuery = " AND `sensor`.`inout` = 'O'";
            //$solarQuery = " AND `sensor`.`inout` IN ('O', 'B')";
            $solarColumn = " ,`sensor`.`inout`
                             , IFNULL(`sensor`.`replace_sensor_sn`, '') AS `replace_sensor_sn`
                           ";
        }

        if (in_array($option, $cncReferenceTables) === true) {
            $preValColumn = " , IFNULL(`sensor`.`pre_val`, 0) AS `pre_val`";
        }

        $makerQuery = " AND `sensor`.`maker` IN ('$makers[0]', '$makers[1]')";
        if (in_array($maker, $makers) === false) {
            $makerQuery = " AND `sensor`.`maker` = '{$maker}'";
        }

        // 건물정보 무등산은 제외한다. reti에서 받기 때문에..
        $query = "SELECT `sensor`.`complex_code_pk`,
                         `sensor`.`home_dong_pk`,
                         `sensor`.`home_ho_pk`,
                         `sensor`.`sensor_sn`,
                         `sensor`.`maker`
                         {$solarColumn}
                         {$preValColumn}
                  FROM `{$sensorTable}` AS `sensor` 
                  WHERE `sensor`.`fg_use` = 'y' 
                  {$makerQuery}
                  {$solarQuery}
                 ";

        return $query;
    }

    /**
     * 엔텍에서 5분전 데이터 조회
     *
     * @param string $sensorNo
     *
     * @return string $query
     */
    public function getQueryNtekMeterData(string $sensorNo) : string
    {
        $ntekTables = $this->ntekTables;

        $sensorTable = $ntekTables['sensor_table'];

        // 엔텍 센서 테이블 참조
        $query = "SELECT `sensor`.`sensor_sn`,
                         `sensor`.`val_date`,
                         `sensor`.`watt`,
                         `sensor`.`kwh_imp`,
                         IFNULL(`sensor`.`error_code`, 0) AS `error_code`,  
                         IFNULL(`sensor`.`pf`, 0.0) AS `pf`,
                         `sensor`.`all_data`
                  FROM `{$sensorTable}` AS `sensor`
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * bems_meter_ 데이터 추가
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param int $val
     * @param int $current
     * @param int $errorCode
     * @param array $columns
     *
     * @return string $query
     */
    public function getQueryInsertMeterTableByEquipment(int $option, string $sensorNo, string $valDate, int $val, int $current, int $errorCode, array $columns) : string
    {
        $rawTables = $this->rawTableNames;
        $rawTable = $rawTables[$option];

        $addQuery = '';
        if (isset($columns['pf']) === true && empty($columns['pf']) === false) {
            $addQuery = ", `pf` = '{$columns['pf']}'";
        }

        $query = "INSERT INTO `{$rawTable}`
                   SET `sensor_sn` = '{$sensorNo}',
                       `val_date` = '{$valDate}',
                       `total_wh` = '{$val}',
                       `error_code` = '{$errorCode}',
                       `current_w` = '{$current}'
                       {$addQuery}
                   ON DUPLICATE KEY UPDATE 
                       `sensor_sn` = '{$sensorNo}',
                       `val_date` = '{$valDate}',
                       `total_wh` = '{$val}',
                       `error_code` = '{$errorCode}',
                       `current_w` = '{$current}'
					   {$addQuery}
                   ";

        return $query;
    }

    public function getQueryInsertSensorData(string $complexCodePk, int $option, string $homeDongPk, string $homeHoPk, string $sensorNo, array $columns) : string
    {
        $sensorTableNames = $this->sensorTableNames;
        $sensorTable = $sensorTableNames[$option];

        $columnStr = "";
        foreach ($columns AS $column => $value) {
            $columnStr .= ", `{$column}` = '{$value}'";
        }

        $query = "INSERT INTO `{$sensorTable}`
                  SET `complex_code_pk` = '{$complexCodePk}',
                      `home_dong_pk` = '{$homeDongPk}',
                      `home_ho_pk` = '{$homeHoPk}',
                      `sensor_sn` = '{$sensorNo}'
                      {$columnStr}
                  ON DUPLICATE KEY UPDATE 
                     `complex_code_pk` = '{$complexCodePk}',
                     `home_dong_pk` = '{$homeDongPk}',
                     `home_ho_pk` = '{$homeHoPk}',
                     `sensor_sn` = '{$sensorNo}'
					 {$columnStr}
                 ";

        return $query;
    }

    /**
     * bems_sensor_ 업데이트
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param int $val
     * @param int $errorCode
     * @param string|null $allData
     *
     * @return string
     */
    public function getQueryUpdateSensorTableByEquipment(int $option, string $sensorNo, string $valDate, int $val, int $errorCode, ?string $allData) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $allDataSet = "";

        if (is_null($allData) === false) {
            $allDataSet = ",`all_data` = '{$allData}'";
        }

        $query = "UPDATE `{$sensorTable}` 
                   SET `val_date` = '{$valDate}', 
                       `val` = '{$val}',
                       `error_code` = '{$errorCode}'
                       {$allDataSet}
                   WHERE `sensor_sn` = '{$sensorNo}'
                   AND `maker` IN ('ntek', 'arklabs')
                  ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // cnc 데이터 수신
    //------------------------------------------------------------------------------------------------------------
    /**
     * CNC 테이블에서 5분전 데이터 조회
     *
     * @param string $sensorNo
     *
     * @return string $query
     */
    public function getQueryCncMeterData(string $sensorNo) : string
    {
        $cncTables = $this->cncTables;

        $sensorTable = $cncTables['sensor_table'];

        // 엔텍 센서 테이블 참조
        $query = "SELECT `sensor`.`sensor_sn`,
                         `sensor`.`val_date`,
                         IFNULL(`sensor`.`ch1_pulse_val`, 0) AS `ch1_pulse_val`,
                         IFNULL(`sensor`.`ch2_pulse_val`, 0) AS `ch2_pulse_val`,
                         IFNULL(`sensor`.`ch1_unit`, 0) AS `ch1_unit`,
                         IFNULL(`sensor`.`ch2_unit`, 0) AS `ch2_unit`,
                         `sensor`.`error_code`
                  FROM `{$sensorTable}` AS `sensor`
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * bems_meter_ 데이터 추가 (열량계)
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param float $val
     * @param int $errorCode
     *
     * @return string $query
     */
    public function getQueryInsertCncMeterTableByEquipment(int $option, string $sensorNo, string $valDate, float $val, int $errorCode) : string
    {
        $rawTables = $this->rawTableNames;
        $columnNames = $this->columnNames;

        $rawTable = $rawTables[$option];
        $column = $columnNames[$option];

        $columnInfo = "`{$column}` = '{$val}'";
        if ($option === 13) {
            $columnInfo = "`flow` = '{$val}', `{$column}` = '{$val}'";
        }

        $query = "INSERT INTO `{$rawTable}`
                   SET `sensor_sn` = '{$sensorNo}',
                       `val_date` = '{$valDate}',
					   `error_code` = '{$errorCode}',
                       {$columnInfo}
                   ON DUPLICATE KEY UPDATE 
                       `sensor_sn` = '{$sensorNo}',
                       `val_date` = '{$valDate}',
                       `error_code` = '{$errorCode}',
                       {$columnInfo}
                 ";

        return $query;
    }

    /**
     * bems_sensor_ 업데이트 (열량계)
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param float $val
     * @param int $errorCode
     *
     * @return string $query
     */
    public function getQueryUpdateCncSensorTableByEquipment(int $option, string $sensorNo, string $valDate, float $val, int $errorCode) : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $columnInfo = "`val` = '{$val}'";
        if ($option === 13) {
            $columnInfo = "`flow` = '{$val}', `flow_oil` = '{$val}'";
        }

        $query = "UPDATE `{$sensorTable}` 
                  SET `val_date` = '{$valDate}', 
				  	  `error_code` = '{$errorCode}',
                      {$columnInfo}
                  WHERE `sensor_sn` = '{$sensorNo}'
                  AND `maker` = 'cnc'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // Reti 데이터 수신
    //------------------------------------------------------------------------------------------------------------
    /**
     * 레티에서 5분전 데이터 조회
     *
     * @param string $groupId
     * @param string $serial
     * @param string $startDateHour
     * @param string $endDateHour
     *
     * @return string
     */
    public function getQuerySelectRetiMeterData(string $groupId, string $serial, string $startDateHour, string $endDateHour) : string
    {
        $query = "SELECT IFNULL(MAX(`raw_accumulate_power_consumption`), 0) AS `val`,
                         IFNULL(MAX(`val_date`), '00000000') AS `val_date`
                  FROM `lbems_mdmt_db`.`reti_group_data_2_raw_data`
                  WHERE `group_id` = '{$groupId}'
                  AND `serial` = '{$serial}'
                  AND `val_date` >= '{$startDateHour}'
                  AND `val_date` <= '{$endDateHour}'
                  GROUP BY CONCAT(`serial`, '_', `group_id`)
                 ";

        return $query;
    }

    /**
     * 레티 에서 데이터 수신 후 bems_meter_ 에 추가
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param int $val
     *
     * @return string
     */
    public function getQueryInsertMeterTableByRetiEquipment(int $option, string $sensorNo, string $valDate, int $val) : string
    {
        $rawTableNames = $this->rawTableNames;
        $columnNames = $this->columnNames;

        $rawTable = $rawTableNames[$option];
        $column = $columnNames[$option];

        $query = "INSERT INTO `{$rawTable}`
                  SET `sensor_sn` = '{$sensorNo}',
                      `val_date` = '{$valDate}',
                      `{$column}` = '{$val}',
                      `error_code` = 0
                  ON DUPLICATE KEY UPDATE 
                     `sensor_sn` = '{$sensorNo}',
                     `val_date` = '{$valDate}',
                     `{$column}` = '{$val}',
                     `error_code` = 0
                 ";

        return $query;
    }

    /**
     * 레티에서 데이터 수신 후 bems_sensor_에 업데이트
     *
     * @param int $option
     * @param string $sensorNo
     * @param string $valDate
     * @param int $val
     *
     * @return string
     */
    public function getQueryUpdateSensorTableByRetiEquipment(int $option, string $sensorNo, string $valDate, int $val) : string
    {
        $sensorTableNames = $this->sensorTableNames;
        $sensorTable = $sensorTableNames[$option];

        $query = "UPDATE `{$sensorTable}` 
                  SET `val_date` = '{$valDate}',
                      `val` = '{$val}'
                  WHERE `sensor_sn` = '{$sensorNo}'
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // TOC
    //------------------------------------------------------------------------------------------------------------
    /**
     * TOC로 보내기 위한 데이터 조회
     *
     * @param int $option
     * @param string $searchQuery
     *
     * @return string
     */
    public function getQuerySelectTocData(int $option, string $searchQuery) : string
    {
        $sensorTableNames = $this->sensorTableNames;
        $sensorTable = $sensorTableNames[$option];

        $query = "SELECT `toc`.`complex_code_pk`,
                         `toc`.`sensor_sn` AS `toc_sensor_sn`,
                         `toc`.`arch_type`,
                         `sensor`.`val_date`,
                         SUM(IFNULL(`sensor`.`val`, 0)) AS `val`
                  FROM `toc_info` AS `toc`
                     LEFT JOIN `bems_home` AS `home`
                        ON `toc`.`complex_code_pk` = `home`.`complex_code_pk`
                        AND `toc`.`type` = `home`.`home_type`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                        AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                        AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `sensor`.`fg_use` = 'y'
                  {$searchQuery}
                  GROUP BY `toc`.`sensor_sn`
                 ";

        return $query;
    }

    /**
     * TOC 건물 정보 조회
     *
     * @return string
     */
    public function getQuerySelectTocBuildingData() : string
    {
        $query = "SELECT `complex`.`complex_code_pk`,
                         `complex`.`name`,
                         `complex`.`post_code`,
                         `complex`.`lat`,
                         `complex`.`lon`
                  FROM `toc_info` AS `info`
                       INNER JOIN `bems_complex` AS `complex`
                          ON `info`.`complex_code_pk` = `complex`.`complex_code_pk`
                  WHERE `complex`.`fg_del` = 'n'
                 ";

        return $query;
    }

    /**
     * TOC 단지 건물 현황 조회
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getQuerySelectBuildingStatusData(string $complexCodePk) : string
    {
        $query = "SELECT COUNT(`T`.`home_dong_pk`) AS `cnt`
                  FROM (
                     SELECT `home_dong_pk`
                     FROM `bems_home`
                     WHERE `complex_code_pk` = '{$complexCodePk}'
                     AND `fg_del` = 'n'
                     GROUP BY `home_dong_pk`
                  ) AS `T`
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 공휴일, 국경일 데이터 조회
    //------------------------------------------------------------------------------------------------------------
    /**
     * 공휴일 데이터 생성
     *
     * @param string $holidayDate
     * @param string $holidayName
     * @param string $isHoliday
     *
     * @return string
     */
    public function getQueryInsertOrUpdateHoliday(string $holidayDate, string $holidayName, string $isHoliday) : string
    {
        $query = "INSERT INTO `bems_holiday`
                  SET `holiday_date` = '{$holidayDate}',
                      `holiday_name` = '{$holidayName}',
                      `is_holiday` = '{$isHoliday}'
                  ON DUPLICATE KEY UPDATE 
                     `holiday_date` = '{$holidayDate}'
                 ";

        return $query;
    }

    /**
     * 주어진 날짜 공휴일인지 조회
     *
     * @param string $date
     *
     * @return string
     */
    public function getQuerySelectIsHoliday(string $date) : string
    {
        $query = "SELECT `holiday_date`
                  FROM `bems_holiday`
                  WHERE `holiday_date` = '{$date}'
                  AND `is_holiday` = 'Y'  
                 ";

        return $query;
    }

    //------------------------------------------------------------------------------------------------------------
    // 데이터 연계 (SYSTEM INTEGRATION)
    //------------------------------------------------------------------------------------------------------------
    /**
     * 해당 단지가 연계업체로 등록 되어 있는지 확인
     *
     * @param string $complexCodePk
     * @param string $target
     *
     * @return string
     */
    public function getQuerySelectSystemIntegrationCheck(string $complexCodePk, string $target) : string
    {
        $query = "SELECT `info`.`target`,
                         `info`.`complex_code_pk`
                  FROM `bems_complex` AS `complex`
                     LEFT JOIN `bems_api_integration_info` AS `info`
                        ON `complex`.`complex_code_pk` = `info`.`complex_code_pk`
                  WHERE `complex`.`fg_del` = 'n'
                  AND `info`.`target` = '{$target}'
                  AND `info`.`complex_code_pk` = '{$complexCodePk}'
                 ";

        return $query;
    }

    /**
     * api 계정 정보 조회
     *
     * @param string $id
     *
     * @return string
     */
    public function getQuerySelectApiLogin(string $id) : string
    {
        $query = "SELECT `target`,
                         `id`,
                         `password`,
                         `client_key`,
                         `iv_key`,
                         `access_token`,
                         `refresh_token`
                  FROM `bems_api_account`
                  WHERE `account_code` = '{$id}'  
                  AND `fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * API 로그인 시 시각 변경
     *
     * @param string $target
     * @param string $id
     *
     * @return string
     */
    public function getQueryUpdateApiLoginDate(string $target, string $id) : string
    {
        $query = "UPDATE `bems_api_account`
                  SET `last_login_date` = NOW(),
                      `update_date` = NOW()
                  WHERE `target` = '{$target}'
                  AND `id` = '{$id}'
                 ";

        return $query;
    }

    /**
     * JWT 토큰 정보 업데이트
     *
     * @param string $target
     * @param string $id
     * @param string $accessToken
     * @param string $refreshToken
     *
     * @return string
     */
    public function getQueryUpdateJwtInfo(string $target, string $id, string $accessToken, string $refreshToken) : string
    {
        $query = "UPDATE `bems_api_account`
                  SET `access_token` = '{$accessToken}',
                      `refresh_token` = '{$refreshToken}',
                      `update_date` = NOW()
                  WHERE `target` = '{$target}'
                  AND `id` = '{$id}'
                 ";

        return $query;
    }

    /**
     * 토큰 업데이트
     *
     * @param string $id
     * @param string $tokenColumn
     * @param string $token
     *
     * @return string
     */
    public function getQueryUpdateJwt(string $id, string $tokenColumn, string $token) : string
    {
        $query = "UPDATE `bems_api_account`
                  SET `{$tokenColumn}` = '{$token}',
                      `update_date` = NOW()
                  WHERE `id` = '{$id}'
                 ";

        return $query;
    }

    /**
     * 토큰 정보 삭제
     *
     * @param $id
     *
     * @return string
     */
    public function getQueryDeleteJwt($id) : string
    {
        $query = "UPDATE `bems_api_account`
                  SET `access_token` = NULL,
                      `refresh_token` = NULL,
                      `update_date` = NOW()
                  WHERE `id` = '{$id}'
                 ";

        return $query;
    }

    /**
     * api 계정의 상태 조회
     *
     * @param string $id
     *
     * @return string
     */
    public function getQuerySelectApiAccountState(string $id) : string
    {
        $query = "SELECT `id`,
                         `target`,
                         IFNULL(`access_token`, '') AS `access_token`,
                         IFNULL(`refresh_token`, '') AS `refresh_token`
                  FROM `bems_api_account`
                  WHERE `id` = '{$id}'
                  AND `fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * 연계 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $sysIntegrationCompany
     * @param string $solarQuery
     *
     * @return string
     */
    public function getQuerySelectSysIntegrationSensorData(string $complexCodePk, int $option, string $sysIntegrationCompany, string $solarQuery = '') : string
    {
        $sensorTables = $this->sensorTableNames;
        $sensorTable = $sensorTables[$option];

        $allDataColumn = ", `sensor`.`all_data`";

        if (in_array($option, [1, 2, 13]) === true) {
            $allDataColumn = "";
        }

        $query = "SELECT `home`.`home_grp_pk`,
                         `sensor`.`sensor_sn`,
                         `sensor`.`complex_code_pk`,
                         IFNULL(`sensor`.`error_code`, 0) AS `error_code`,
                         IFNULL(`sensor`.`val_date`, '00000000000000') AS `val_date`,
                         IFNULL(`sensor`.`val`, 0) AS `val`
                         {$allDataColumn}
                  FROM `bems_complex` AS `complex`
                      LEFT JOIN `bems_api_integration_info` AS `info`
                         ON `complex`.`complex_code_pk` = `info`.`complex_code_pk`
                      LEFT JOIN `bems_home` AS `home`  
                         ON `info`.`complex_code_pk` = `home`.`complex_code_pk`
                      LEFT JOIN `{$sensorTable}` AS `sensor`
                         ON `home`.`complex_code_pk` = `sensor`.`complex_code_pk`
                         AND `home`.`home_dong_pk` = `sensor`.`home_dong_pk`
                         AND `home`.`home_ho_pk` = `sensor`.`home_ho_pk`
                  WHERE `info`.`target` = '{$sysIntegrationCompany}' 
                  AND `sensor`.`complex_code_pk` = '{$complexCodePk}'   
                  AND `complex`.`fg_del` = 'n'
                  AND `sensor`.`fg_use` = 'y'
                  {$solarQuery}
                 ";

        return $query;
    }

    /**
     * 연계 데이터 중 미세먼지 데이터 조회
     *
     * @param string $complexCodePk
     * @param string $sysIntegrationCompany
     *
     * @return string
     */
    public function getQuerySelectSysIntegrationFinedustData(string $complexCodePk, string $sysIntegrationCompany) : string
    {
        $finedustTables = Config::FINEDUST_TABLE_INFO;
        $sensorTable = $finedustTables['sensor_table'];

        $query = "SELECT `sensor`.`device_eui` AS `sensor_sn`,
                         `sensor`.`complex_code_pk` AS `complex_code_pk`,
                         IFNULL(`sensor`.`val_date`, '00000000000000') AS `val_date`,
                         IFNULL(`sensor`.`pm25`, 0) AS `pm25`,
                         IFNULL(`sensor`.`temperature`, 0) AS `temperature`,
                         IFNULL(`sensor`.`humidity`, 0) AS `humidity`,
                         IFNULL(`sensor`.`co2`, 0) AS `co2`
                  FROM `bems_complex` AS `complex`
                     LEFT JOIN `bems_api_integration_info` AS `info`
                        ON `complex`.`complex_code_pk` = `info`.`complex_code_pk`
                     LEFT JOIN `{$sensorTable}` AS `sensor`
                        ON `info`.`complex_code_pk` = `sensor`.`complex_code_pk`
                  WHERE `info`.`target` = '{$sysIntegrationCompany}'
                  AND `sensor`.`complex_code_pk` = '{$complexCodePk}'
                  AND `complex`.`fg_del` = 'n'
                  AND `sensor`.`fg_use` = 'y'
                 ";

        return $query;
    }

    /**
     * API 호출 시 로그 저장
     *
     * @param string $logDate
     * @param string $ip
     * @param string $type
     * @param string $url
     * @param string $parameter
     * @param string $requestInfo
     *
     * @return string
     */
    public function getQueryInsertApiCallLog(string $logDate, string $ip, string $type, string $url, string $parameter, string $requestInfo) : string
    {
        $query = "INSERT INTO `bems_api_call_log`
                  SET `log_date` = '{$logDate}',
                      `ip` = '{$ip}',
                      `type` = '{$type}',
                      `url` = '{$url}',
                      `parameter` = '{$parameter}',
                      `request_info` = '{$requestInfo}'
                 ";

        return $query;
    }

    /**
     * 연계 대상(만) 조회
     *
     * @return string
     */
    public function getQuerySelectIntegrationTarget() : string
    {
        $query = "SELECT `target`
                  FROM `bems_api_integration_info`
                  GROUP BY `target`
                 ";

        return $query;
    }

    /**
     * 연계 대상과 정보를 조회
     *
     * @return string
     */
    public function getQuerySelectIntegrationTargetAndInfo() : string
    {
        $query = "SELECT `target`,
                         `complex_code_pk`
                  FROM `bems_api_integration_info`  
                 ";

        return $query;
    }

    /**
     * 연계 대상에 속한  단지정보 조회
     *
     * @param string $target
     * @param string $complexQuery
     *
     * @return string
     */
    public function getQuerySelectIntegrationComplexInfo(string $target, string $complexQuery = '') : string
    {
        $query = "SELECT `complex`.`complex_code_pk`,
                         `complex`.`name`,
                         `complex`.`addr`
                  FROM `bems_complex` AS `complex`
                     LEFT JOIN `bems_api_integration_info` AS `info`
                        ON `complex`.`complex_code_pk` = `info`.`complex_code_pk`
                  WHERE `info`.`target` = '{$target}'
                  {$complexQuery}
                 ";

        return $query;
    }
}
