<?php
namespace Module;

use EMS_Module\EMSQuery;
use EMS_Module\Utility;

use Database\DbModule;

/**
 * Class Session
 */
class Session
{
    /** @var null|EmsQuery $emsQuery */
    private ?EMSQuery $emsQuery = null;

    /** @var null|DbModule $db */
    private ?DbModule $db = null;

    /** @var string $connectionMethodPrefix 현재 사용자의 접속 방식 Prefix */
    private string $connectionMethodPrefix = '';

    /**
     * Session constructor.
     */
	public function __construct()
	{
	    if (isset($_SESSION) === false) {
	        $this->startSession();
        }

	    $this->emsQuery = new EMSQuery();
	    $this->db = new DbModule();

        $this->connectionMethodPrefix = Utility::getInstance()->getConnectionMethodPrefix();
	}

    /**
     * 세션 시작
     */
	public function startSession() : void
	{
		session_start();
	}

    /**
     * 세션 삭제
     */
	public function destroySession() : void
	{
		session_destroy();
	}

    /**
     * 세션 설정
     * 
     * @param array $sessions
     */
	public function setSession(array $sessions) : void
	{
		$this->clearSessionData();
        $prefix = $this->connectionMethodPrefix;

		$_SESSION[$prefix . 'ss_id'] = $sessions['id'];
		$_SESSION[$prefix . 'ss_password'] = $sessions['password'];
		$_SESSION[$prefix . 'ss_pk'] = $sessions['pk'];
		$_SESSION[$prefix . 'ss_name'] = $sessions['name'];
		$_SESSION[$prefix . 'ss_level'] = $sessions['level'];
		$_SESSION[$prefix . 'ss_ip'] = $sessions['ip'];
		$_SESSION[$prefix . 'ss_complex_pk'] = $sessions['complex_code_pk'];
		$_SESSION[$prefix . 'ss_phone'] = $sessions['phone'];
		$_SESSION[$prefix . 'ss_device_key'] = $sessions['device_key'];
		$_SESSION[$prefix . 'ss_login_key'] = $sessions['login_key'];
	}

    /**
     * 세션 초기화
     */
	public function clearSessionData() :void
    {
        $prefix = $this->connectionMethodPrefix;

        $ss_complex_pk = $_SESSION[$prefix . 'ss_complex_pk'];

        if (empty($prefix) === true) {
            unset($_SESSION['tmp'][$ss_complex_pk]);
        }

		unset($_SESSION[$prefix . 'ss_id']);
		unset($_SESSION[$prefix . 'ss_password']);
		unset($_SESSION[$prefix . 'ss_pk']);
		unset($_SESSION[$prefix . 'ss_name']);
		unset($_SESSION[$prefix . 'ss_level']);
		unset($_SESSION[$prefix . 'ss_ip']);
		unset($_SESSION[$prefix . 'ss_phone']);
		unset($_SESSION[$prefix . 'ss_device_key']);
		unset($_SESSION[$prefix . 'ss_login_key']);
		unset($_SESSION[$prefix . 'ss_complex_pk']);
	}

    /**
     * 로그인 여부 조회
     *
     * @param bool|false $ipCheck
     *
     * @return bool
     */
	public function isLogin(bool $ipCheck) : bool
	{
        $prefix = $this->connectionMethodPrefix;

        $ss_id = $_SESSION[$prefix . 'ss_id'];
        $ss_name = $_SESSION[$prefix . 'ss_name'];
        $ss_level = $_SESSION[$prefix . 'ss_level'];
        $ss_ip = $_SESSION[$prefix . 'ss_ip'];
        $ss_pk = $_SESSION[$prefix . 'ss_pk'];
        $ss_device_key = $_SESSION[$prefix . 'ss_device_key'];
        $ss_login_key = $_SESSION[$prefix . 'ss_login_key'];

		if (isset($ss_id) === true && isset($ss_name) === true && isset($ss_level) === true && isset($ss_ip) === true) {
		    if (strlen($ss_id) <= 0 || strlen($ss_name) <= 0 || strlen($ss_level) <= 0 || strlen($ss_ip) <= 0) {
		        return false;
			}

			if ($this->isChangeMember() === false) {
			    // 회원정보 변경 이력이 있는 경우 자동로그인 해제
                $uAutoLoginQ = $this->emsQuery->getQueryUpdateAutologinOff($ss_pk, $ss_device_key, $ss_login_key);
                $this->db->squery($uAutoLoginQ);

                $this->clearSessionData();
                return false;
            }

			if ($ipCheck === true && $ss_ip != $_SERVER['REMOTE_ADDR']) {
				return false;
			}

			return true;
		}

		return false;
	}

    /**
     * 회원 정보 변동사항이 있는지 체크
     *
     * @return bool
     */
	private function isChangeMember() : bool
    {
        $prefix = $this->connectionMethodPrefix;

        $ss_id = $_SESSION[$prefix . 'ss_id'];
        $ss_password = $_SESSION[$prefix . 'ss_password'];

        $rLoginQ = $this->emsQuery->getQueryLogin($ss_id);
        $this->db->querys($rLoginQ);

        $infos = $this->db->getData();
        if (count($infos) === 0) {
            return false;
        }

        if (empty($ss_password) === false
            &&  Utility::getInstance()->getPasswordVerifyResult($ss_password, $infos[0]['password']) === false) {
            return false;
        }

        return true;
    }
}