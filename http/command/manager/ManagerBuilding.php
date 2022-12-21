<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class ManagerBuilding
 */
class ManagerBuilding extends Command
{
    /**
     * ManagerBuilding constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ManagerBuilding destructor.
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
        $formArray = [];
        $isOverlap = true;

        $ssPK = $_SESSION['ss_pk'];

        $mode = '';

        if (isset($params[0]['mode'])) {
            parse_str($params[0]['formData'], $formArray);
            $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

            $mode = $params[0]['mode'];
            $complexCodePk = $params[0]['complex_code_pk'];

            if ($mode === 'del') {
                $uComplexDelQ = $this->emsQuery->deleteRorenData($complexCodePk, $ssPK);

                // 로렌하우스 삭제
                $this->squery($uComplexDelQ);
            } else if ($mode === 'update') {
                // 수정

                $adminPk = $params[0]['admin_pk'];
                $email = $formArray['email'];
                $closingDayElectric = (int)$formArray['closing_day_electric'];

                // 계정정보 추가
                $formArray['ss_pk'] = $ssPK;

                // 암호화
                $formArray['name'] = Utility::getInstance()->updateEncryption($formArray['name']);
                $formArray['addr'] = Utility::getInstance()->updateEncryption($formArray['addr']);
                $formArray['tel'] = Utility::getInstance()->updateEncryption($formArray['tel']);
                $formArray['fax'] = Utility::getInstance()->updateEncryption($formArray['fax']);

                // 수정(로렌하우스 정보)
                if (($closingDayElectric >= 1 && $closingDayElectric <= 28) || $closingDayElectric === 99) {
                    $uComplexQ = $this->emsQuery->updateComplexs($formArray);
                    $this->squery($uComplexQ);
                } else {
                    $data['error'] = 'Error';
                    $this->data = $data;
                    return true;
                }

                $data['isOverlap'] = $isOverlap;
            } else if ($mode === 'insert') {
                // 추가

                $complexCodePk = $formArray['complex_code_pk'];
                $closingDayElectric = (int)$formArray['closing_day_electric'];

                // 계정정보 추가
                $formArray['ss_pk'] = $ssPK;

                // 중복검사
                $rOverlapQ = $this->emsQuery->getIsOverlapComplexCode($complexCodePk);
                $rOverlapRslt = $this->query($rOverlapQ);

                if ($rOverlapRslt[0]['cnt'] > 0) {
                    $isOverlap = false;
                }

                if ($isOverlap === true) {
                    if (($closingDayElectric >= 1 && $closingDayElectric <= 28) || $closingDayElectric === 99) {
                        // 암호화
                        $formArray['name'] = Utility::getInstance()->updateEncryption($formArray['name']);
                        $formArray['addr'] = Utility::getInstance()->updateEncryption($formArray['addr']);
                        $formArray['tel'] = Utility::getInstance()->updateEncryption($formArray['tel']);
                        $formArray['fax'] = Utility::getInstance()->updateEncryption($formArray['fax']);

                        // 추가
                        $cComplexCodeQ = $this->emsQuery->insertComplexs($formArray);
                        $this->squery($cComplexCodeQ);
                    } else {
                        $data['error'] = 'Error';
                        $this->data = $data;
                        return true;
                    }
                }

                $data['isOverlap'] = $isOverlap;
            } else if ($mode === 'select') {
                $rComplexDataQ = $this->emsQuery->getRorenData($complexCodePk);
                $rComplexData = $this->query($rComplexDataQ);

                $rComplexData[0]['name'] = Utility::getInstance()->updateDecryption($rComplexData[0]['name']);
                $rComplexData[0]['addr'] = Utility::getInstance()->updateDecryption($rComplexData[0]['addr']);
                $rComplexData[0]['tel'] = Utility::getInstance()->updateDecryption($rComplexData[0]['tel']);
                $rComplexData[0]['fax'] = Utility::getInstance()->updateDecryption($rComplexData[0]['fax']);
                $rComplexData[0]['email'] = Utility::getInstance()->updateDecryption($rComplexData[0]['email']);

                $data['complex_data'] = $rComplexData[0];
            }
        } else {
            // 리스트 추출

            // 페이징정보
            $startPage = $params[0]['start_page']-1;
            $endPage = $params[0]['view_page_count'];
            if ($startPage < 1) {
                $startPage = 0;
            } else {
                $startPage = $startPage * $endPage;
            }

            // 로렌하우스 리스트
            $rComplexListQ = $this->emsQuery->getAllRorenList($startPage, $endPage);
            $rComplexList = $this->query($rComplexListQ);

            // 카운트
            $rComplexCountQ = $this->emsQuery->getAllRorenCount();
            $rCountRslt = $this->query($rComplexCountQ);

            $data['complex_data'] = [
                'list'=> $this->getTransData($rComplexList),
                'count'=> $rCountRslt[0]['cnt']
            ];
        }

        $this->data = $data;
        return true;
    }

    /**
     * 암호화 된 데이터를 복호화 시킴
     *
     * @param array $data
     *
     * @return array
     */
    private function getTransData(array $data) : array
    {
        $fcData = $data;

        for ($fcIndex = 0; $fcIndex < count($data); $fcIndex++) {
            $name = $data[$fcIndex]['name'];
            $fcData[$fcIndex]['name'] = Utility::getInstance()->updateDecryption($name);

            $addr = $data[$fcIndex]['addr'];
            $fcData[$fcIndex]['addr'] = Utility::getInstance()->updateDecryption($addr);

            $tel = $data[$fcIndex]['tel'];
            $fcData[$fcIndex]['tel'] = Utility::getInstance()->updateDecryption($tel);

            $fax = $data[$fcIndex]['fax'];
            $fcData[$fcIndex]['fax'] = Utility::getInstance()->updateDecryption($fax);

            $email = $data[$fcIndex]['email'];
            $fcData[$fcIndex]['email'] = Utility::getInstance()->updateDecryption($email);
        }

        return $fcData;
    }
}