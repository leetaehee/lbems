<?php
namespace Http\Command;

/**
 * Class ElectricPriceTest 전력 요금 테스트
 */
class ElectricPriceTest extends Command
{
    /**
     * ElectricPriceTest constructor.
     */
    public function  __construct()
    {
        parent::__construct();
    }

    /**
     * ElectricPriceTest destructor.
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
        $this->data = [];
        return true;
    }
}