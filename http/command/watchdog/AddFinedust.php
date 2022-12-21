<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class AddFinedust MQTT 서버에서 미세먼지 정보 수신 
 */
class AddFinedust extends Command
{
    /**
     * AddFinedust constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddFinedust destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params

     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        // 무등산 건물 제외하고 검색.
        $complexQuery = Utility::getInstance()->makeWhereClause('sensor', 'complex_code_pk', '2001', '<>');

        $rFinedustQ = $this->emsQuery->getQueryFinedustSensor($complexQuery);
        $finedustSensors = $this->query($rFinedustQ);

        // bems_meter_finedust 에 데이터 추가
        $this->addFinedustMeterData($finedustSensors);
        $this->close();

        $this->data = $data;
        return true;
    }

    /**
     * bems_meter_finedust에 데이터 추가
     *
     * @param array $data
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function addFinedustMeterData(array $data) : bool
    {
        if (count($data) === 0) {
            return false;
        }

        // 미세먼지 서버 정보
        $mqttFinedustApiURL = $this->devOptions['MQTT_FINEDUST_API_URL'];

        // curl 통신
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $mqttFinedustApiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        for ($i = 0; $i < count($data); $i++) {
            $deviceEUI = $data[$i]['device_eui'];

            $postData['sensor'] = $deviceEUI;
            if (empty($postData) === true) {
                continue;
            }

            // post로 데이터 넘기기
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

            // 실행
            $results = curl_exec($ch);
            //$fcResultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // 데이터 배열로 변환
            $tempData = json_decode($results, true);
            if ($tempData['result'] !== 'OK') {
                // 오류가 있으면 추가하지 않는다.
                continue;
            }

            $valDate = date('Y-m-d H:i:s', strtotime($tempData['val_date']));
            $pm25 = $tempData['pm25'];
            $co2 = $tempData['co2'];
            $temperature = $tempData['temperature'];
            $humidity = $tempData['humidity'];

            // meter 추가
            $cFinedustQ = $this->emsQuery->getQueryInsertFinedustMeter($deviceEUI, $valDate, $pm25, $co2, $temperature, $humidity);
            $this->squery($cFinedustQ);

            // 센서정보 업데이트
            $valDate = $tempData['val_date'];

            $uFinedustQ = $this->emsQuery->getQueryUpdateFinedustSensor($deviceEUI, $valDate, $pm25, $co2, $temperature, $humidity);
            $this->squery($uFinedustQ);
        }

        // curl 통신 종료
        curl_close($ch);

        return true;
    }
}