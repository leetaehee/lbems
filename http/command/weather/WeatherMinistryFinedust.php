<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class WeatherMinistryFinedust 환경부 미세먼지 수신 (크론탭)
 */
class WeatherMinistryFinedust extends Command
{
    /**
     * WeatherMinistryFinedust constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * WeatherMinistryFinedust destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메세지 출력
     * 
     * @param string $complexCodePk
     * @param string $message
     */
    private function getMinistryMessage(string $complexCodePk, string $message)
    {
        echo '-- ['.$complexCodePk.']' . $message .'-- <br>';
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
        // initialize.
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // 한국환경공단_에어코리아_대기오염정보 참조
        $url = 'http://apis.data.go.kr/B552584/ArpltnInforInqireSvc/getMsrstnAcctoRltmMesureDnsty?';

        // 에어코리아 서비스 키 조회
        $serviceKey = urlencode($this->devOptions['AIR_KOREA_SERVICE_KEY']);

        // parameter.
        $queryParam = 'serviceKey='.$serviceKey;
        $queryParam .= '&numOfRows=100';
        $queryParam .= '&pageNo=1';
        $queryParam .= '&dataTerm=DAILY';
        $queryParam .= '&returnType=xml';
        $queryParam .= '&ver=1.0';

        // parameter mapping.
        $url .= $queryParam;
        $complexCodePk = '';

        // 단지정보 가져오기
        $complexQuery = Utility::getInstance()->makeWhereClause('complex', 'complex_code_pk', $complexCodePk);
        $rComplexQ = $this->emsQuery->getQueryAirStationByComplex($complexQuery);
        $complexData = $this->query($rComplexQ);

        $complexCount = count($complexData);
        for ($z0 = 0; $z0 < $complexCount; $z0++) {
            $complexCodePk = $complexData[$z0]['complex_code_pk'];
            $airStationName = $complexData[$z0]['air_station_name'];

            if (empty($airStationName)) {
                // 관측소 설정되어있지 않으면 진행안함
                $this->getMinistryMessage($complexCodePk, 'bems_complex.air_station_name 미설정');
                continue;
			}

            // 관측소 넘기기
            $stationParam = '&stationName='  . urlencode($airStationName);

            // 실행
            curl_setopt($ch, CURLOPT_URL, $url. $stationParam);

            $response = curl_exec($ch);

            if ($response === false) {
                $this->getMinistryMessage($complexCodePk, 'curl 응답 오류');
                continue;
            }

            // xml 받아오기
            $xml = simplexml_load_string($response);

            $resultCode = $xml->header->resultCode;
            if ($resultCode == null || $resultCode != "00") {
                continue;
            }

            // 갯수
            $totalCount = $xml->body->totalCount;
            if ($totalCount == 0) {
                $this->getMinistryMessage($complexCodePk, '조회된 항목이 존재하지 않습니다.');
                continue;
            }

            //미세먼지농도 air_pm10
            $pm10Value = (int)$xml->body->items->item[0]->pm10Value;
            //초미세먼지농도 air_pm25
            $pm25Value = (int)$xml->body->items->item[0]->pm25Value;

            //미세먼지농도 변경
            $query = $this->emsQuery->getQueryUpdateFinedust($complexCodePk, $pm10Value, $pm25Value);
            $result = $this->squery($query);

            if ($result === false) {
                $this->getMinistryMessage($complexCodePk, '미세먼지 농도 변경 실패하였습니다.');
            }
        }

        $this->close();

        return true;
    }
}