<?php
namespace Http\Command;

use EMS_Module\Usage;

/**
 * Class DailyElectricLibraryPrice 전력 요금 라이브러리 대한  금일 요금 테스트
 */
class DailyElectricLibraryPrice extends Command
{
    /** @var Usage|null $usage 사용량 조회  */
    private ?Usage $usage = null;
    /**
     * DailyElectricLibraryPrice constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * DailyElectricLibraryPrice destructor.
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
        $complexCodePk = '2002';
        $date = '20210416';
        $option = 0;
        $dateType = 2;

        $addOptions = [
            'floor' => 'all',
            'room' => 'all',
            'sensor' => '2002_ALL',
            'energy_name' => 'electric', // 전기만 진행하기 때문에..
        ];

        // 이제 존재하지 않음.. 테스트할 때 변경해서 할 것.
        //$d = $this->usage->getUsedTotalData($this, $complexCodePk, $option, $dateType, $date, $addOptions);

        $this->data = [];
        return true;
    }
}