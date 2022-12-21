<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class WeatherFinedust
 */
class WeatherFinedust extends Command
{
    /**
     * WeatherFinedust constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * WeatherFinedust destructor.
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
            'pm10' => 0,
            'pm25' => 0
        ];

        $complexCodePk = $_SESSION['ss_complex_pk'];

        $today = date('Y-m-d');
        $query = $this->emsQuery->getQueryFinedust($complexCodePk, $today);
        $d = $this->query($query);

        if (count($d) > 0) {
            $data['pm10'] = $d[0]['pm10'];
            $data['pm25'] = $d[0]['pm25'];
        }

		// 온도 습도 가져오기
        $today = $this->baseDateInfo['date'];;
		$hour = date('H', strtotime($today));
        
        if ($hour < 10) {
            $hour = "{$hour}";
        }

        $query = $this->emsQuery->getQueryTempHumiCurrent($complexCodePk, $today, $hour);
        $weather = $this->query($query);

        if (count($weather) > 0) {
            $data['temp'] = $weather[0]['temp'];
            $data['humi'] = $weather[0]['humi'];
			$data['weat'] = $weather[0]['weat'];
        }

        // 환경부 미세먼지 가져오기
        $complexQuery = Utility::getInstance()->makeWhereClause('complex', 'complex_code_pk', $complexCodePk);

        $query = $this->emsQuery->getQueryAirStationByComplex($complexQuery);
        $airStationData = $this->query($query);

        if (count($airStationData) > 0) {
            $data['air_pm10'] = $airStationData[0]['air_pm10'];
            $data['air_pm25'] = $airStationData[0]['air_pm25'];
        }

        $this->data = $data;

        return true;
    }
}