<?php
namespace Http\Command;

use Database\DbModule;

/**
 * Class UnitPriceKepcoInfo - kepco 기준으로 단지 요금 정보 조회 
 */
class UnitPriceKepcoInfo extends Command
{
    /** @var DbModule|null $priceDb 전기 요금 객체 */
    private ?DbModule $priceDb = null;

    /**
     * UnitPrice constructor.
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
     * UnitPrice destructor.
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
        $today = date('Y-m-d');

        $costNo = isset($params[0]['value']) === true ? $params[0]['value'] : 0;

        if (empty($costNo) === true || $costNo < 1) {
            $data['error'] = 'dataError';

            $this->data = $data;
            return true;
        }

		// 요금 조히
        $data['cost_data'] = $this->getCostDataById($costNo);

        // 기타 요금 조회
        $data['etc_price'] = $this->getEtcPriceData($today);

        $this->data = $data;
        return true;
    }

    /**
     * 해당되는 요금 정보만 조회
     *
     * @param int $costNo
     *
     * @return array
     */
    private function getCostDataById(int $costNo) : array
    {
        $fcData = [];

        $rCostQ = $this->electricPriceQuery->getQueryCostDataById($costNo);
        $this->priceDb->querys($rCostQ);

        $fcData = $this->priceDb->getData();
        $fcData = $fcData[0];

        if (count($fcData) < 1) {
            $fcData = [];
        }

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

        $fcData['etcPrice1'] = $result[0]['name'] === 'etcPrice1' ? $result[0]['rate'] : 0;
        $fcData['etcPrice2'] = $result[1]['name'] === 'etcPrice2' ? $result[1]['rate'] : 0;
        $fcData['etcPrice3'] = $result[2]['name'] === 'etcPrice3' ? $result[2]['rate'] : 0;
        $fcData['etcPrice4'] = $result[3]['name'] === 'etcPrice4' ? $result[3]['rate'] : 0;

        return $fcData;
    }
}