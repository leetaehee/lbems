<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class EquipmentSet 장비관리 수정, 등록, 삭제
 */
class EquipmentSet extends Command
{
    /**
     * EquipmentSet constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * EquipmentSet destructor.
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
        $setMode = isset($params[0]['value']) === true ? $params[0]['value'] : '';
        $option = isset($params[1]['value']) === true ? $params[1]['value'] : '';
        $sensorNo = isset($params[2]['value']) === true ? $params[2]['value'] : 0;
        $homeType = isset($params[3]['value']) === true ? $params[3]['value'] : '';
        $homeDongPk = isset($params[4]['value']) === true ? $params[4]['value'] : '';
        $homeHoPk = isset($params[5]['value']) === true ? $params[5]['value'] : '';
        $homeGrpPk = isset($params[6]['value']) === true ? $params[6]['value'] : '';
        $formStr = isset($params[7]['value']) === true ? $params[7]['value'] : '';

        $formData = $this->getFormData($option, $homeType, $homeDongPk, $homeHoPk, $sensorNo, $formStr, $setMode);
        if ($formData === null) {
            $data['error'] = 'dataError';
        }

        $data['result'] = $this->setProcess($complexCodePk, $option, $homeType, $homeDongPk, $homeHoPk, $homeGrpPk, $setMode, $formData);

        $this->data = $data;
        return true;
    }

    /**
     * 폼 데이터 검증 후 배열로 반환받기
     *
     * @param int $option
     * @param string $homeType
     * @param string $homeDongPk
     * @param string $homeHoPk
     * @param string $sensorNo
     * @param string $formString
     * @param string $setMode
     *
     * @return array|null
     */
    private function getFormData(int $option, string $homeType, string $homeDongPk, string $homeHoPk, string $sensorNo, string $formString, string $setMode) :? array
    {
        $fcData = [];

        if (empty($sensorNo) === true) {
            return null;
        }

        if (empty($formString) === true || strlen($formString) < 1) {
            return null;
        }

        if (empty($setMode) === true) {
            return null;
        }

        if (is_int($option) === false && empty($option) === true) {
            return null;
        }

        if (empty($homeType) === true || $homeType === 'all') {
            return null;
        }

        if (empty($homeDongPk) === true) {
            return null;
        }

        if (empty($homeHoPk) === true) {
            return null;
        }

        parse_str($formString, $fcData);

        return Utility::getInstance()->removeXSSFromFormData($fcData);
    }

    /**
     * 장비 등록/수정/삭제
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $homeType
     * @param string $homeDongPk
     * @param string $homeHoPk
     * @param string $homeGrpPk
     * @param string $setMode
     * @param array $formData
     *
     * @return array
     *
     * @throws \Exception
     */
    private function setProcess(string $complexCodePk, int $option, string $homeType, string $homeDongPk, string $homeHoPk, string $homeGrpPk, string $setMode, array $formData) : array
    {
        $fcData = [
            'error_type' => '',
            'success' => true,
        ];

        $time = date('H:i:s');

        $installedDate = $formData['popup_installed_date'];
        $lastestCheckDate = $formData['popup_lastest_check_date'];
        $replaceDate = $formData['popup_replace_date'];

        if (isset($formData['popup_building_type']) === false) {
            $formData['popup_building_type'] = '';
        }

        $popupBuildingType = $formData['popup_building_type'];

        $formData['popup_building_type'] = empty($popupBuildingType) === true ? $complexCodePk : $popupBuildingType;

        if (empty($installedDate) === false) {
            $formData['popup_installed_date'] = $installedDate . ' ' . $time;
        }

        if (empty($lastestCheckDate) === false) {
            $formData['popup_lastest_check_date'] = $lastestCheckDate . ' ' . $time;
        }

        if (empty($replaceDate) === false) {
            $formData['popup_replace_date'] = $replaceDate . ' ' . $time;
        }

        $f = $formData;

        switch ($setMode) {
            case 'i':
                $complexCodePk = $formData['popup_building_type'];

                // 홈 정보 확인
                $rHomeCheckQ = $this->emsQuery->getQueryHomeInfoValidate($complexCodePk, $homeType, $homeDongPk, $homeHoPk, $homeGrpPk);
                $rHomeCheckes = $this->query($rHomeCheckQ);

                if ((int)$rHomeCheckes[0]['cnt'] === 0) {
                    // 정보가 존재하지 않음
                    return [
                        'error_type' => 'not_right_info',
                        'success' => false,
                    ];
                }

                // 등록되어있는지 확인
                $rHomeValidateQ = $this->emsQuery->getQueryOverlapSensor($complexCodePk, $option, $homeDongPk, $homeHoPk);
                $rHomeValidates = $this->query($rHomeValidateQ);

                if ((int)$rHomeValidates[0]['cnt'] > 0) {
                    // 이미 등록됨..
                    return [
                        'error_type' => 'exist',
                        'success' => false,
                    ];
                }

                $rSensorNoOverlapQ = $this->emsQuery->getQueryIsOverlapSensorNo($option, $f['popup_sensor_sn']);
                $rSensorNoOverlap = $this->query($rSensorNoOverlapQ);

                if ($rSensorNoOverlap[0]['cnt'] > 0) {
                    // 센서정보 중복 확인
                    return [
                        'error_type' => 'overlap',
                        'success' => false,
                    ];
                }

                $cEquipmentQ = $this->emsQuery->insertEquipment($option, $f);
                $this->squery($cEquipmentQ);
                break;
            case 'u':
                 // 장비관리 수정
                $uEquipmentQ = $this->emsQuery->updateEquipment($option, $formData);
                $this->squery($uEquipmentQ);
                break;
        }

        return $fcData;
    }
}