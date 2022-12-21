<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class Instrument
 */
class Instrument extends Command
{
    /**
     * Instrument constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Instrument destructor.
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
        $complexCodePk = $_SESSION['ss_complex_pk'];

        $floor = isset($params[0]['value']) === true ? $params[0]['value'] : '';
        $startPage = isset($params[1]['value']) === true ? $params[1]['value'] : 1;
        $viewPageCount = isset($params[2]['value']) === true ? $params[2]['value'] : 13;

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 층 검색 조건 추가
        $floorQuery = Utility::getInstance()->makeWhereClause('search', 'home_grp_pk', $floor);

        // 층별 계측기 상태 조회
        $monitoringStatus = $this->getInstrumentStatus($complexCodePk);

        // 페이징 번호 셋팅
        $startPage = $startPage - 1;
        $startPage = $startPage < 1 ? 0 : ($startPage * $viewPageCount);

        // 계측기 상태 세부 조회
        $detailInstrumentStatus = $this->getDetailInstrumentStatus($complexCodePk, $startPage, $viewPageCount, $floorQuery);

        // 센서에 대한 별칭 정보
        $sensorAlias = $this->sensorObj->getSensorAliasInfo();

        // 뷰에 보여질 데이터 바인딩
        $data = [
            'monitoring_status' => $monitoringStatus,
            'detail_status' => $detailInstrumentStatus,
            'sensor_alias' => $sensorAlias,
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 계측기 층별 상태 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getInstrumentStatus(string $complexCodePk) : array
    {
        $fcData = [
            'floors' => [],
            'total_count' => 0,
            'defect_count' => 0,
        ];
        $totalDefectCount = 0;

        $floorQuery = '';

        // 층별 계측기 정보 조회
        $rInstrumentStatusQ = $this->emsQuery->getQuerySelectInstrumentStatusByFloor($complexCodePk);
        $rInstrument = $this->query($rInstrumentStatusQ);

        // 계측기 장애 내역 조회
        $rInstrumentStatusQ = $this->emsQuery->getQuerySelectInstrumentErrorCount($complexCodePk, $floorQuery);
        $rInstrumentStatus = $this->query($rInstrumentStatusQ);

        for ($fcIndex = 0; $fcIndex < count($rInstrument); $fcIndex++) {
            // 초기화
            $instrumentNo = $rInstrument[$fcIndex]['home_grp_pk'];
            $fcData['floors'][$instrumentNo] = 0;
            $fcData['total_count'] += $rInstrument[$fcIndex]['instrument_count'];
            $fcData['defect_count'] = 0;
        }

        for ($fcIndex = 0; $fcIndex < count($rInstrumentStatus); $fcIndex++) {
            $instrumentNo = $rInstrumentStatus[$fcIndex]['home_grp_pk'];

            $fcData['floors'][$instrumentNo] = $rInstrumentStatus[$fcIndex]['defect_count'];
            $fcData['defect_count'] += $rInstrumentStatus[$fcIndex]['defect_count'];
        }

        return $fcData;
    }

    /**
     * 계측기 세부 상태 조회
     *
     * @param string $complexCodePk
     * @param int $startPage
     * @param int $endPage
     * @param string $floorQuery
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getDetailInstrumentStatus(string $complexCodePk, int $startPage, int $endPage, string $floorQuery = '') : array
    {
        $fcData = [];
        $fcList = [];
        $energyTypeSensors = [];

        if (is_null($this->sensorObj) === false) {
            $energyTypeSensors = $this->sensorObj->getHindranceAlarmSensor();
        }

        $isSelected = empty($floorQuery) === true ? false : true;

        $tmpStartPage = $startPage;
        $tmpEndPage = $endPage;

        $startPage = $isSelected === false ? -1 : $startPage;
        $endPage = $isSelected === false ? -1 : $endPage;

        // 층별 계측기 정보 조회
        $rSensorQ = $this->emsQuery->getQuerySelectInstrumentDetailStatus($complexCodePk, $startPage, $endPage, $floorQuery);
        $rSensorData = $this->query($rSensorQ);

        $startPage = $isSelected === false ? $tmpStartPage : -1;
        $endPage = $isSelected === false ? $tmpEndPage : -1;

        // 장애 내역 조회
        $rDetailListQ = $this->emsQuery->getQuerySelectInstrumentError($complexCodePk, $floorQuery, $startPage, $endPage);
        $rDetailList = $this->query($rDetailListQ);

        if ($isSelected === false) {
            for ($fcIndex = 0; $fcIndex < count($rDetailList); $fcIndex++) {
                $homeGrpPk = $rDetailList[$fcIndex]['home_grp_pk'];
                $sensorType = $rDetailList[$fcIndex]['sensor_type'];
                $sensorNo = $rDetailList[$fcIndex]['sensor_sn'];

                for ($fcIndexS = 0; $fcIndexS < count($rSensorData); $fcIndexS++) {
                    // 발생한 장애에 대한 센서정보 조회
                    if ($homeGrpPk === $rSensorData[$fcIndexS]['home_grp_pk'] && $sensorType === $rSensorData[$fcIndexS]['sensor_type']
                        && $sensorNo === $rSensorData[$fcIndexS]['sensor_sn']) {
                        $rDetailList[$fcIndex]['memo'] = $rSensorData[$fcIndexS]['memo'];
                        $rDetailList[$fcIndex]['installed_date'] = $rSensorData[$fcIndexS]['installed_date'];
                    }

                    // 에너지원 키 이름을 이름으로 변경
                    $rDetailList[$fcIndex]['energy_type'] = $energyTypeSensors[$sensorType]['name'];
                }
            }

            $fcList = $rDetailList;
        }

        if ($isSelected === true) {
            for ($fcIndex = 0; $fcIndex < count($rSensorData); $fcIndex++) {
                $homeGrpPk = $rSensorData[$fcIndex]['home_grp_pk'];
                $sensorType = $rSensorData[$fcIndex]['sensor_type'];
                $sensorNo = $rSensorData[$fcIndex]['sensor_sn'];

                $rSensorData[$fcIndex]['alarm_on_off'] = '정상';
                $rSensorData[$fcIndex]['alarm_msg'] = '';

                for ($fcIndexS = 0; $fcIndexS < count($rDetailList); $fcIndexS++) {
                    if ($homeGrpPk == $rDetailList[$fcIndexS]['home_grp_pk']
                        && $sensorType == $rDetailList[$fcIndexS]['sensor_type']
                        && $sensorNo == $rDetailList[$fcIndexS]['sensor_sn']) {
                        $rSensorData[$fcIndex]['alarm_on_off'] = $rDetailList[$fcIndexS]['alarm_on_off'];
                        $rSensorData[$fcIndex]['alarm_msg'] = $rDetailList[$fcIndexS]['alarm_msg'];
                    }
                }

                $rSensorData[$fcIndex]['energy_type'] = $energyTypeSensors[$sensorType]['name'];
            }

            $fcList = $rSensorData;
        }

        // 계측기 세부 총 데이터 조회
        $rDetailCountQ = $this->emsQuery->getQuerySelectInstrumentCount($complexCodePk, $floorQuery);
        if ($isSelected === false) {
            $rDetailCountQ = $this->emsQuery->getQuerySelectInstrumentErrorCount($complexCodePk, $floorQuery, $isSelected);
        }

        $rDetailCount = $this->query($rDetailCountQ);

        $fcData = [
            'list' => $fcList,
            'count' => $isSelected === false ? $rDetailCount[0]['defect_count'] : count($rDetailCount),
        ];

        return $fcData;
    }
}