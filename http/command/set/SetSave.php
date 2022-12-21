<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class SetSave
 */
class SetSave extends Command
{
    /** @var Usage|null 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * SetSave constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SetSave destructor.
     */
    public function __destruct()
    {
        parent::__destruct();

        $this->usage = new Usage();
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
        $ssPK = $_SESSION['ss_pk'];

        $formArray = [];
        $mode =  isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $option = isset($params[1]['value']) === true ? $params[1]['value'] : 0;
        $closingDay = isset($params[2]['value']) === true ? (int)$params[2]['value'] : 0;
        $adminPk = isset($params[3]['value']) === true ? $params[3]['value'] : 0;
        $formData = isset($params[4]['value']) === true ? $params[4]['value'] : 0;

        // 폼 데이터 형식변경
        parse_str($formData, $formArray);

        // 크로스사이트스크립트 처리
        $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

        switch ($mode)
        {
            case 'basic' :
                // 이메일 수정
                //$uEmailQ = $this->emsQuery->updateComplexEmail($adminPk, $formArray['email'], $ssPK);
                //$this->squery($uEmailQ);

                // 암호화
                $formArray['name'] = Utility::getInstance()->updateEncryption($formArray['name']);
                $formArray['addr'] = Utility::getInstance()->updateEncryption($formArray['addr']);
                $formArray['tel'] = Utility::getInstance()->updateEncryption($formArray['tel']);
                $formArray['fax'] = Utility::getInstance()->updateEncryption($formArray['fax']);

                // 기본정보 수정
                $uBasicComplexQ = $this->emsQuery->updateComplexInfo($complexCodePk, $formArray);
                $this->squery($uBasicComplexQ);

                break;
            case 'closing_day' :
                // 마감일 변경
                if (($closingDay >= 1 && $closingDay <= 28) || $closingDay == 99) {
                    // 마감일은 1~28일, 99만 허용
                    $uEnergyTargetQ = $this->emsQuery->updateClosingDay($complexCodePk, $option, $closingDay);
                    //$this->squery($uEnergyTargetQ);
                } else {
                    $data['error'] = 'Error';
                    $this->data = $data;
                    return true;
                }
                break;
        }

        $this->data = [];
        return true;
    }
}