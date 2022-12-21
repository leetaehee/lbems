<?php
namespace EMS_Module;

use Http\Command\Command;

/**
 * Class Finedust
 */
class Finedust
{
    /** @var EmsQuery|null $emsQuery */
    private ?EMSQuery $emsQuery = null;

    /**
     * Finedust Constructor.
     */
    public function __construct()
    {
        $this->emsQuery = new EMSQuery();
    }

    /**
     * Finedust Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * finedust 테이블에서 주기별 데이터 조회
     *
     * @param Command $command
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $date
     * @param string $finedustType
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getFinedustData(Command $command, string $complexCodePk, int $dateType, string $date, string $finedustType) : array
    {
        $fcData = [];

        $dateType = (int)$dateType;

        /*
         * 추후 roren_date_module.php에 getEnergyData() getQuery()처럼 할 것
         * 일,월통계 테이블 이용
         */
        $periods = $this->getPeriodRange($dateType, $date);
        $startDate = $periods['start_date'];
        $endDate = $periods['end_date'];

        if ($dateType === 0) {
            // 금년
            $query = $this->emsQuery->getFinedustYearData($complexCodePk, $startDate, $endDate, $finedustType);
        }

        if ($dateType === 1) {
            // 금월
            $query = $this->emsQuery->getFinedustMonthData($complexCodePk, $startDate, $endDate, $finedustType);
        }

        if ($dateType === 2) {
            // 금일
            $query = $this->emsQuery->getFinedustDailyData($complexCodePk, $startDate, $endDate, $finedustType);
        }

        // 데이터 추가
        $d = $command->query($query);

        // 시간부분은 삭제
        $startDate = substr($startDate, 0, 10);
        $startDate = str_replace('-','', $startDate);

        $endDate = substr($endDate, 0, 10);
        $endDate = str_replace('-','', $endDate);

        // 배열 정돈
        $d = $this->rearrangeData($d, $dateType, $startDate, $endDate, $finedustType);
        $fcData = $d;

        return $fcData;
    }

    /**
     * 주기에 따른 날짜 범위 반환 (마감일과 관련없음)
     *
     * @param int $dateType
     * @param string $date
     *
     * @return array $fcPeriods
     */
    private function getPeriodRange(int $dateType, string $date) : array
    {
        $fcPeriods = [];

        $startHour = '00:00:00';
        $endHour = '23:59:59';

        $startDate = $endDate = '';
        $tempDate = date('Y-m-d', strtotime($date));

        switch($dateType)
        {
            case 0:
                // 금년
                $currentYear = date('Y', strtotime($date));

                // 날짜 설정
                $startYear = date('Ymd', strtotime($currentYear . '0101'));
                $endYear = date('Ymd', strtotime($currentYear . '1201'));

                // 1월, 12월에 대해 말일 구하기
                $endDayByStartYear = date('t', strtotime($startYear));
                $endDayByEndYear = date('t', strtotime($endYear));

                // 년월만 출력
                $startMonth = date('Y-m', strtotime($startYear));
                $endMonth = date('Y-m', strtotime($endYear));

                // 시작년월~종료년월로 설정
                $startDate = $startMonth . '-' . $endDayByStartYear . ' ' . $startHour;
                $endDate = $endMonth . '-' . $endDayByEndYear . ' ' . $endHour;

                break;
            case 1:
                // 금월
                $endDay = date('t', strtotime($tempDate));
                $nowMonth = date('Y-m', strtotime($tempDate));
                $startDate = $nowMonth . '-01 ' . $startHour;
                $endDate = $nowMonth . '-' . $endDay .' '. $endHour;
                break;
            case 2:
                // 금일
                $startDate = $tempDate .' '. $startHour;
                $endDate = $tempDate .' '. $endHour;
                break;
        }

        $fcPeriods = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        return $fcPeriods;
    }

    /**
     * 주기에 타입에 따라 데이터 정돈
     *
     * @param array $d
     * @param int $dateType
     * @param string $startDate
     * @param string $endDate
     * @param string $finedustType
     *
     * @return array $fcData
     */
    private function rearrangeData(array $d, int $dateType, string $startDate, string $endDate, string $finedustType) : array
    {
        $fcData = [];

        // 초기화
        switch ($dateType)
        {
            case 0 :
                $tempDate = substr($startDate, 0, 6);
                $startDate = date('Ymd', strtotime($tempDate . '10'));

                while (1) {
                    $monthKey = date('Ym', strtotime($startDate));

                    if ($startDate > $endDate) {
                        break;
                    }

                    $fcData[$monthKey] = 0;

                    // 월 증가
                    $startDate = date('Ymd', strtotime($startDate . "+1 month"));
                }
                break;
            case 1 :
                // 금월
                while (1) {
                    if ($startDate > $endDate) {
                        break;
                    }

                    $fcData[$startDate] = 0;

                    // 일 증가
                    $startDate = date('Ymd', strtotime($startDate . "+1 day"));
                }

                break;
            case 2 :
                // 금일
                for ($i = 0; $i < 24; $i++) {
                    $tempHour = $startDate . $i;
                    if ($i < 10) {
                        $tempHour = $startDate . '0' . $i;
                    }

                    $fcData[$tempHour] = 0;
                }
                break;
        }

        // 날짜에 데이터 매핑하기
        foreach ($d AS $key => $value) {
            $wDate = str_replace('-', '', $value['w_date']);
            $wDate = str_replace(' ', '', $wDate);

            if (array_key_exists($wDate, $fcData) === true) {
                $fcData[$wDate] = $value[$finedustType];
            }
        }

        return $fcData;
    }
}