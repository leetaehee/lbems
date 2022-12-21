<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class ManagerLogin
 */
class ManagerLogin extends Command
{
    /**
     * ManagerLogin constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ManagerLogin destructor.
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
        $formArray = [];

        $complexCodePk = $_SESSION['ss_complex_pk'];

        // 폼 데이터 받기
        parse_str($params[0]['formData'], $formArray);
        $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

        // 페이징정보
        $startPage = $params[0]['start_page']-1;
        $endPage = $params[0]['view_page_count'];
        if ($startPage < 1) {
            $startPage = 0;
        } else {
            $startPage = $startPage * $endPage;
        }

        $complexCodePk = empty($formArray['building_type']) === true ? $complexCodePk : $formArray['building_type'];

        // 로그인 로그
        $rLoginLogQ = $this->emsQuery->getLoginLog($complexCodePk, $formArray, $startPage, $endPage);
        $loginLog = $this->query($rLoginLogQ);

        // 로그인 로그 카운트
        $rLoginLogCntQ = $this->emsQuery->getLoginLogCount($complexCodePk, $formArray);
        $loginLogCount = $this->query($rLoginLogCntQ);

        $data['log_data'] = [
            'list'=> $this->getTransData($loginLog),
            'count'=> $loginLogCount[0]['cnt']
        ];

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
            $ipAddr = $fcData[$fcIndex]['ip_addr'];

            $fcData[$fcIndex]['name'] = Utility::getInstance()->updateDecryption($name);
            $fcData[$fcIndex]['ip_addr'] = Utility::getInstance()->updateDecryption($ipAddr);
        }

        return $fcData;
    }
}