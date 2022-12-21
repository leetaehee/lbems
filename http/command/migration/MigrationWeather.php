<?php
namespace Http\Command;

use EMS_Module\MigrationQuery;

/**
 * Class MigrationWeather  bems_weather 테이블 암호화 마이그레이션
 */
class MigrationWeather extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

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
        $migrationQuery = new MigrationQuery();

        $rWeatherQ = $migrationQuery->getQuerySelectWheatherComplexData();
        $weatherData = $this->query($rWeatherQ);

        for ($i = 0; $i < count($weatherData); $i++) {
            $complexCodePk = $weatherData[$i]['complex_code_pk'];
            $name = $weatherData[$i]['name'];

            $uWeatherQ = $migrationQuery->getQueryUpdateWeatherData($complexCodePk, $name);
            //$this->squery($uWeatherQ);
        }

        $this->data = [];
        return true;
    }
}