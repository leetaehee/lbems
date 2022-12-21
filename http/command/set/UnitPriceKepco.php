<?php
namespace Http\Command;

use Database\DbModule;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class UnitPriceKepco  - Kepco 기준으로 에너지 단가 관리 조회
 */
class UnitPriceKepco extends Command
{
    /** @var DbModule|null $priceDb 전기 요금 객체 */
    private ?DbModule $priceDb = null;

    /**
     * UnitPriceKepco constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $devOptions = $this->devOptions;
        $priceDBInfo = [
            'DB_TYPE' => $devOptions['FEE_DB_TYPE'],
            'DB_HOST' => $devOptions['FEE_DB_HOST'],
            'DB_PORT' => $devOptions['FEE_DB_PORT'],
            'DB_ID' => $devOptions['FEE_DB_ID'],
            'DB_PASSWORD' => $devOptions['FEE_DB_PASSWORD'],
            'DB_SID' => $devOptions['FEE_DB_SID'],
        ];

        $this->priceDb = new DbModule($priceDBInfo);
    }

    /**
     * UnitPriceKepco destructor.
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
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params): ?bool
    {
        $data = [];
        $addQuery = [];

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $electricType = isset($params[0]['value']) === true ? $params[0]['value'] : 'N';
        $startPage = isset($params[1]['value']) === true ? $params[1]['value'] : 1;
        $viewPageCount = isset($params[2]['value']) === true ? $params[2]['value'] : 10;
        $isUseManage = isset($params[3]['value']) === true ? $params[3]['value'] : false;
        $today = date('Y-m-d');

        // 페이징 번호 셋팅
        $startPage = $startPage - 1;
        $startPage = $startPage < 1 ? 0 : ($startPage * $viewPageCount);

        // 설정 메뉴에서 조회 했을 경우 해당 업체에 대한 정보만 조회되도록 한다.
        if ($isUseManage === false) {
            $addQuery = $this->getPriceQueryData($complexCodePk);
        }

        // 요금 데이터 조회
        $data['electric_prices'] = $this->getElectricPriceData($electricType, $today, $startPage, $viewPageCount, $addQuery);

        $this->data = $data;
        return true;
    }

    /**
     * 에너지 단가관리 리스트 조회
     *
     * @param string $electricType
     * @param string $date
     * @param int $startPage
     * @param int $endPage
     * @param array $addQuery
     *
     * @return array
     */
    private function getElectricPriceData(string $electricType, string $date, int $startPage, int $endPage, array $addQuery) : array
    {
        $fcData = [];
        $fcList = [];
        $fcCountData = [];
        $fcEtcPriceData = [];

        // 요금 데이터 조회
        $rPriceListQ = $this->electricPriceQuery->getQueryCostList($electricType, $date, $startPage, $endPage, $addQuery);
        $this->priceDb->querys($rPriceListQ);
        $fcList = $this->priceDb->getData();

        // 총 데이터량 조회
        $rPriceListTotalQ = $this->electricPriceQuery->getQueryCostListCount($electricType, $date, $addQuery);
        $this->priceDb->querys($rPriceListTotalQ);
        $fcCountData = $this->priceDb->getData();

        // 기타 요금 조회
        $fcEtcPriceData = $this->getEtcPriceData($date);

        $fcData = [
            'list' => $fcList,
            'price_etc' => $fcEtcPriceData,
            'count' => $fcCountData[0]['cnt'],
        ];

        return $fcData;
    }

    /**
     * 요금 계산을 위한 조건 배열로 반환
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getPriceQueryData(string $complexCodePk) : array
    {
        $fcData = [];

        $voltTypeKeys = Config::FEE_LIBRARY_VOLT_TYPE_KEYS;

        $rComplexQ = $this->emsQuery->getQuerySelectComplexPriceInfo($complexCodePk);
        $prices = $this->query($rComplexQ);

        $typeGubun2 = $prices[0]['typeGubun2'];

        $typeGubunQ = Utility::getInstance()->makeWhereClause('price', 'powerDiv', $prices[0]['typeGubun']);
        $typeGubun2Q = Utility::getInstance()->makeWhereClause('price', 'voltType', $voltTypeKeys[$typeGubun2]);

        $typeSelectQ = '';
        if ($typeGubun2 !== 'low') {
            $typeSelectQ = Utility::getInstance()->makeWhereClause('price', 'selectType', $prices[0]['typeSelect']);
        }

        $fcData = [
            'type_gubun_q' => $typeGubunQ,
            'type_gubun2_q' => $typeGubun2Q,
            'type_select_q' => $typeSelectQ,
        ];

        return $fcData;
    }

    /**
     * 기타 요금 조회
     *
     * @param string $date
     *
     * @return int[]
     */
    public function getEtcPriceData(string $date) : array
    {
        $fcData = [
            'etcPrice1' => 0,
            'etcPrice2' => 0,
            'etcPrice3' => 0,
            'etcPrice4' => 0,
        ];

        // 기타요금 조회
        $rPriceEtcQ = $this->electricPriceQuery->getQuerySelectElectricEtcPrice($date);
        $this->priceDb->querys($rPriceEtcQ);

        $result = $this->priceDb->getData();

        if (count($result) === 0) {
            return $fcData;
        }

        $fcData['etcPrice1'] =  $result[0]['name'] === 'etcPrice1' ? $result[0]['rate'] : 0;
        $fcData['etcPrice2'] = $result[1]['name'] === 'etcPrice2' ? $result[1]['rate'] : 0;
        $fcData['etcPrice3'] = $result[2]['name'] === 'etcPrice3' ? $result[2]['rate'] : 0;
        $fcData['etcPrice4'] = $result[3]['name'] === 'etcPrice4' ? $result[3]['rate'] : 0;

        return $fcData;
    }
}