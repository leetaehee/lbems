<?php
namespace Http\Command;

use EMS_Module\Efficiency;

/**
 * Class EfficiencyTest 역률 클래스 테스트
 */
class EfficiencyTest extends Command
{
    /** @var Efficiency|null $efficiency 역률 객체 */
    private ?Efficiency $efficiency = null;

    /**
     * EfficiencyTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->efficiency = new Efficiency();
    }

    /**
     * EfficiencyTest Destructor.
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
        $efficiency = $this->efficiency;

        $complexCodePk = 2002;
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $this->data = [];
        return true;
    }
}