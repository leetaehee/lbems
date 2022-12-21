<?php
namespace Http\Command;

/**
 * Class WeatherTempHumiCur
 */
class WeatherTempHumiCur extends Command
{
    /**
     * WeatherTempHumiCur constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * WeatherTempHumiCur destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
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
        $data = [
            'temp' => 0,
            'humi' => 0,
            'weat' => 0,
        ];

		$complexCodePk = $_SESSION['ss_complex_pk'];

        $dateInfo = $this->baseDateInfo;

        $hour = date('H', strtotime($dateInfo['date_time']));
        if ($hour < 10) {
            $hour = "{$hour}";
        }

        $query = $this->emsQuery->getQueryTempHumiCurrent($complexCodePk, $dateInfo['date'], $hour);
        $weather = $this->query($query);

        if (count($weather) > 0) {
            $data['temp'] = $weather[0]['temp'];
            $data['humi'] = $weather[0]['humi'];
			$data['weat'] = $weather[0]['weat'];
        }

        $this->data = $data;

        return true;
    }
}