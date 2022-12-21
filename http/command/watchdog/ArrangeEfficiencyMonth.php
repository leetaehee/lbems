<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ArrangeEfficiencyMonth 효율 월통계 생성
 */
class ArrangeEfficiencyMonth extends Command
{
    /**
     * ArrangeEfficiencyMonth Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrangeEfficiencyMonth Destructor.
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
        $data = [
            'result' => false,
        ];

        $today = date("Ymd");
        $previousDay = date('Ymd', strtotime($today . '-1 day'));

        $rComplexQ = $this->emsQuery->getQuerySelectComplex();
        $complexResult = $this->query($rComplexQ);

        $efficiencyTables = Config::EFFICIENCY_TABLES;

        $complexCount = count($complexResult);
        for ($z0 = 0; $z0 < $complexCount; $z0++) {
            // 고객시설 정보 받아오기
            $complexCodePk = $complexResult[$z0]['complex_code_pk'];
            if ($complexCodePk === '2001') {
                continue; // 무등산은 효율 정보 존재하지 않으므로 안받음..
            }

            for ($j = 0; $j < count($efficiencyTables); $j++) {
                $option = $j;

                if (empty($efficiencyTables[$option]) === true) {
                    continue; // 효율정보가 있는것만 조회..
                }

                $query = $this->emsQuery->getDueday($option, $complexCodePk);
                $d = $this->query($query);
                $dueDay = (int)$d[0]['closing_day'];
                $tempDueDay = $dueDay;

                if ($dueDay === 99) {
                    // 말일인 경우 예외처리
                    $dueDay = date('t', strtotime($previousDay));
                }

                $temp = Utility::getInstance()->getDateFromDueday($dueDay, $previousDay);

                $checkDueDate = date('Ymd', strtotime($temp['end'] . '+1 day'));
                if ($checkDueDate !== $today) {
                    continue;
                }

                $start = $temp['start'];
                $end = $temp['end'];

                $query = $this->emsQuery->getQueryEfficiencyAvgMonthData($complexCodePk, $option, $start, $end);
                $d = $this->query($query);

                for ($k = 0; $k < count($d); $k++) {
                    $sensor_sn = $d[$k]['sensor_sn'];
                    $val = $d[$k]['val'];
                    $valSt = $d[$k]['val_st'];
                    $valEd = $d[$k]['val_ed'];
                    $query = $this->emsQuery->getQueryUpdateOrInsertEfficiencyMonth($option, $start, $end, $sensor_sn, $val, $valSt, $valEd, $tempDueDay);
                    $this->squery($query);
                }
            }
        }

        $data['result'] = true;

        $this->data = $data;
        return true;
    }
}