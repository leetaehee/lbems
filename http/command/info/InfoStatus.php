<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class InfoStatus 정보감시 부하별 사용량
 */
class InfoStatus extends Command
{
    /** @var  Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /** @var array $separatedData 독립적으로 조회되는 에너지원 */
    private array $separatedData = [];

    /**
     * InfoStatus constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * InfoStatus destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수.
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
        $this->sensorObj = $this->getSensorManager($complexCodePk);
        $this->separatedData = $this->sensorObj->getSpecialSensorKeyName();

        $option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $date = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '0000';
        $dateType = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 0;
        $floor = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : 'all';
        $room = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : 'all';
        $energyName = isset($params[5]['value']) === true ? $params[5]['value'] : 'electric';
        $dong = isset($params[6]['value']) === true ? $params[6]['value'] : 'all';

        $option = (int)$option;
        $dateType = (int)$dateType;

        if ($dateType === 2 &&
            $date === date('Y-m-d')) {
            $this->data['error'] =  'today';
            return true;
        }

        // 주요지표(경, 중, 최대) 조회
        $indicatorData = $this->getIndicatorData($complexCodePk, $option, $dateType, $date, $dong, $floor, $room, $energyName);

        // 부하현황 조회
        $statusData = $this->getStatusData($complexCodePk, $option, $dateType, $date, $dong, $floor, $room, $energyName);

        // 데이터반환
        $data = [
            'indicator_data' => $indicatorData,
            'status_data' => $statusData,
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 주요지표 부하별 사용량 조회 (현재, 이전)
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $energyName
     *
     * @return array[]
     *
     * @throws \Exception
     */
    private function getIndicatorData(string $complexCodePk, int $option, int $dateType, string $date, string $dong, string $floor, string $room, string $energyName) : array
    {
        $fcUsage = $this->usage;
        $separatedData = $this->separatedData;

        $fcData = [
            'current' => [],
            'last' => [],
        ];

        $fcAddOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $energyName,
            'is_status' => true,
            'separated_date' => Utility::getInstance()->arrayKeyCheckResult($energyName, $separatedData),
        ];

        $periods = $this->getPeriod($date, $dateType);

        // 현재
        if ($dateType === 1) {
            $fcAddOptions['start_date'] = $periods['start_date'];
            $fcAddOptions['end_date'] = $periods['end_date'];
        }

        $tempDate = date('Ymd', strtotime($date));
        $tempDate = $fcUsage->getDateByOption($tempDate, $dateType);

        $nowData = $fcUsage->getUsageSumData($this, $complexCodePk, $option, $dateType, $tempDate, $fcAddOptions);

        // 과거
        if ($dateType === 1) {
            $fcAddOptions['start_date'] = $periods['prev_start_date'];
            $fcAddOptions['end_date'] = $periods['prev_end_date'];
        }

        $tempDate = date('Ymd', strtotime($periods['prev_start_date']));
        $tempDate = $fcUsage->getDateByOption($tempDate, $dateType);

        $lastData = $fcUsage->getUsageSumData($this, $complexCodePk, $option, $dateType, $tempDate, $fcAddOptions);

        $fcData['current'] = $nowData['current']['data'];
        $fcData['last'] = $lastData['current']['data'];

        return $fcData;
    }

    /**
     * 부하현황에 보여줄 데이터 (시간, 일, 월)
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $energyName
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getStatusData(string $complexCodePk, int $option, int $dateType, string $date, string $dong, string $floor, string $room, string $energyName) : array
    {
        $fcData = [];

        $fcAddOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $energyName,
        ];

        $fcUsage = $this->usage;

        if ($dateType === 1) {
            $periods = $this->getPeriod($date, $dateType);
            $temps = $fcUsage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $periods['start_date'], $periods['this_end_date'], $fcAddOptions);

            $fcData = $temps['data'];

            return $fcData;
        }

        $date = date('Ymd', strtotime($date));
        $date = $fcUsage->getDateByOption($date, $dateType);
        $temps = $fcUsage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $fcAddOptions);

        $fcData = $temps['data'];
        return $fcData;
    }

    /**
     * 주기에 따른 검색 날짜 조회
     *
     * @param string $date
     * @param int $dateType
     *
     * @return array
     */
    private function getPeriod(string $date, int $dateType) : array
    {
        $fcData = [];

        switch ($dateType)
        {
            case 0:
                // 금년
                $endMonth = date('Y-m', strtotime($date));
                $temp = explode('-', $endMonth);

                // 시작(월)~종료(월)
                $startDate = $temp[0] . '01';
                $endDate = $temp[0] . '' . '12';

                // 주요지표에서 전년 구하기 위한 날짜
                $prevStartDate = date('Ymd', strtotime("-1 year", strtotime($startDate . '01')));
                $prevEndDate = date('Ymd', strtotime("-1 year", strtotime($endDate . '01')));

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'prev_start_date' => $prevStartDate,
                    'prev_end_date' => $prevEndDate
                ];

                break;
            case 1:
            case 6:
                // 금월
                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $temp[2];
                $endThisDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                // 주요지표에서 전월 구하기 위한 날짜
                $prevStartDate = date("Ymd", strtotime('-1 month', strtotime($startDate)));
                $prevEndDate = date("Ymd", strtotime('-1 month', strtotime($date)));
                $endThisDate = date("Ymd",strtotime($endThisDate));

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'this_end_date' => $endThisDate,
                    'prev_start_date' => $prevStartDate,
                    'prev_end_date' => $prevEndDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));
                $prevDate = date("Ymd", strtotime("-1 day", strtotime($date)));

                $fcData = [
                    'start_date' => $today,
                    'end_date' => $today,
                    'prev_start_date' => $prevDate,
                    'prev_end_date' => $prevDate,
                ];
                break;
        }

        return $fcData;
    }
}