<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ArrangeEfficiencyDay 효율 일통계 생성
 */
class ArrangeEfficiencyDay extends Command
{
    /**
     * ArrangeEfficiencyDay Constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeEfficiencyDay Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 데이터 생성
     *
     * @param array $d
     * @param int $index
     * @param string $currentSensor
     * @param int $option
     *
     * @return array
     *
     * @throws \Exception
     */
    private function makeUpdateInfo(array $d, int &$index, string &$currentSensor, int $option) : array
    {
        $todayVal = 0;

        $info = [
            'sensor_sn' => $currentSensor,
            'efficiency' => 0,
        ];

        $len = count($d);

        for (; $index < $len; $index++) {
            $sensor = $d[$index]['sensor_sn'];
            $complexCodePk = $d[$index]['complex_code_pk'];

            if ($sensor != $currentSensor) {
                $currentSensor = $sensor;
                return $info;
            }

            $date = (int)substr($d[$index]['val_date'], 8, 2);
            $date = 'efficiency_'.$date;
            $val = $d[$index]['val'];

            if ($index % 24 === 0) {
                $previousDay = substr($d[$index]['val_date'], 0, 8);

                // 센서 검색 조건
                $sensorQuery = Utility::getInstance()->makeWhereClause('sensor', 'sensor_sn', $sensor);

                // 금일 사용량에 대해서 조회
                $rDailySumQ = $this->emsQuery->getQueryEfficiencyAvgSumData($complexCodePk, $option, $previousDay, $sensorQuery);
                $dailySums = $this->query($rDailySumQ);
                $todayVal = $dailySums[0]['val'];
            }

            $info[$date] = $val;
            $info['efficiency'] = $todayVal;
        }


        return $info;
    }

    /**
     * meter 데이터 조회
     *
     * @param int $option
     * @param int $day
     * @param array $d
     *
     * @throws \Exception
     */
    private function SetDailyData(int $option, int $day, array $d) : void
    {
        $maxCount = 2147483647;
        $index = 0;
        $tempIndex = 0;
        $len = count($d);
        $currentSensor = '';

        while (true) {
            $tempIndex++;

            if ($index >= $len || $tempIndex >= $maxCount) {
                break;
            }

            $info = $this->makeUpdateInfo($d, $index, $currentSensor, $option);

            if (count($info) == 2) {
                continue;
            }

            $query = $this->emsQuery->getQueryUpdateOrInsertEfficiencyDay($option, $day, $info);
            $this->squery($query);
        }
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
        $data = [
            'result' => false,
        ];

        $today = date('Ymd');
        //$today = date('Ymd', strtotime('20210823'));

        $yesterday = Utility::getInstance()->addDay($today, -1);

        $efficiencyTables = Config::EFFICIENCY_TABLES;

        for ($i = 0; $i < count($efficiencyTables); $i++) {
            $option = $i;

            if (empty($efficiencyTables[$option]) === true) {
                continue; // 효율정보가 있는 것만 조회..
            }

            $query = $this->emsQuery->getQueryEfficiencyAvgDayData($option, $yesterday);
            $d = $this->query($query);

            $this->SetDailyData($option, $yesterday, $d);
        }

        $this->close();

        $data['result'] = true;
        $this->data = $data;

        return true;
    }
}