<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class ConfirmAuthInfo 인증번호 받아서 처리 확인하는 함수
 */
class ConfirmAuthInfo extends Command
{
    /**
     * ConfirmAuthInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ConfirmAuthInfo destructor.
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
        $data = [
            'confirm_valid' => true,
        ];

        $name = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $email = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
        $authNumber = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : '';

        $validResult = $this->getAuthSuccessCheck($name, $email, $authNumber);
        if ($validResult['is_valid'] === false) {
            $data['confirm_valid'] = false;
            $this->data = $data;
            return true;
        }

        // 인증번호 세션으로 등록, 비밀번호가 변경완료 될 시 세션 파괴
        $_SESSION['tmp_session']['ss_admin_pk'] = Utility::getInstance()->updateEncryption($validResult['admin_pk']);

        $this->data = $data;
        return true;
    }

    /**
     * 비밀번호 인증정보 성공여부 체크
     *
     * @param string $name
     * @param string $email
     * @param string $authNumber
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getAuthSuccessCheck(string $name, string $email, string $authNumber) : array
    {
        $fcData = [
            'is_valid' => true,
            'admin_pk' => null,
        ];

        // 양방향 암호화
        $encryptName = Utility::getInstance()->updateEncryption($name);
        $encryptEmail = Utility::getInstance()->updateEncryption($email);

        $rAuthSuccessQ = $this->emsQuery->getQuerySelectAuthSuccessCheck($encryptName, $encryptEmail);
        $successData = $this->query($rAuthSuccessQ);

        if (count($successData) === 0) {
            // 인증정보가 없는 경우..
            $fcData['is_valid'] = false;
            return $fcData;
        } else {
            $encodeAuthNumber = Utility::getInstance()->updateDecryption($successData[0]['auth_num']);
            $nowDateTime = date('YmdHis');
            $authValidTime = $successData[0]['auth_valid_time'];
            $adminPk = $successData[0]['admin_pk'];
            $after5MinuteTime = date('YmdHis', strtotime($authValidTime. '+5 minutes'));

            if ($authNumber !== $encodeAuthNumber) {
                // 인증정보가 일치하지 않는 경우
                $fcData['is_valid'] = false;
                return $fcData;
            }

            if ($nowDateTime > $after5MinuteTime) {
                // 인증번호 시간 초과한 경우
                $fcData['is_valid'] = false;
                return $fcData;
            }
        }

        $fcData['admin_pk'] = $adminPk;

        return $fcData;
    }
}