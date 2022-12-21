<?php
namespace EMS_Module;

/**
 * Class MobileQuery
 */
class MobileQuery
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
     * MobileQuery constructor.
     */
    public function __construct()
    {
    }

    /**
     * MobileQuery destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 건물 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return string $query
     */
    public function getQueryBuildingInfo(string $complexCodePk) : string
    {
        $query = "SELECT `name`,
                         `addr`
                  FROM `bems_complex`
                  WHERE `complex_code_pk` = '{$complexCodePk}'";

        return $query;
    }
}