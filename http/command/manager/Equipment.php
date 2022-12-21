<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class Equipment 장비관리 조회
 */
class Equipment extends Command
{
    /**
     * Equipment constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Equipment destructor.
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

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $adminLevel = $_SESSION['ss_level'];
        $startPage = isset($params[0]['value']) === true ? $params[0]['value'] : 1;
        $viewPageCount = isset($params[1]['value']) === true ? $params[1]['value'] : 10;
        $option = isset($params[2]['value']) ===  true ? $params[2]['value'] : 0;

        // 폼 데이터 배열로 변환
        parse_str($params[3]['value'], $formArray);

        $formData = Utility::getInstance()->removeXSSFromFormData($formArray);

        // 페이징 번호 셋팅
        $startPage = $startPage - 1;
        $startPage = $startPage < 1 ? 0 : ($startPage * $viewPageCount);

        $data['equipment_list'] = $this->getEquipmentListData($complexCodePk, $option, $adminLevel, $startPage, $viewPageCount, $formData);

        $this->data = $data;
        return true;
    }

    /**
     * 장비 관리 리스트 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $adminLevel
     * @param int $startPage
     * @param int $endPage
     * @param array $formData
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEquipmentListData(string $complexCodePk, int $option, int $adminLevel, int $startPage, int $endPage, array $formData) : array
    {
        $fcData = [];

        $anomalyTables = Config::ANOMALY_TABLES;
        $anomalyTable = $anomalyTables[$option];

        $startDateQuery = '';
        $endDateQuery = '';

        $formData['building_type'] = empty($formData['building_type']) === true ? $complexCodePk : $formData['building_type'];

        $complexQuery = Utility::getInstance()->makeWhereClause('sensor', 'complex_code_pk', $formData['building_type']);
        $fgUseQuery = Utility::getInstance()->makeWhereClause('sensor', 'fg_use', $formData['fg_use']);

        if ((isset($formData['start_date']) === true && empty($formData['start_date']) === false)
            &&(isset($formData['end_date']) === true && empty($formData['end_date']) === false)) {
            $startDateQuery = "AND DATE_FORMAT(`sensor`.`installed_date`, '%Y-%m-%d') >= '{$formData['start_date']}'";
            $endDateQuery = "AND DATE_FORMAT(`sensor`.`installed_date`, '%Y-%m-%d') <= '{$formData['end_date']}'";
        }

        // 이상증후 더 이상 생성하지 않음
        /*
            if (empty($anomalyTable) === false && $adminLevel === 100) {
                $this->updateAnomalyStatus($formData['building_type'], $option);
            }
        */

        $rEquipmentQ = $this->emsQuery->getQuerySelectEquipmentList($option, $startPage, $endPage, $complexQuery, $fgUseQuery, $startDateQuery, $endDateQuery);
        $rEquipmentList = $this->query($rEquipmentQ);

        $rEquipmentTotalQ = $this->emsQuery->getQuerySelectEquipmentCount($option, $complexQuery, $fgUseQuery, $startDateQuery, $endDateQuery);
        $rEquipmentTotal = $this->query($rEquipmentTotalQ);

        $fcData = [
            'list' => $rEquipmentList,
            'count' => $rEquipmentTotal[0]['cnt'],
        ];

        return $fcData;
    }

    /**
     * 이상증후 상태 변경
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @throws \Exception
     */
    private function updateAnomalyStatus(string $complexCodePk, int $option) : void
    {
        $apiUrl = $this->devOptions['AI_PREDICTION_API_URL'];

        if (empty($apiUrl) === false) {
            $rSensorQ = $this->emsQuery->getQuerySensorData($complexCodePk, true, $option);
            $sensorData = $this->query($rSensorQ);

            //$lPreviousDate = date('Ymd', strtotime(date('Ymd') . '-1 day'));
            $lToday = date('Ymd');

            for ($z = 0; $z <= count($sensorData); $z++) {

                $fcResultURL = '';
                $fcSensorNo = $sensorData[$z]['sensor'];

                if (empty($fcSensorNo) === true) {
                    continue;
                }

                $detailUrl = 'anomaly/sensor/date/mul';

                $fcResultURL = $apiUrl . "/{$detailUrl}?sensor_id={$fcSensorNo}";
                $fcResultURL .= "&from_date={$lToday}";
                $fcResultURL .= "&to_date={$lToday}";

                $responses = $this->getResponse($fcResultURL);

                $resArray = json_decode($responses, true);
                $resArray = $resArray['data'];

                if (count($resArray) > 0) {
                    for ($fcIndex = 0; $fcIndex < count($resArray); $fcIndex++) {
                        $anomaly = (int)$resArray[$fcIndex]['Anomaly'];
                        $anomalyScore = (float)$resArray[$fcIndex]['Anomaly score'];
                        $sensorNo = $resArray[$fcIndex]['sensor_id'];

                        $uSensorAnomalyQ = $this->emsQuery->getQueryUpdateAnomalyData($option, $sensorNo, $anomaly, $anomalyScore);
                        $this->squery($uSensorAnomalyQ);
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
}