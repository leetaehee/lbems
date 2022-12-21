<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Usage;

/**
 * Class ArrangeAiPrediction AI 예측 정보 받아오기
 */
class ArrangeAiPrediction extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

	/** @var int[] $checkEnergyType 예측 데이터를 허용하는 에너지원 */
    private array $checkEnergyType = [0, 11];

    /**
     * ArrangeAiPrediction constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * ArrangeAiPrediction destructor.
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
        $apiUrl = $this->devOptions['AI_PREDICTION_API_URL'];

        if (empty($apiUrl) === true) {
            return true;
        }

        $this->updateAiPrediction($apiUrl);

        $this->data = [];
        return true;
    }

    /**
     * 예측 데이터 업데이트
     *
     * @param string $apiUrl
     *
     * @throws \Exception
     */
    private function updateAiPrediction(string $apiUrl) : void
    {
        $previousDate = date('Ymd', strtotime(date('Ymd') . '-1 day'));

        $detailUrl = 'sensor/date/mul/sum';

        $rComplexQ = $this->emsQuery->getQuerySelectComplex();
        $complexResult = $this->query($rComplexQ);

        $complexCount = count($complexResult);

        $sensorTypes = Config::SENSOR_TYPES;
        $tempApiUrl = $apiUrl;

        for ($z0 = 0; $z0 < $complexCount; $z0++) {
            // 고객시설 정보 받아오기
            $complexCodePk = $complexResult[$z0]['complex_code_pk'];

            $tableCount = count(Config::SENSOR_TABLES);
            for ($j = 0; $j < $tableCount; $j++) {
                $option = $j;

                if (in_array($option, $this->checkEnergyType) === false) {
                    /*
                     * lbems - 전기,태양광만 예측 추가
                     * bems - 전기,가스,수도, 급탕, 난방, 태양광 예측 추가
                     *
                     * 최종 적용시 전기, 태양광(?) 적용.. 그전에는 전기만
                     */
                    continue;
                }

                $rSensorQ = $this->emsQuery->getQuerySensorData($complexCodePk, true, $option);
                $sensorData = $this->query($rSensorQ);

                $sensorType = $sensorTypes[$option];

                $predictionDateInfo = $this->getPredictionDateInfo($option, $complexCodePk);
                foreach ($predictionDateInfo AS $key => $items) {
                    $sensorType = ($sensorType === 'electric') ? 'electricity' : $sensorType;

                    for ($z = 0; $z <= count($sensorData); $z++) {

                        $fcSensorNo = $sensorData[$z]['sensor'];

                        if (empty($fcSensorNo) === true) {
                            continue;
                        }

                        $apiUrl = $tempApiUrl . "/{$sensorType}/{$detailUrl}?sensor_id={$fcSensorNo}";
                        $apiUrl .= "&from_date={$items['start']}";
                        $apiUrl .= "&to_date={$items['end']}";

                        $responses = $this->getResponse($apiUrl);

                        $resArray = json_decode($responses, true);
                        if (count($resArray) === 0) {
                            continue;
                        }

                        for ($fcIndex = 0; $fcIndex < count($resArray); $fcIndex++) {
                            $predictionUsage = (int)$resArray[$fcIndex]['prediction_usage'];
                            $sensorNo = $resArray[$fcIndex]['_id'];

                            $column = $this->getRealKey($key);
                            if (empty($column) === true) {
                                continue;
                            }

                            $uPredictQ = $this->emsQuery->getQueryUpdatePredict($option, $previousDate, $sensorNo, $column, $predictionUsage);
                            $this->squery($uPredictQ);
                        }
                    }
                }
            }
        }
    }

    /**
     * curl 통신
     *
     * @param string $url
     *
     * @return string
     */
    private function getResponse(string $url) : string
    {
        $ch = curl_init(); // curl 초기화

        curl_setopt($ch, CURLOPT_URL, $url); // URL 지정하기
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 반환
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MaxTimeout); // connection timeout 60초
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사 안함

        $resData = curl_exec($ch); // 값을 받음

        curl_close($ch);  // close

        return $resData;
    }

    /**
     * 예측 컬럼명 조회
     *
     * @param string $periodString
     *
     * @return string
     */
    private function getRealKey(string $periodString) : string
    {
        $predictColumnNames = Config::PREDICT_COLUMN_NAMES;

        $fcString = '';

        switch ($periodString) {
            case 'daily':
                $fcString = $predictColumnNames[2];
                break;
            case 'weekly':
                $fcString = $predictColumnNames[5];
                break;
            case 'month':
                $fcString = $predictColumnNames[1];
        }

        return $fcString;
    }

    /**
     * 예측 주기별 기간 조회
     *
     * @param int $option
     * @param string $complexCodePk
     *
     * @return array[]
     *
     * @throws \Exception
     */
    private function getPredictionDateInfo(int $option, string $complexCodePk) : array
    {
        $usage = $this->usage;

        $date = date('Ymd');
        $time = strtotime($date);

        $lastMonth = date('Ym', strtotime($date . '-1 month'));

        // 금일
        $dailyStartDate = $dailyEndDate = date('Ymd', strtotime($date));

        // 금주
        $dayOfTheWeek = date('w', $date);
        if ($dayOfTheWeek === '0') {
            // 현재가 일요일인 경우 다음날로 +1 변경한다.
            $time = strtotime($date . '+1 day');
        }
        $weeklyStartDate = date('Ymd', strtotime('LAST SUNDAY', $time));
        $weeklyEndDate = date('Ymd', strtotime('SATURDAY', $time));

        // 금월
        $monthPeriods = $usage->getDueDatePeriodByMonth($this, $complexCodePk, $option, $lastMonth);
        $monthStartDate = date('Ymd', strtotime($monthPeriods['start_date']));
        $monthEndDate = date('Ymd', strtotime($monthPeriods['end_date']));

        return [
            'daily' => [
                'start' => $dailyStartDate,
                'end' => $dailyEndDate
            ],
            'weekly' => [
                'start' => $weeklyStartDate,
                'end' => $weeklyEndDate
            ],
            'month' => [
                'start' => $monthStartDate,
                'end' => $monthEndDate
            ]
        ];
    }
}