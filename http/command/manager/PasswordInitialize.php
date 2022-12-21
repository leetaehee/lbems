<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class PasswordInitialize  관리자가 비밀번호 초기화 하는 기능
 */
class PasswordInitialize extends Command
{
    /**
     * PasswordInitialize constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * PasswordInitialize destructor.
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
    public function execute(array $params): ?bool
    {
        $adminPk = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        //$loginLevel = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';

        $newPassword = $this->makeInitializePassword($adminPk);
        if (empty($newPassword) === true) {
            $this->data['password'] = $newPassword;
            return true;
        }

        // 비밀번호 변경
        $encryptionPw = Utility::getInstance()->getPasswordHashValue($newPassword);

        $uPasswordQ = $this->emsQuery->getQueryUpdatePassword($adminPk, $encryptionPw);
        $this->squery($uPasswordQ);

        $this->data['password'] = $newPassword;
        return true;
    }

    /**
     * 초기화 패스워드 생성
     *
     * @param int $adminPk
     *
     * @return string
     *
     * @throws \Exception
     */
    private function makeInitializePassword(int $adminPk) : string
    {
        $devOptions = $this->devOptions;

        $fcNewPassword = '';

        $rLoginLevelQ = $this->emsQuery->getAdminLoginLevel($adminPk);
        $rLoginLevels = $this->query($rLoginLevelQ);

        if (count($rLoginLevels) === 0) {
            return $fcNewPassword;
        }

        $loginLevel = (int)$rLoginLevels[0]['login_level'];

        $fcNewPassword = $devOptions['DEFAULT_PASSWORD'];
        if (empty($fcNewPassword) === true) {
            $fcNewPassword = Config::DEFAULT_PASSWORD;
        }

        if ($loginLevel < 100) {
            $fcNewPassword = Utility::getInstance()->getSecretKey(Config::NORMAL_PASSWORD_LENGTH);
        }

        return $fcNewPassword;
    }
}