<?php
namespace Http\Command;

use EMS_Module\Indication;

/**
 * Class AnalysisZeroTest 제로에너지 개별 테스트
 */
class AnalysisZeroTest extends Command
{
    /**
     * AnalysisZeroTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AnalysisZeroTest destructor.
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
     */
    public function execute(array $params) :? bool
    {
        $complexCodePk = $_SESSION['ss_complex_pk'];

        $indication = new Indication($this);

        $today = date('Ymd', strtotime('2020-10-28'));

        $yesterDay = date("Ymd", strtotime($today . "-1 day"));
        $preMonth = date("Ym", strtotime($today . "-1 month"));

        //$year = $indication->getIndependencePercent($complexCodePk, date('Y'), 'year');
        //$month = $indication->getIndependencePercent($complexCodePk, $preMonth);
        //$day = $indication->getIndependencePercent($complexCodePk, $yesterDay, 'daily');

        // 데이터 출력
        $this->data = [];

        return true;
    }
}