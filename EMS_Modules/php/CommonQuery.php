<?php
namespace EMS_Module;

/**
 * Class CommonQuery 공통으로 사용하는 쿼리
 */
class CommonQuery
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

    /** @var array|string[] 부하종류 컬멈 */
    private array $statusColumns = Config::STATUS_COLUMNS;

    //------------------------------------------------------------------------------------------------------------
    // 경부하, 중부하, 최대부하
    //------------------------------------------------------------------------------------------------------------
    /**
     * 경부하, 중부하, 최대부하 컬럼 추출
     *
     * @param int $option
     * @param string $alias
     *
     * @return string[]
     */
    public function getStatusColumn(int $option, string $alias) : array
    {
        $statusColumns = $this->statusColumns;
        $statusEnergyTypes = Config::STATUS_ENERGY_TYPES;

        $result = [
            'in' => '',
            'group' => '',
        ];

        if (in_array($option, $statusEnergyTypes) === false) {
            return $result;
        }

        if (empty($alias) === true) {
            return $result;
        }

        foreach ($statusColumns as $i => $column) {
            $result['in'] .= " ,IFNULL(SUM(`{$alias}`.`{$column}`), 0) AS `{$column}`";
            $result['group'] .= " ,IFNULL(SUM(`T`.`{$column}`), 0) AS `{$column}`";
        }

        return $result;
    }

    /**
     * 경부하, 중부하, 최대부하  일통계 참조를 위한 join 쿼리
     *
     * @param int $option
     * @param string $startDate
     * @param string $endDate
     *
     * @return string
     */
    public function getStatusTableJoin(int $option, string $startDate, string $endDate) : string
    {
        $statusEnergyTypes = Config::STATUS_ENERGY_TYPES;

        $dayTableNames = $this->dayTableNames;
        $dayTableName = $dayTableNames[$option];

        if (in_array($option, $statusEnergyTypes) === false) {
            return '';
        }

        $tableJoin = " LEFT JOIN `{$dayTableName}` AS `daily_status`
                             ON `sensor`.`sensor_sn` = `daily_status`.`sensor_sn`
                             AND `daily_status`.`val_date` >= '{$startDate}'
                             AND `daily_status`.`val_date` <= '{$endDate}'
                        ";

        return $tableJoin;
    }
}