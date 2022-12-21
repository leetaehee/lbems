<?php
namespace Http\Command;

use EMS_Module\Usage;

/**
 * Class AreaUsedTest 단위면적 사용량 테스트
 */
class AreaUsedTest extends Command
{
    /** @var Usage|null $usage 사용량 */
    private $usage = null;

    /**
     * AnalysisEnergy constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * AnalysisEnergy destructor.
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
        $data = [];

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $date = date('Ymd', strtotime('20210520'));
        $dateType = 2;
        $complexCodePk = '2002';
        $option = 0;
        $isUseNextDate = false;
        $isArea = true;

        $date = $this->usage->getDateByOption($date, $dateType);

        $addOptions = [
            'floor' => 'all',
            'room' => 'all',
            'sensor' => '2002_ALL',
            'is_use_next_date' => $isUseNextDate,
            'is_area' => $isArea,
            'energy_name' => 'electric',
        ];

        $d = $this->usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
        print_r($d);

        $this->data = $data;
        return true;
    }
}