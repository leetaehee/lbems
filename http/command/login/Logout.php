<?php
namespace Http\Command;

use EMS_Module\Utility;

use Module\Session;

/**
 * Class Logout 로그아웃
 */
class Logout extends Command
{
    /**
     * Logout constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Logout destructor.
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
     */
    public function execute(array $params) :? bool
    {
        $session = new Session();
        $prefix = Utility::getInstance()->getConnectionMethodPrefix();

        $deviceKey = $_SESSION[$prefix . 'ss_device_key'];
        $loginKey  = $_SESSION[$prefix . 'ss_login_key'];
        $pk = $_SESSION[$prefix . 'ss_pk'];
        $updQuery = $this->emsQuery->getQueryUpdateAutologinOff($pk, $deviceKey, $loginKey);
        $this->db->squery($updQuery);

        $session->clearSessionData();

        return true;
    }
}