<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class Solar 태양광 조회
 */
class Solar extends Command
{
    /** @var array $periodKeys 주기에 대한 키 */
    private array $periodKeys = Config::PERIODS;

    /** @var Usage|null $usage 사용량 */
    private ?Usage $usage = null;

    /** @var int $option 에너지타입 */
    private int $option = 11;

    /**
     * Solar constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * Solar destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 금일/금월/금년 태양광 데이터 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $solarSensors
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getSolarUsedData(string $complexCodePk, int $option, array $solarSensors) : array
    {
        $fcData = [];

        $usage = $this->usage;
        $periods = $this->periodKeys;

        // 태양광 발전량
        $solarInOptions = [
            'sensor' => $solarSensors['in'],
            'solar_type' => 'I'
        ];

        // 태양광 소비량
        $solarOutOptions = [
            'sensor' => $solarSensors['out'],
            'solar_type' => 'O'
        ];

        for ($i = 0; $i < 3; $i++) {
            $date = $this->baseDateInfo['date'];
            $dateType = $i;
            $date = $usage->getDateByOption($date, $dateType);

            $periodKey = $periods[$i];

            $solarIns = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $solarInOptions);
            $solarOuts = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $date, $solarOutOptions);

            $fcData[$periodKey] = [
                'in' => $solarIns['current']['data'],
                'out' => $solarOuts['current']['data']
            ];
        }

        return $fcData;
    }

    /**
     * 기간별 태양광 소비량, 생산량 데이터 검색
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $start
     * @param string $end
     * @param array $solarSensors
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getSolarUsedByUserData(string $complexCodePk, int $option, int $dateType, string $start, string $end, array $solarSensors) : array
    {
        $fcData = [];

        $usage = $this->usage;

        $start = str_replace('-', '', $start);
        $end = str_replace('-', '', $end);

        // 태양광 발전량
        $solarInOptions = [
            'sensor' => $solarSensors['in'],
            'solar_type' => 'I',
            'energy_name' => 'solar_in',
        ];
        // 태양광 소비량
        $solarOutOptions = [
            'sensor' => $solarSensors['out'],
            'solar_type' => 'O',
            'energy_name' => 'solar_out',
        ];

        if ($dateType === 0) {
            $solarInOptions['is_use_next_date'] = false;
            $solarOutOptions['is_use_next_date'] = false;

            $solarIns = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $start, $solarInOptions);
            $solarOuts = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $start, $solarOutOptions);
        } else {
            $solarIns = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $start, $end, $solarInOptions);
            $solarOuts = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $start, $end, $solarOutOptions);
        }

        $fcData = [
            'in' => $solarIns['data'],
            'out' => $solarOuts['data']
        ];

        return $fcData;
    }

    /**
     * 시간 발전효율 계산을 위한 현재 시각 값 조회
     *
     * @param string $complexCodePk
     *
     * @return float
     *
     * @throws \Exception
     */
    private function getSolarEfficiency(string $complexCodePk) : float
    {
        $solarEfficiency = 0;
        $today = $this->baseDate;
        $option = 11;

        $rSolarInfoQ = $this->emsQuery->getQuerySolarCurrentTime($complexCodePk, $option);
        $solarData = $this->query($rSolarInfoQ);

        $allData = (string)$solarData[0]['all_data'];
        $solarEfficiency = $this->getFindEfficiencyValue($allData);
        if ($solarEfficiency === -999) {
            return $solarEfficiency;
        }

        return (float)$solarEfficiency;
    }

    /**
     * 태양광 효율 추출.
     *
     * @param string $data
     *
     * @return int
     */
    private function getFindEfficiencyValue(string $data) : int
    {
        $allData = json_decode($data, true);
        $efficiency = $allData['efficiency'];

        if (empty($efficiency) === false) {
            return round($efficiency * 100);
        }

        return -999;
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

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $isLoading = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $start = isset($params[1]['value']) == true ? Utility::getInstance()->removeXSS($params[1]['value']) : '00000000';
        $end = isset($params[2]['value']) == true ? Utility::getInstance()->removeXSS($params[2]['value']) : '00000000';
        $dateType = isset($params[3]['value']) == true ? Utility::getInstance()->removeXSS($params[3]['value']) : 0;

        $option = $this->option;
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $solarSensors = $this->sensorObj->getSolarSensor();

        if ($isLoading === 1) {
            // 처음 로딩시에는 금일,금월,금년 데이터 보여준다.
            $solarUseds = $this->getSolarUsedData($complexCodePk, $option, $solarSensors);
            $efficiency = $this->getSolarEfficiency($complexCodePk);

            $data = [
                'solar_used' => $solarUseds,
                'solar_efficiency' => $efficiency,
            ];
        }

        // 검색 버튼을 눌렀을 때는 해당 기간에 따라 데이터 보여준다.
        $solarPeriodUseds = $this->getSolarUsedByUserData($complexCodePk, $option, $dateType, $start, $end, $solarSensors);

        $data['solar_period_used'] = $solarPeriodUseds;

        // 뷰에 보여줄 데이터 전달
        $this->data = $data;

        return true;
    }
}