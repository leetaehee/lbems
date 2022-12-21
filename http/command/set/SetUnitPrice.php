<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class SetUnitPrice
 */
class SetUnitPrice extends Command
{
    /**
     * SetUnitPrice constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SetUnitPrice destructor.
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
        $ssPK = $_SESSION['ss_pk'];
        $ssId = $_SESSION['ss_id'];

        $costPk = $params[0]['cost_pk'];
        $energyType =  $params[0]['energy_type'];
        $mode = $params[0]['mode'];

        $formArray = [];
        $isOverlap = true;

        if (!empty($mode)) {
            if ($mode == 'del') {
                if (isset($params[0]['popup_roren_type'])) {
                    $complexCodePk = $params[0]['popup_roren_type'];
                }

                // 삭제
                $uUnitPriceQ = $this->emsQuery->deleteUnitPrice($complexCodePk, $energyType, $costPk);
                $this->squery($uUnitPriceQ);
            } elseif($mode == 'select') {
                if (isset($params[0]['popup_roren_type'])) {
                    $complexCodePk = $params[0]['popup_roren_type'];
                }

                // 키 값을 가지고 데이터 가져오기
                $rUnitPriceQ = $this->emsQuery->getUnitPrice($complexCodePk, $energyType, $costPk);
                $rUnitPriceRslt = $this->query($rUnitPriceQ);

                $data['priceData'] = $rUnitPriceRslt[0];
            } elseif($mode == 'update') {
                // 수정
                parse_str($params[0]['formData'], $formArray);
                $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

                // 계정정보 추가
                $formArray['ss_pk'] = $ssPK;
                $formArray['complex_code_pk'] = $complexCodePk;

                // 수정
                $uUnitPriceQ = $this->emsQuery->updateUnitPrice($costPk, $energyType, $formArray);
                $this->squery($uUnitPriceQ);

                $data['isOverlap'] = $isOverlap;
            } elseif($mode == 'insert') {
                // 추가
                parse_str($params[0]['formData'], $formArray);
                $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

                // 계정정보 추가
                $formArray['ss_id'] = $ssId;

                if (isset($formArray['popup_roren_type'])) {
                    $formArray['complex_code_pk'] = $formArray['popup_roren_type'];

                }else{
                    $formArray['complex_code_pk'] = $complexCodePk;
                }

                // 중복검사
                $rOverlapQ = $this->emsQuery->getIsOverlapEnergyPrice($energyType, $formArray);
                $rOverlapRslt = $this->query($rOverlapQ);

                if ($rOverlapRslt[0]['cnt'] > 0) {
                    $isOverlap = false;
                }

                if ($isOverlap == true) {
                    // 추가
                    $uUnitPriceQ = $this->emsQuery->insertUnitPrice($energyType, $formArray);
                    $this->squery($uUnitPriceQ);
                }

                $data['isOverlap'] = $isOverlap;
            }
        }

        $this->data = $data;

        return true;
    }
}