<?php
namespace EMS_Module;

/**
 * Class ElectricPriceQuery 전력요금 라이브러리 관련 쿼리
 */
class ElectricPriceQuery
{
    /** @var string $priceDatabase 요금 데이터베이스 */
    private string $priceDatabase = 'kepco_db';

    /** @var string $priceTable 요금 테이블  */
    private string $priceTable = 'kepco_price';

    /** @var string $priceEtcTable 기타 요금 테이블  */
    private string $priceEtcTable = 'kepco_etc_price';

    /**
     * ElectricPriceQuery Constructor.
     */
    public function __construct()
    {
        $dbConfigs = parse_ini_file(dbConfigFile);

        $this->priceDatabase = $dbConfigs['FEE_DB_SID'];
    }

    /**
     * ElectricPriceQuery Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 용도에 따라  지정된 페이지 정보만큼 요금 정보 조회
     *
     * @param string $electricType
     * @param string $date
     * @param int $startPage
     * @param int $endPage
     * @param array $addQuery
     *
     * @return string
     */
    public function getQueryCostList(string $electricType, string $date, int $startPage, int $endPage, array $addQuery) : string
    {
        $priceDatabase = $this->priceDatabase;
        $priceTable = $this->priceTable;

        $query = "SELECT `price`.`idx`,
                         `price`.`electricType`,
                         `price`.`powerDiv` AS `typeGubun`,
                         `price`.`voltType` AS `typeGubun2`,
                         `price`.`selectType` AS `typeSelect`,
                         `price`.`season` AS `summerGubun`,
                         `price`.`section`,
                         `price`.`level`,
                         `price`.`defaultPrice`,
                         `price`.`unitCost` AS `cost`
                  FROM `{$priceDatabase}`.`{$priceTable}` AS `price`
                  WHERE `price`.`electricType` = '{$electricType}'
                  AND `price`.`endDate` <= '{$date}'
                  {$addQuery['type_gubun_q']}
                  {$addQuery['type_gubun2_q']}
                  {$addQuery['type_select_q']}
                  ORDER BY `price`.`powerDiv`
                  LIMIT {$startPage}, {$endPage}
                 ";

        return $query;
    }

    /**
     * 용도에 따른 전체 데이터 카운트 조회
     *
     * @param string $electricType
     * @param string $date
     * @param array $addQuery
     *
     * @return string
     */
    public function getQueryCostListCount(string $electricType, string $date, array $addQuery) : string
    {
        $priceDatabase = $this->priceDatabase;
        $priceTable = $this->priceTable;

        $query = "SELECT COUNT(`idx`) AS `cnt`
                  FROM `{$priceDatabase}`.`{$priceTable}`  AS `price`
                  WHERE `electricType` = '{$electricType}'
                  AND `price`.`endDate` <= '{$date}'
                  {$addQuery['type_gubun_q']}
                  {$addQuery['type_gubun2_q']}
                  {$addQuery['type_select_q']}
                 ";

        return $query;
    }

    /**
     * 기타 요금 조회
     *
     * @param string $date
     *
     * @return string
     */
    public function getQuerySelectElectricEtcPrice(string $date) : string
    {
        $priceDatabase = $this->priceDatabase;
        $priceEtcTable = $this->priceEtcTable;

        $query = "SELECT CASE WHEN `name` = 'environment_fee' THEN 1
                              WHEN `name` = 'fuel_cost_adj' THEN 2 
                              WHEN `name` = 'vat' THEN 3 
                              WHEN `name` = 'elec_fund' THEN 4  
                              ELSE 5 END `order`,
                         CASE WHEN `name` = 'environment_fee' THEN 'etcPrice1'
                              WHEN `name` = 'fuel_cost_adj' THEN 'etcPrice2' 
                              WHEN `name` = 'vat' THEN 'etcPrice3' 
                              WHEN `name` = 'elec_fund' THEN 'etcPrice4'  
                              ELSE '-' END `name`,
                         `rate`
                  FROM `{$priceDatabase}`.`{$priceEtcTable}`
                  WHERE `endDate` >= '{$date}'
                  AND `startDate` <= '{$date}'
                  ORDER BY `order` ASC    
                 ";

        return $query;
    }

    /**
     * 해당되는 요금 정보만 조회
     *
     * @param int $idx
     *
     * @return string
     */
    public function getQueryCostDataById(int $idx) : string
    {
        $priceDatabase = $this->priceDatabase;
        $priceTable = $this->priceTable;

        $query = "SELECT `price`.`idx`,
                         `price`.`electricType`,
                         `price`.`powerDiv` AS `typeGubun`,
                         `price`.`voltType` AS `typeGubun2`,
                         `price`.`selectType` AS `typeSelect`,
                         `price`.`season` AS `summerGubun`,
                         `price`.`section`,
                         `price`.`level`,
                         `price`.`defaultPrice`,
                         `price`.`unitCost` AS `cost`,
                         `price`.`startDate`,
                         `price`.`endDate`
                  FROM `{$priceDatabase}`.`{$priceTable}`  AS `price`
                  WHERE `price`.`idx` = '{$idx}'
                 ";
        return $query;
    }

    /**
     * 요금정보 변경
     *
     * @param int $costNo
     * @param array $formData
     *
     * @return string
     */
    public function getQueryUpdatePriceInfo(int $costNo, array $formData) : string
    {
        $priceDatabase = $this->priceDatabase;
        $priceTable = $this->priceTable;

        $query = "UPDATE `{$priceDatabase}`.`{$priceTable}`
                   SET `defaultPrice` = '{$formData['popup_defaultPrice']}',
                       `unitCost` = '{$formData['popup_cost']}'
                   WHERE `idx` = '{$costNo}'
                 ";

        return $query;
    }
}