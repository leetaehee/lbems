<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class ManagerAuthority
 */
class ManagerAuthority extends Command
{
   // 키(번호) => 값(레벨)
    /** @var array|int[] $loginLevel 권한 정보  : 키 => 레벨 */
    private array $loginLevel = Config::LOGIN_LEVEL_DATA['key'];

    /** @var array|string[] $rLoginLevel 권한정보 :  레벨 => 레벨명  */
    private array $rLoginLevel = Config::LOGIN_LEVEL_DATA['value'];

    /**
     * ManagerAuthority constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ManagerAuthority destructor.
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
        $ssPK = $_SESSION['ss_pk'];
        $ssId = $_SESSION['ss_id'];

        $isOverlap = true;
        $isDelete = false;
        $mode = $params[0]['mode'];

        // 레벨 : 값 => 번호
        $rLevelType = $this->rLoginLevel;
        // 레벨 : 번호 => 값
        $levelType = $this->loginLevel;

        if ($mode === 'select') {
            $adminPk = $params[0]['admin_pk'];

            $rAuthorityDataQ = $this->emsQuery->getAuthorityData($adminPk);
            $authorityData = $this->query($rAuthorityDataQ);

            $authorityData[0]['login_level'] = $rLevelType[$authorityData[0]['login_level']];

            // 양방향 암호화에 대한 복호화
            $authorityData[0]['name'] = Utility::getInstance()->updateDecryption($authorityData[0]['name']);
            $authorityData[0]['hp'] = Utility::getInstance()->updateDecryption($authorityData[0]['hp']);
            $authorityData[0]['email'] = Utility::getInstance()->updateDecryption($authorityData[0]['email']);

            $data['authority_data'] = $authorityData[0];
        } else {
            $formArray = [];

            // 폼 데이터 받기
            parse_str($params[0]['formData'], $formArray);
            $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

            if ($mode === 'list') {
                $loginLevl = $formArray['authority_type'];
                $formArray['authority_type'] = $levelType[$loginLevl];

                // 페이징정보
                $startPage = $params[0]['start_page']-1;
                $endPage = $params[0]['view_page_count'];
                if ($startPage < 1) {
                    $startPage = 0;
                } else {
                    $startPage = $startPage * $endPage;
                }

                // 권한관리 리스트 출력
                $rAuthorityQ = $this->emsQuery->getAutority($formArray, $startPage, $endPage);
                $authority = $this->query($rAuthorityQ);

                // 권한관리 카운트
                $rAuthorityCntQ = $this->emsQuery->getAuthorityCount($formArray);
                $authorityCount = $this->query($rAuthorityCntQ);

                $data['authority_data'] = [
                    'list'=> $this->getTransData($authority),
                    'count'=> $authorityCount[0]['cnt']
                ];
            } else if ($mode === 'update') {
                // 계정정보 추가
                $formArray['ss_pk'] = $ssPK;
                $formArray['admin_pk'] = $params[0]['admin_pk'];

                // 암호화
                $formArray['name'] = Utility::getInstance()->updateEncryption($formArray['name']);
                $formArray['email'] = Utility::getInstance()->updateEncryption($formArray['email']);
                $formArray['hp'] = Utility::getInstance()->updateEncryption($formArray['hp']);

                // 레벨 변환
                $formArray['login_level'] = $levelType[$formArray['login_level']];

                // 권한정보 수정
                $uAuthorityQ = $this->emsQuery->updateAuthority($formArray);
                $this->squery($uAuthorityQ);

                $data['isOverlap'] = $isOverlap;
            } else if ($mode === 'insert') {
                // 계정정보 추가
                $formArray['ss_pk'] = $ssPK;
                $formArray['admin_pk'] = $params[0]['admin_pk'];

                // 중복검사
                $rOverlapQ = $this->emsQuery->getIsOverlapAuthority($formArray['admin_id']);
                $rOverlapRslt = $this->query($rOverlapQ);

                if ($rOverlapRslt[0]['cnt'] > 0) {
                    $isOverlap = false;
                }

                $formLoginLevel = $formArray['login_level'];

                if ($isOverlap == true) {

                    $authorityData = $this->getAuthorityDataByLevel($formLoginLevel);
                    $password = $authorityData['password'];
                    $firstLoginDateQuery = $authorityData['first_login_date_query'];

                    // 암호화
                    $formArray['name'] = Utility::getInstance()->updateEncryption($formArray['name']);
                    $formArray['password'] = Utility::getInstance()->getPasswordHashValue($password);
                    $formArray['email'] = Utility::getInstance()->updateEncryption($formArray['email']);
                    $formArray['hp'] = Utility::getInstance()->updateEncryption($formArray['hp']);
                    
                    // 레벨변환
                    $formArray['login_level'] = $levelType[$formLoginLevel];

                    // 추가
                    $cAuthorityQ = $this->emsQuery->insertAuthority($formArray, $firstLoginDateQuery);
                    $this->squery($cAuthorityQ);

                    $data['password'] = $password;
                }

                $data['isOverlap'] = $isOverlap;

            } else if ($mode === 'delete'){
                $pks = $params[0]['pk'];
                $complexCodePk = $params[0]['complex_code_pk'];

                $pkStr = '';
                for ($i = 0; $i < COUNT($pks); $i++) {
                    if ($pkStr == '') {
                        $pkStr = "" . $pks[$i] . "";
                    } else {
                        $pkStr .= "','" . $pks[$i] . "";
                    }
                }

                $rDeleteNotQ = $this->emsQuery->getIsDeleteAuthority($complexCodePk);
                $rDeleteRslt = $this->query($rDeleteNotQ);

                if ($rDeleteRslt[0]['cnt'] > 1) {

                    if ($rDeleteRslt[0]['cnt'] != COUNT($pks)) {
                        // 삭제
                        // 계정 권한이 1개 일 때는 삭제 안되도록 할 것
                        // 삭제 갯수와 선택갯수가 같지 않으면
                        $uAuthorityQ = $this->emsQuery->deleteAuthority($pkStr, $ssPK);
                        $this->squery($uAuthorityQ);

                        $isDelete = true;
                    }
                }

                $data['is_delete'] = $isDelete;
            }
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
            $adminName = $data[$fcIndex]['admin_name'];
            $fcData[$fcIndex]['admin_name'] = Utility::getInstance()->updateDecryption($adminName);

            $name = $data[$fcIndex]['complex_name'];
            $fcData[$fcIndex]['complex_name'] = Utility::getInstance()->updateDecryption($name);

            $hp = $data[$fcIndex]['hp'];
            $fcData[$fcIndex]['hp'] = Utility::getInstance()->updateDecryption($hp);

            $email = $data[$fcIndex]['email'];
            $fcData[$fcIndex]['email'] = Utility::getInstance()->updateDecryption($email);
        }

        return $fcData;
    }

    /**
     * 권한에 따른 권한 설정  정보 반환
     *
     * @param string $loginLevel
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getAuthorityDataByLevel(string $loginLevel) : array
    {
        $devOptions = $this->devOptions;
        $loginLevelKeyData = Config::LOGIN_LEVEL_DATA['key'];

        $fcNewPassword = '';
        $fcFirstLoginQuery = '';
        $fcToday = date('Y-m-d');

        $loginLevel = $loginLevelKeyData[$loginLevel];
        if (empty($loginLevel) === true) {
            return $fcNewPassword;
        }

        $fcNewPassword = Utility::getInstance()->getSecretKey(Config::NORMAL_PASSWORD_LENGTH);

        if ($loginLevel === 100) {
            $fcNewPassword = $devOptions['DEFAULT_PASSWORD'];
            if (empty($fcNewPassword) === true) {
                $fcNewPassword = Config::DEFAULT_PASSWORD;
            }

            $fcFirstLoginQuery = " , first_login_date = '{$fcToday}'";
        }

        return [
            'password' => $fcNewPassword,
            'first_login_date_query' => $fcFirstLoginQuery,
        ];
    }
}