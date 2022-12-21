<?php
namespace Http\Command;

use EMS_Module\Usage;

/**
 * Class SetStandardCode
 */
class SetStandardCode extends Command
{
    /** @var Usage|null $usage 사용량 | 요금  */
    private ?Usage $usage = null;

    /**
     * SetStandardCode constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * SetStandardCode destructor.
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
        $data = [];

        $complexCodePk = $_SESSION['ss_complex_pk'];

        // 센서정보를 가져오기 위한 단지 설정
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 설정 기준값에 따른 코드 정보 조회
        $data = $this->getStandardCode();

        if (count($data) === 0) {
            $data['error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $this->data = $data;
        return true;
    }

    /**
     * 에너지원 분류하기
     *
     * @return array
     */
    private function getStandardCode() : array
    {
        $fcData = [];

        $energyData = $this->sensorObj->getEnergyPartData();

        foreach ($energyData as $groups => $kinds) {
            foreach ($kinds as $energyType => $values) {
                $fcData[$energyType] = $values['option'];
            }
        }

        $fcData['solar'] = 11;

        return $fcData;
    }
}