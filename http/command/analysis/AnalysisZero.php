<?php
namespace Http\Command;

use EMS_Module\Indication;
use EMS_Module\Utility;

/**
 * Class AnalysisZero 분석 > 제로에너지 등급
 */
class AnalysisZero extends Command
{
    /** @var string $year 연도 */
    private string $year;

    /**
     * AnalysisZero constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 현재 연도로 초기화
        $this->year = date('Y');
    }

    /**
     * AnalysisZero destructor.
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
        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);

        if (isset($params[1]['value']) && !empty($params[1]['value'])) {
            $this->year = Utility::getInstance()->removeXSS($params[1]['value']);
        }

        $month = $params[0]['value'];
        if (!empty($month)){
            // 월 그래프 선택시에는 일데이터 가져오기
            $data['daily'] = $this->getDailyIndependencePecent($complexCodePk, $month);
        } else {
            // 금월 데이터 저장
            $data['month'] = $this->getMonthIndependencePercent($complexCodePk);

            // 금년 자립률 계산
            $data['year'] = $this->getYearIndependencePercent($complexCodePk);
        }

        // 데이터 출력
        $this->data = $data;

        return true;
    }

    /**
     * 일별 자립률 출력
     *
     * @param string $complexCodePk
     * @param string $month
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getDailyIndependencePecent(string $complexCodePk, string $month) : array
    {
        $fcData = [];

        $startDay = $month . '01';
        $endDay = $month.''.date("t", strtotime($startDay));

        $dateType = 2;

        $indication = new Indication($this);

        for ($day = $startDay; $day <= $endDay; $day++) {

            $indepentPercent = $indication->getIndependencePercent($complexCodePk, $dateType ,$day);
            $tempDay = date('j', strtotime($day));

            $co2Emission = $indication->getCo2Emission($complexCodePk, $dateType, $day);

            $fcData[$day] = [
                'day'=> $tempDay . '일',
                'production'=> $indepentPercent[1],
                'consumption'=> $indepentPercent[2],
                'co2Emission'=> $co2Emission
            ];
        }

        return $fcData;
    }

    /**
     * 월별 자립률 출력
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getMonthIndependencePercent(string $complexCodePk) : array
    {
        $fcData = [];

        $year = $this->year;

        $startMonth = $year . '01';
        $endMonth = $year . '12';

        $dateType = 1;

        $indication = new Indication($this);

        for ($month = $startMonth; $month <= $endMonth; $month++) {

            $monthToDay = $month . '01';

            $independents = $indication->getIndependencePercent($complexCodePk, $dateType, $monthToDay);
            $tempMonth = date('n', strtotime($monthToDay));

            $co2Emission = $indication->getCo2Emission($complexCodePk, $dateType, $monthToDay);
            $independenceGrade = $indication->getIndependenceGrade($independents[0]);

            $rateText = $this->getIndependenceRateFormat($independenceGrade, $independents[0], true);

            $fcData[$month] = [
                'value'=> $independents[0],
                'month'=> $tempMonth . '월',
                'grade_str'=> $rateText,
                'production'=> $independents[1],
                'consumption'=> $independents[2],
                'co2Emission'=> $co2Emission
            ];
        }

        return $fcData;
    }

    /**
     * 자립률, 생산/소비량, 등급 구하기
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getYearIndependencePercent(string $complexCodePk) : array
    {
        $fcData = [];

        $date = date('Ymd');

        $indication = new Indication($this);
        $independents = $indication->getIndependencePercent($complexCodePk, 0, $date);

        $fcData = [
            'grade'=> $indication->getIndependenceGrade($independents[0]),
            'indecatior_percent'=> $independents[0],
            'production'=> $independents[1],
            'consumption'=> $independents[2]
        ];

        return $fcData;
    }

    /**
     * 자립률 등급 구하기
     *
     * @param string $independenceGrade
     * @param int $value
     * @param bool $isAddStr
     *
     * @return string
     */
    private function getIndependenceRateFormat(string $independenceGrade, int $value, bool $isAddStr = false) : string
    {
        $independenceRate = $independenceGrade;

        if ($value > 0 && $isAddStr === true) {
            $independenceRate .= '('.number_format($value).'%)';
        }

        return $independenceRate;

    }
}