<?php
namespace Http\Command;

/**
 * Class UnitPrice
 */
class UnitPrice extends Command
{
    /**
     * UnitPrice constructor.
     */
    public function __construct()
    {
        parent::__construct();
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
    public function execute(array $params) :? bool
    {
        if (isset($params[0]['roren_type'])) {
            $complexCodePk = $params[0]['roren_type'];
        } else {
            $complexCodePk = $_SESSION['ss_complex_pk'];
        }

        $startPage = $params[0]['start_page']-1;
        $endPage = $params[0]['view_page_count'];
        if ($startPage < 1) {
            $startPage = 0;
        } else {
            $startPage = $startPage * $endPage;
        }

        // 전기 외 (가스,수도,난방,급탕 등)
        $rDiffUnitCostQ = $this->emsQuery->getUnitCosts($complexCodePk, $startPage, $endPage);
        $etcs = $this->query($rDiffUnitCostQ);

        // 전기 외 데이터 카운트
        $rUnitCntQ = $this->emsQuery->getUnitCostDataCount($complexCodePk, $startPage, $endPage);
        $rUnitCntRslt = $this->query($rUnitCntQ);

        $data['unitCosts'] = [
            'electric' => [],
            'etc' => $etcs,
            'electricCount' => 0,
            'etcCount' => $rUnitCntRslt[0]['cnt']
        ];

        $this->data = $data;

        return true;
    }
}