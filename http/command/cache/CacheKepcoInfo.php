<?php
namespace Http\Command;

use Module\FileCache;

/**
 * Class CacheKepcoInfo bems_complex 테이블에서  kepco 요금 정보 캐시화
 */
class CacheKepcoInfo extends Command
{
    /**
     * CacheKepcoInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * CacheKepcoInfo destructor.
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
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        // 캐시 데이터 조회
        $data = $this->getCacheData();

        // 캐시추가
        $cache = new FileCache('kepco_info', 'complex');
        $cache->cacheFileWrite($data);

        $this->data = [];
        return true;
    }

    /**
     * 캐시하고자 하는 데이터 조회
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getCacheData(): array
    {
        $fcData = [];

        // 건물 조회
        $rComplexQ = $this->emsQuery->getQuerySelectComplex();
        $complexData = $this->query($rComplexQ);

        for ($fcIndex = 0; $fcIndex < count($complexData); $fcIndex++) {
            $complexCodePk = $complexData[$fcIndex]['complex_code_pk'];

            // 요금 정보 조회
            $rKepcoPriceinfoQ = $this->emsQuery->getQuerySelectComplexPriceInfo($complexCodePk);
            $info = $this->query($rKepcoPriceinfoQ);

            $fcData[$complexCodePk] = $info[0];
        }

        return $fcData;
    }
}