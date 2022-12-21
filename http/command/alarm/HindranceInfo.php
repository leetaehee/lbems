<?php
namespace Http\Command;

use EMS_Module\Config;

/**
 * Class HindranceInfo
 */
class HindranceInfo extends Command
{
    /** @var array $floors 층 정보 */
    private array $floors = Config::FLOOR_INFO;

    /**
     * HindranceInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * HindranceInfo destructor.
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
		$complexData = [];
        $energyData = [];

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $floors = $this->floors;
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 에너지원 조회
        if (is_null($this->sensorObj) === false) {
            $energyData = $this->sensorObj->getHindranceAlarmSensor();
        }

        // 층 정보 조회
        if (in_array($complexCodePk, Config::FACTORY_USE_GROUP) === false) {
            $rComplexQ = $this->emsQuery->getQueryComplexInfoAll($complexCodePk);
            $rComplexResult = $this->query($rComplexQ);
        }

        for ($i = 0; $i < count($rComplexResult); $i++) {
            $homeGrpPk = $rComplexResult[$i]['home_grp_pk'];
            $complexData[$homeGrpPk] = $floors[$homeGrpPk];
        }

        // 뷰에 데이터 전달
        $data = [
            'complex_data' => $complexData,
            'energy_data' => $energyData
        ];

        $this->data = $data;
        return true;
    }
}
