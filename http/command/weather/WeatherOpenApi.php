<?php
namespace Http\Command;

/**
 * Class WeatherOpenApi 날씨 정보 수신 (크론탭)
 */
class WeatherOpenApi extends Command 
{
    /**
     * WeatherOpenApi constructor.
     */
    public function __construct() 
	{
        parent::__construct();
    }

    /**
     * WeatherOpenApi destructor.
     */
    public function __destruct() 
	{
        parent::__destruct();
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
     * 배열로 바꾸기
     *
     * @param string $resData
     *
     * @return array
     */
    private function convertToArray(string $resData) : array
	{                      
		// 값을 가지고 배열의 형태로
		$xml = simplexml_load_string($resData); // string형 xml을 php형태의 object로 불러온다
        $times = $xml->forecast->time;
        $arrData = [];

        foreach($times as $time) {
            if (strlen($time['from']) <= 10) {
                // time from의 길이가 10보다 같거나 작으면
                continue; // 현재 loop를 중단하고 다음 loop로, 적절한 값이 아닐수 있으므로
            }

            $valDate = substr($time['from'], 0, 10); // ex 2019-08-03
            $pos = strpos($time['from'], "T"); // 위치를 정수로 반환하며, strpos() 함수는 true & false로 반환한다.

            if ($pos === false) {
                // T가 없다면 다시 loop로 돌아갈것 (T가 있어야 한다, 한번 더 묻기)
                continue;
            }

            $hour = substr($time['from'], $pos + 1, 2); // ex 00, 06, 21

            if (array_key_exists($valDate, $arrData) == false) {
                // ($arrData)배열에 ($valdate)키값 있는지 / true & false
                $arrData[$valDate] = [];
            }

            if (array_key_exists($hour, $arrData[$valDate]) == false) {
                // ($arrData[$valDate])배열에 ($hour)키값 있는지
                $arrData[$valDate][$hour] = [];
            }

            $temp = (string)$time->temperature['value'];
            $humi = (string)$time->humidity['value'];
            $arrData[$valDate][$hour] = array(array($temp,$humi));
        }

        return $arrData;
    }

    /**
     * 디비에 반영
     *
     * @param array $arrData
     * @param string $complexCodePk
     * @param string $name
     * @param string $sunRise
     * @param string $sunSet
     *
     * @throws \Exception
     */
    private function updateDbTable(array $arrData, string $complexCodePk, string $name, string $sunRise, string $sunSet) : void
	{
        $complex_pk = $complexCodePk;
        $name = $name;
        $queryData = [];

        foreach ($arrData as $val_date => $value) {
            $valDate = str_replace('-', '', $val_date);
            $tempHour = '';
            $humiHour = '';
            $tempData = '';
            $humiData = '';
            $tempUpdateQuery = '';
            $humiUpdateQuery = '';

            foreach ($value as $hour => $values) {
                $tempHourUpdateData = " temperature_".$hour;
                $humiHourUpdateData = " humidity_".$hour;
                $tempHour = " temperature_".$hour.",".$tempHour;
                $humiHour = " humidity_".$hour.",".$humiHour;

                foreach ($values as $key => $valuesData) {
                    $tempUpdateData = (double)$valuesData[0] - (double)273.15;
                    $humiUpdateData = $valuesData[1];
                    $tempDataTemp = (double)$valuesData[0] - (double)273.15;
                    $humiDataTemp = $valuesData[1];
                    $tempData = "'".$tempDataTemp."',".$tempData;
                    $humiData = "'".$humiDataTemp."',".$humiData;
                    $temp_humi = substr($tempData.$humiData , 0, -1);
                    $tempUpdateQuery = "${tempHourUpdateData}='${tempUpdateData}',".$tempUpdateQuery;
                    $humiUpdateQuery = "${humiHourUpdateData}='${humiDataTemp}',".$humiUpdateQuery;
                }
            }

            //sunrise, sunset
            $updateTempHumi = $tempUpdateQuery.$humiUpdateQuery;
            $updateTempHumi = substr($updateTempHumi , 0, -1);
            $tempHour = substr($tempHour , 0, -1);
            $humiHour = substr($humiHour , 0, -1);

            //$sunRise = '0000-00-00 00:00:00';
            //$sunSet  = '0000-00-00 00:00:00';

            $today = date('Ymd');
            if ($valDate == $today) {
                continue;
                //$sunRise = '0000-00-00 00:00:00';
                //$sunSet  = '0000-00-00 00:00:00';
            }

            $query = $this->emsQuery->getQueryWeatherInfos($tempHour, $humiHour, $complex_pk, $name, $valDate, $temp_humi, $updateTempHumi);// query문을 받아오고
            $this->squery($query); // query안에 querys 가 있으므로 값을 받아올수 있음  + query 날리고
        }
    }

    /**
     * 날씨 변경
     *
     * @param array $complexInfos
     *
     * @throws \Exception
     */
    private function updateWeather(array $complexInfos) : void
	{
        $appId = $this->devOptions['OPEN_WEATHER_APP_ID'];
        $len = count($complexInfos);

        for($i = 0; $i < $len; $i++) {
            $complexCodePk = $complexInfos[$i]['complex_code_pk'];
            $name = $complexInfos[$i]['name'];
            $lat = $complexInfos[$i]['lat'];
            $lon = $complexInfos[$i]['lon'];

            if ($lat == null || $lon == null || $name == null || $complexCodePk == null || $appId == null) {
                // $appId 추가 8/6
                continue;  // 다시 loop
            }

            $url = "http://api.openweathermap.org/data/2.5/forecast?mode=xml&lat=${lat}&lon=${lon}&APPID=${appId}";
            $urlSun = "http://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&APPID=${appId}"; // sunrise, sunset 값 가져올 url

            $resData = $this->getResponse($url);
            $xmlResData = simplexml_load_string($resData);
            $xmlResDataPos = strpos($xmlResData, "ClientError");

            if ($xmlResDataPos === true) {
                // Client Error를 가지고 있으면
                continue; // 다시 loop
            }

            $urlSunData = $this->getResponse($urlSun);
            $sunData = json_decode($urlSunData, true);

            if ($sunData["cod"] !== 200) {
                // ex) success 200, error 400 / 200이 아니면
                $sunRise = '0000-00-00 00:00:00';
                $sunSet  = '0000-00-00 00:00:00';
                continue; // 다시 loop
            }

            $sunRise = date('Y-m-d H:i:s', $sunData['sys']['sunrise']);
            $sunSet = date('Y-m-d H:i:s', $sunData['sys']['sunset']);
            $tempCur = (double)$sunData['main']['temp'] - (double)273.15;
            $humiCur = $sunData['main']['humidity'];
            $weatherCur = $sunData['weather'][0]['icon']; // https://openweathermap.org/weather-conditions

            if ($resData) {
                // response 해서 받은 값이 있다면
                $arrData = $this->convertToArray($resData);
                $this->updateDbTable($arrData, $complexCodePk, $name, $sunRise, $sunSet); // 실행부분
            }

            //Update Today
            $query = $this->emsQuery->getQueryUpdateCurrentTempHumi($complexCodePk, $tempCur, $humiCur, $sunRise, $sunSet, $weatherCur);
            $this->squery($query);
        }
    }

    /**
     * 메인 함수 실행
     *
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
	{
        $query = $this->emsQuery->getQueryBemsInfos();
        $complexInfos = $this->query($query);

        $this->updateWeather($complexInfos);

        return true;
    }
}