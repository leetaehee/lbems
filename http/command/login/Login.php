<?php
namespace Http\Command;

use Module\Session;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class Login 로그인
 */
class Login extends Command
{
    /** @var string $connectionMethodPrefix 현재 사용자의 접속 방식 Prefix */
    private string $connectionMethodPrefix = '';

	/**
	 * Login constructor.
	 */
	public function __construct()
	{
		parent::__construct();

        $this->connectionMethodPrefix = Utility::getInstance()->getConnectionMethodPrefix();
	}

	/**
	 * Login destructor.
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

    /**
     * 메인 실행 함수
     *
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
	    $isUseAutoLogin = false;

		$session = new Session();
		$session->clearSessionData();

		$ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) === true ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $ipAddress = Utility::getInstance()->updateEncryption($ipAddress);
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$id = isset($params[0]['value']) == true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
		$password = isset($params[1]['value']) == true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';
		$deviceKey = isset($params[2]['value']) == true ? $params[2]['value'] : null;
		$autoLogin = isset($params[3]['value']) == true ? $params[3]['value'] : 'n';
		$loginKey = isset($params[4]['value']) == true ? $params[4]['value'] : '';

		// 자동로그인 체크 여부
        $autoResult = $this->getAutoLoginStatus($autoLogin, $id, $password, $deviceKey, $loginKey);
        if ($autoLogin === true && $autoResult['is_success'] === false) {
            $this->data['is_success'] = $autoResult['is_success'];
            return true;
        }

        $isSuccess = $autoResult['is_success'];
        if (isset($autoResult['password']) === true && $isSuccess === true) {
            $isUseAutoLogin = true;
        }

        // 로그인정보 조회
        $loginInfo = $this->getLoginInfo($id, $loginKey, $isSuccess);
        $logins = $loginInfo['logins'];
        $loginKey = $loginInfo['login_key'];

        // 로그인 정보에 대한 유효성 검증 후 로그 남기기
        $validates = $this->getLoginValidate($id, $password, $ipAddress, $browser, $logins, $isUseAutoLogin);
        if (count($validates) > 0) {
            $this->data = $validates;
            return true;
        }

        // 세션 처리
        $result = $this->updateLogin($session, $password, $loginKey, $deviceKey, $ipAddress, $autoLogin, $logins);
        if (count($result) === 0) {
            $this->data['dashboard_error'] = true;
            return true;
        }

        $data = [
            'login_key' => $loginKey,
            'result' => $result,
        ];

        $this->data = $data;
		return true;
	}

    /**
     * 자동로그인 결과 조회
     *
     * @param bool $autoLogin
     * @param string $id
     * @param string $password
     * @param string $deviceKey
     * @param string $loginKey
     *
     * @return array
     *
     * @throws \Exception
     */
	private function getAutoLoginStatus(bool $autoLogin, string $id, string $password, string $deviceKey, string $loginKey) : array
    {
        $fcData = [];
        $fcResult = [
            'is_success' => true,
        ];

        if ($autoLogin === false || empty($password) === false) {
            // 자동로그인을 새로 갱신하거나, 자동로그인을 사용자가 선택하지 않은 경우..
            $fcResult['is_success'] = count($fcData);
            return $fcResult;
        }

        // 이미 자동로그인이 되어있는 경우 데이터 검증 시도
        $rAutoLoginQ = $this->emsQuery->getQueryAutoLogin($id, $deviceKey, $loginKey);
        $result = $this->query($rAutoLoginQ);

        $isSuccess = count($result) > 0;

        if (empty($password) === true && $isSuccess === false) {
            // 자동로그인 내역이 없고 비밀번호 입력정보가 없는 경우 오류 처리함.
            $fcResult['is_success'] = false;
            return $fcResult;
        }

        $fcResult['is_success'] = $isSuccess;
        $fcResult['password'] = $result[0]['password'];

        return $fcResult;
    }

    /**
     * 로그인 정보 조회
     *
     * @param string $id
     * @param string $loginKey
     * @param bool $isSuccess
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getLoginInfo(string $id, string $loginKey, bool $isSuccess) : array
    {
        $rLoginQ = $this->emsQuery->getQueryLogin($id);
        $loginData = $this->query($rLoginQ);

        if ($isSuccess === false) {
            $loginKey = uniqid() .'-'. uniqid();
        }

        return [
            'logins' => $loginData,
            'login_key' => $loginKey,
        ];
    }

    /**
     * 로그인 데이터 검증
     *
     * @param string $id
     * @param string $password
     * @param string $ip
     * @param string $browser
     * @param array $loginData
     * @param bool $isUseAutoLogin
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getLoginValidate(string $id, string $password, string $ip, string $browser, array $loginData, bool $isUseAutoLogin) : array
    {
        $fcData = [];

        $dataCount = count($loginData);

        $loginBlocking = false;
        $fcIsSuccess = true;

        $connectionMethodPrefix = $this->connectionMethodPrefix;

        if ($dataCount === 0) {
            // 존재하지 않는 아이디 체크
            $fcData['id_error'] = true;
            return $fcData;
        }

        if ($dataCount > 0) {
            $pk = $loginData[0]['admin_pk'];
            $id = $loginData[0]['admin_id'];
            $dbPassword = $loginData[0]['password'];
            $name = $loginData[0]['name'];
            $complexCodePk = $loginData[0]['complex_code_pk'];
            $level = $loginData[0]['login_level'];
            $loginFailCnt = (int)$loginData[0]['login_fail_cnt'];
            $loginFailLimitCount = Config::LOGIN_FAIL_LIMIT_COUNT;
            $fgMobile = $loginData[0]['fg_mobile'];
            $firstLoginDate = empty($loginData[0]['first_login_date']) === true ? '' : $loginData[0]['first_login_date'];

            if (in_array($complexCodePk, Config::FACTORY_USE_GROUP) === true
                && empty($connectionMethodPrefix) === false) {
                $fcData['factory'] = true;
                return $fcData;
            }

            if (empty($connectionMethodPrefix) === false
                && $fgMobile === 'n') {
                $fcData['not_access'] = true;
                return $fcData;
            }

            $isLogin = $loginFailCnt === $loginFailLimitCount ? true : false;
            if ($isLogin === true) {
                // 비밀번호 제한..
                $fcData['login_blocking'] = $isLogin;
            }

            if (Utility::getInstance()->getPasswordVerifyResult($password, $dbPassword) === false
                && $isUseAutoLogin === false
                && $isLogin === false) {

                $loginFailCnt += 1;

                if ($loginFailCnt <= $loginFailLimitCount
                    && empty($firstLoginDate) === false) {
                    // 비밀번호 틀린경우 횟수 증가
                    $uLoginFailCntQ = $this->emsQuery->getQueryUpdateLoginFailCnt($pk);
                    $this->squery($uLoginFailCntQ);
                }

                $fcData = [
                    'password_error' => true,
                ];
            }

            $isFirstLogin = $this->getIsFirstLogin($pk, $firstLoginDate);
            if (isset($fcData['password_error']) === false
                && $isFirstLogin === true) {
                $fcData['first_login'] = true;
                return $fcData;
            }

            if ($name === null || $name === '' || $level === null || $level === '') {
                $fcData['empty'] = true;
            }
        }

        $fcIsSuccess = count($fcData) > 0 ? 'n' : 'y';

        // 모바일 분기처리
        $deviceColumn = empty($connectionMethodPrefix) === false ? 'fg_mobile' : 'fg_login';

        // 로그인 로그 저장
        $uLoginLogQ = $this->emsQuery->getQueryLoginLog($id, $ip, $browser, $deviceColumn, $fcIsSuccess);
        $this->squery($uLoginLogQ);

        return $fcData;
    }

    /**
     * 로그인
     *
     * @param Session $session
     * @param string $password
     * @param string $loginKey
     * @param string $deviceKey
     * @param string $ip
     * @param bool $autoLogin
     * @param array $loginData
     *
     * @return array
     *
     * @throws \Exception
     */
    private function updateLogin(Session $session, string $password, string $loginKey, string $deviceKey, string $ip, bool $autoLogin, array $loginData) : array
    {
        $connectionMethodPrefix = $this->connectionMethodPrefix;

        $fcData = [];
        $fcLoginKey = $loginKey;

        $pk = $loginData[0]['admin_pk'];
        $id = $loginData[0]['admin_id'];
        $name = $loginData[0]['name'];
        $level = $loginData[0]['login_level'];
        $complexCodePk = $loginData[0]['complex_code_pk'];
        $phone = $loginData[0]['phone'];
        //$password = $loginData[0]['password'];

        $dashboardData = $this->getDashboardInfo($complexCodePk, $id);
        if (count($dashboardData) === 0
            && empty($connectionMethodPrefix) === true) {
            return $fcData;
        }

        $fcData = $dashboardData;

        // 로그인
        $sessions = [
            'id' => $id,
            'password' => $password,
            'pk' => $pk,
            'name' => $name,
            'level' => $level,
            'complex_code_pk' => $complexCodePk,
            'ip' => Utility::getInstance()->updateDecryption($ip),
            'phone' => $phone,
            'device_key' => $deviceKey,
            'login_key' => $fcLoginKey,
        ];

        $session->setSession($sessions);

        // 최근접속일 변경
        $uLastLoginDateQ = $this->emsQuery->getQueryUpdateLastLogin($pk);
        $this->squery($uLastLoginDateQ);

        if ($deviceKey !== null && strlen($deviceKey) > 1 && $autoLogin === true) {

            // 모바일 분기처리
            $fgMobileWhere = empty($connectionMethodPrefix) === false ? ",`fg_mobile` = 'y'" : '';

            $cAutoLoginQ = $this->emsQuery->getQueryUpdateAutologin($pk, $deviceKey, $loginKey, $fgMobileWhere);
            $this->squery($cAutoLoginQ);
        }

        return $fcData;
    }

    /**
     * 사이트에 처음 접속하는 사용자인지 체크
     *
     * @param int $adminPk
     * @param string $firstLoginDate
     *
     * @return bool
     */
    private function getIsFirstLogin(int $adminPk, string $firstLoginDate) : bool
    {
        $connectionMethodPrefix = $this->connectionMethodPrefix;

        if (empty($connectionMethodPrefix) === false) {
            // 모바일 체크
            if (empty($firstLoginDate) === true) {
                return true;
            }

            return false;
        }

        if (empty($firstLoginDate) === true) {
            // 웹 체크
            // 사용자가 사이트에 처음 접속 할 경우 기본 비밀번호를 변경하도록 요구함.
            // 인증번호 세션으로 등록, 비밀번호가 변경완료 될 시 세션 파괴
            $_SESSION['tmp_session'] = [
                'ss_admin_pk' => Utility::getInstance()->updateEncryption($adminPk),
                'first_login_date' => $firstLoginDate,
            ];

            return true;
        }

        return false;
    }

    /**
     * 웹 대시보드에서  url 정보 조회
     *
     * @param string $complexCodePk
     * @param string $id
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getDashboardInfo(string $complexCodePk, string $id) : array
    {
        $fcData = [];

        $complexMenuDashboardTypes = Config::COMPLEX_MENU_DASHBOARD_TYPES;
        $userMenuDashboardTypes = Config::USER_MENU_DASHBOARD_TYPES;

        $connectionMethodPrefix = $this->connectionMethodPrefix;
        if (empty($connectionMethodPrefix) === false) {
            return $fcData;
        }

        $rDashboardQ = $this->emsQuery->getDashboardList($complexCodePk);

        $menuType = $complexMenuDashboardTypes[$complexCodePk];
        if (empty($complexMenuDashboardTypes[$complexCodePk]) === false) {
            // 단지별 대시보드 메뉴가 없는 경우  찾아서 보여주기
            $rDashboardQ = $this->emsQuery->getDashboardList($complexCodePk, $menuType);
        }

        $menuType = $userMenuDashboardTypes[$id];
        if (empty($userMenuDashboardTypes[$id]) === false) {
            // 유저별 대시보드 메뉴가 없는 경우 찾아서 보여주기
            $rDashboardQ = $this->emsQuery->getDashboardList($complexCodePk, $menuType);
        }

        $rDashboardData = $this->query($rDashboardQ);
        if (count($rDashboardData) === 0) {
            return $fcData;
        }

        $fcData = $rDashboardData[0];

        $_SESSION['tmp'] = [
            $complexCodePk => [
                'dashboard_info' => $fcData
            ],
        ];

        return $fcData;
    }
}