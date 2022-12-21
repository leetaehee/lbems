<?php
namespace Http\Command;

use EMS_Module\Utility;

use Module\Session;

/**
 * Class ChangePassword 비밀번호 변경
 */
class ChangePassword extends Command
{
    /** @var array|$rules 비밀번호 유효성 규칙 */
    private array $rules = [
        'rule_1' => '#[a-zA-z]#', // 대, 소문자
        'rule_2' => '#[0-9]#', // 숫자
        'rule_3' => '#[\W]#', // 특수문자
    ];

    /**
     * ChangePassword Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ChangePassword Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $adminPk = $_SESSION['ss_pk'];

        $data = [
            'error' => 'success',
            'data' => [],
        ];

        $oldPassword = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $newPassword = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $rePassword = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : '';
        $isSession = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : false;

        $validateResult = $this->getPasswordValidateCheck($newPassword, $rePassword, $oldPassword);
        if ($validateResult === false) {
            // 패스워드 정규식 확인해볼것..
            $data['error'] = 'password_wrong';
            $this->data = $data;
            return true;
        }

        if ($isSession === false) {
            $adminPk = Utility::getInstance()->updateDecryption($_SESSION['tmp_session']['ss_admin_pk']);
        }

        $updateResult = $this->updatePassword($adminPk, $oldPassword, $newPassword, $isSession);

        if ($updateResult['error_type'] !== 'success') {
            $data['error'] = $updateResult['error_type'];
        }

        $this->data = $data;
        return true;
    }

    /**
     * 패스워드 규칙 유효성 검증
     *
     * @param string $newPassword
     * @param string $rePassword
     * @param string $oldPassword
     *
     * @return bool
     */
    private function getPasswordValidateCheck(string $newPassword, string $rePassword, string $oldPassword) : bool
    {
        $result = true;
        $rules = $this->rules;

        if ($newPassword !== $rePassword) {
            $result = false;
        }

        if (empty($oldPassword) === true && $result === true) {
            // 로그인 하지 않았을 때..
            if (isset($_SESSION['tmp_session']['ss_admin_pk']) === false) {
                return false;
            }
        }

        if (empty($oldPassword) === false && $result === true) {
            // 로그인 했을 때
            if (empty($oldPassword) === true) {
                $result = false;
            }
        }

        if (empty($newPassword) === true && $result === true) {
            $result = false;
        }

        if (empty($rePassword) === true && $result === true) {
            $result = false;
        }

        if ($result === true) {
            // 패스워드 정책 유효성 검사
            $rule1Result = preg_match($rules['rule_1'], $newPassword);
            $rule2Result = preg_match($rules['rule_2'], $newPassword);
            $rule3Result = preg_match($rules['rule_3'], $newPassword);
            $strLen = strlen($newPassword);

            $length  = ($strLen>= 8 && $strLen <= 15);

            if (!$rule1Result || !$rule2Result || !$rule3Result || !$length) {
                return false;
            }

        }

        return $result;
    }

    /**
     * 패스워드 변경
     *
     * @param int $adminPk
     * @param string $oldPassword
     * @param string $newPassword
     * @param bool $isSession
     *
     * @return array
     *
     * @throws \Exception
     */
    public function updatePassword(int $adminPk, string $oldPassword, string $newPassword, bool $isSession) : array
    {
        /**
         * success: 정상,
         * fail: db 핸들링 실패,
         * same_password: 기존과 동일한 패스워드
         * not_old_password: 기존패스워드와 입력한 기존 패스워드가 일치 하지 않을 때
         */
        $fcData['error_type'] = 'success';

        $ssFirstLoginDate = $_SESSION['tmp_session']['first_login_date'];

        $rPasswordQ = $this->emsQuery->getQuerySelectPassword($adminPk);
        $passwordData = $this->query($rPasswordQ);

        $dbOldPassword = $passwordData[0]['password'];
        $dbNewPassword = Utility::getInstance()->getPasswordHashValue($newPassword);

        $pwdCompareResult = Utility::getInstance()->getPasswordVerifyResult($oldPassword, $dbOldPassword);
        if (empty($oldPassword) === false && $pwdCompareResult === false) {
            $fcData['error_type'] = 'not_old_password';
            return $fcData;
        }

        $newOldPwdCompareResult = Utility::getInstance()->getPasswordVerifyResult($newPassword, $dbOldPassword);
        if ($newOldPwdCompareResult === true) {
            $fcData['error_type'] = 'same_password';
            return $fcData;
        }

        if ($isSession === false && empty($oldPassword) === true) {
            $authValidTime = $passwordData[0]['auth_valid_time'];
            $after5MinuteTime = date('YmdHis', strtotime($authValidTime. '+5 minutes'));
            $nowDateTime = date('YmdHis');

            if ($nowDateTime > $after5MinuteTime) {
                // 인증번호 요청 시간 체크
                $fcData['error_type'] = 'late_time';
                return $fcData;
            }
        }

        if ($isSession === false && empty($ssFirstLoginDate) === true) {
            // 기본 비밀번호를 변경하였으므로 상태 변경 처리.
            $uFirstLoginDateQ = $this->emsQuery->getQueryUpdateFirstLoginDate($adminPk);
            $this->squery($uFirstLoginDateQ);
        }

        if (empty($ssFirstLoginDate) === true) {
            // 인증정보와 로그인 실패 횟수 초기화
            $uInitializeQ = $this->emsQuery->getQueryUpdateAccountDataInitialize($adminPk);
            $this->squery($uInitializeQ);
        }

        if ($isSession === false) {
            unset($_SESSION['tmp_session']); // 세션정보 삭제
        }

        // 로그인 변경
        $uPasswordQ = $this->emsQuery->getQueryUpdatePassword($adminPk, $dbNewPassword);
        $this->squery($uPasswordQ);

        return $fcData;
    }
}