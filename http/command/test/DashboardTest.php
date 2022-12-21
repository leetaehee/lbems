<?php
namespace Http\Command;

use EMS_Module\Usage;

/**
 * Class DashboardTest
 */
class DashboardTest extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * Dashboard constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage;
    }

    /**
     * Dashboard Destructor.
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
     * @throws /Exception
     */
    public function execute(array $params) :? bool
    {
        $usage = $this->usage;

        $complexCodePk = $_SESSION['ss_complex_pk'];

        // 함수 파라미터 수정해서 할 것
        //$d = $usage->getEnergyDataByHome($this, $complexCodePk, 0, 0, '2021', 'all', 'all', '');

        $this->data = [];
        return true;
    }
}