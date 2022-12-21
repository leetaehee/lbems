<?php
namespace EMS_Modules;

use EMS_Module\Config;
use EMS_Module\EMSQuery;
use EMS_Module\Utility;
use EMS_Module\JWT;

use Database\DbModule;

/**
 * ApiManager
 */
class ApiManager
{
    /** @var string $successSignal 응답 성공 시 메시지 */
    private string $successSignal = 'ok';

    /** @var string $failSignal 응답 실패 시 메시지 */
    private string $failSignal = 'nok';

    /** @var string|null $cookieName 쿠키 이름 */
    private ?string $cookieName = null;

    /** @var int $tokenDateType 토큰 주기 */
    private int $tokenDateType = Config::TOKEN_DATE_TYPE;

    /** @var int $expireTime 만료시간  */
    private int $expireTime;

    /** @var string|null 쿠키경로 */
    private ?string $path = null;

    /** @var string|null 도메인 */
    private ?string $domain = null;

    /** @var string|null $secretKey  비밀키 */
    private ?string $secretKey = null;

    /** @var array $apiRules  도메인, method 등 정보 */
    private array $apiRules;

    /** @var bool $secure 보안 설정 http, https */
    private bool $secure = false;

    /** @var bool $httpOnly 프론트에서 쿠키 탈최 적용 여부 */
    private bool $httpOnly = true;

    /** @var string|null $target  API 적용 대상 */
    private ?string $target = null;

    /** @var JWT|null JWT 싱클톤 객체  */
    private ?JWT $jwtSingleton = null;

    /** @var EMSQuery|null $emsQuery 쿼리 객체  */
    private ?EMSQuery $emsQuery = null;

    /** @var DbModule|null $db DB 객체 */
    private ?DbModule $db = null;

    /**
     * ApiManager Constructor.
     *
     * @param string $cookieName
     * @param string $path
     * @param string $domain
     * @param string $secretKey
     * @param array $apiRules
     */
    public function __construct(string $cookieName, string $path, string $domain, string $secretKey, array $apiRules)
    {
        $this->cookieName = $cookieName;
        $this->path = $path;
        $this->domain = $domain;
        $this->secretKey = $secretKey;
        $this->apiRules = $apiRules;
        $this->expireTime = strtotime("+{$this->tokenDateType} days");

        $this->jwtSingleton = JWT::getInstance();
        $this->emsQuery = new EMSQuery();
        $this->db = new DbModule();

        if (isset($_SERVER['REQUEST_SCHEME']) === true
            && $_SERVER['REQUEST_SCHEME'] === 'https') {
            // http, https 에 대해 처리..
            $this->secure = true;
        }
    }

    /**
     * ApiManager Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * apiRules 배열에서 해당 타입 조회
     *
     * @param string $type
     *
     * @return string
     */
    public function getApiRuleData(string $type) : string
    {
        $result = '';

        $apiRules = $this->apiRules;
        $scriptName = $_SERVER['SCRIPT_NAME'];

        $result = $apiRules[$scriptName][$type];

        if (empty($result) === true) {
            return '';
        }

        return $result;
    }

    /**
     * api 상태 체크
     *
     * @return array
     */
    public function checkStatusData() : array
    {
        $fcData = [
            'result' => $this->successSignal,
        ];

        $contentType  = $_SERVER['CONTENT_TYPE'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        //$requestURI = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];

        $choiceMethod = $this->getApiRuleData('method');
        $contentTypeRule = $this->getApiRuleData('content-type');
        $isMenuOpen =  $this->apiRules[$scriptName]['is_menu_open'];

        if ($isMenuOpen === false) {
            $fcData['result'] = $this->failSignal;
            $fcData['reason'] = ErrApiFunctionOpen;
            return $fcData;
        }

        if ($requestMethod !== $choiceMethod) {
            $fcData['result'] = $this->failSignal;
            $fcData['reason'] = ErrAPIStandard;
            return $fcData;
        }

        if ($requestMethod === 'POST'
            && $contentType !== $contentTypeRule) {
            $fcData['result'] = $this->failSignal;
            $fcData['reason'] = ErrContentTypePostRule;
            return $fcData;
        }

        return $fcData;
    }

    /**
     * 로그인 처리
     *
     * @param string $id
     * @param string $password
     *
     * @return array
     */
    public function loginProcess(string $id, string $password) : array
    {
        $fcData = [
            'result' => 'nok',
            'reason' => '',
        ];

        $isSuccess = 'n';

        $utilityInstance = Utility::getInstance();
        //$target = $this->target;

        $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) === true ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $ipAddress = $utilityInstance->updateEncryption($ipAddress);
        $browser = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $rLoginQ = $this->emsQuery->getQuerySelectApiLogin($id);
        $this->db->querys($rLoginQ);

        $logins = $this->db->getData();

        $dataCount = count($logins);

        if ($dataCount === 0) {
            $fcData['reason'] = ErrAccount;
            return $fcData;
        }

        $dbId = $logins[0]['id'];
        $dbTarget = $logins[0]['target'];
        $dbPassword = $logins[0]['password'];
        $accessToken = $logins[0]['access_token'];
        $refreshToken = $logins[0]['refresh_token'];

        $addOptions = [
            'secret_key' => $logins[0]['client_key'],
            'iv_key' => $logins[0]['iv_key']
        ];

        $id = $utilityInstance->updateDecryption($id, $addOptions);
        $password = $utilityInstance->updateDecryption($password, $addOptions);

        if (empty($id) === true) {
            $fcData['reason'] = ErrAccount;
            return $fcData;
        }

        if ($id !== $dbId) {
            $fcData['reason'] = ErrAccount;
            return $fcData;
        }

        $passwordResult = $utilityInstance->getPasswordVerifyResult($password, $dbPassword);
        if ($passwordResult === false) {
            $fcData['reason'] = ErrPassword;
        }

        if (empty($accessToken) === false && empty($refreshToken) === false) {
            header('Authorization: ' . $accessToken);
            return [
                'result' => 'ok',
                'reason' => loginSuccessMessage,
            ];
        }

        $tokenData = $this->getToken($id);
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'];

        if (empty($accessToken) === true
            || empty($refreshToken) === true) {
            $fcData['reason'] = ErrTokenAuthorization;
            header('HTTP/1.0 401 Unauthorized');
            return $fcData;
        }

        if (empty($fcData['reason']) === true) {
            $fcData['result'] = 'ok';
            $fcData['reason'] = loginSuccessMessage;

            $isSuccess = 'y';
        }

        $deviceColumn = 'fg_api';

        // 로그인 시각 남기기
        $uLoginLogQ = $this->emsQuery->getQueryUpdateApiLoginDate($dbTarget, $id);
        $this->db->squery($uLoginLogQ);

        // 로그인 로그 남기기
        $uLoginLogQ = $this->emsQuery->getQueryLoginLog($id, $ipAddress, $browser, $deviceColumn, $isSuccess);
        $this->db->squery($uLoginLogQ);

        // 토큰 저장하기
        $uJwtTokenQ = $this->emsQuery->getQueryUpdateJwtInfo($dbTarget, $id, $accessToken, $refreshToken);
        $this->db->squery($uJwtTokenQ);

        // 인증 헤더 추가
        header('Authorization: ' . $accessToken);

        return $fcData;
    }

    /**
     * 토큰 조회
     *
     * @param string $id
     *
     * @return array
     */
    private function getToken(string $id) : array
    {
        $today = date('YmdHis');

        $fcData = [
            'access_token' => '',
            'refresh_token' => '',
        ];

        $payloads = [
            'make_date' => strtotime($today),
            'expire_date' => '',
            'id' => $id,
        ];

        $jwtSingleton = $this->jwtSingleton;
        $secretKey = $this->secretKey;

        // access_token 발급
        $fcData['access_token'] = $jwtSingleton->makeToken($secretKey, 'access_token', $payloads);

        // refresh_token 발급
        $fcData['refresh_token'] = $jwtSingleton->makeToken($secretKey, 'refresh_token', $payloads);

        return $fcData;
    }

    /**
     * 요청 데이터 받아오기
     *
     * @return array
     */
    public function requestData() : array
    {
        $jsonData = json_decode(file_get_contents('php://input'), true);

        if (is_null($jsonData) === true) {
            $jsonData = [];
        }

        if (count($jsonData) === 0) {
            $jsonData = [];
        }

        return $jsonData;
    }

    /**
     * post 데이터 받아오기
     *
     * @return array
     */
    public function postData() : array
    {
        $postData = $_POST;

        if (is_null($postData) === true) {
            $postData = [];
        }

        return $postData;
    }

    /**
     * 쿠키 등록
     *
     * @param string $cookieValue
     */
    public function addCookie(string $cookieValue) : void
    {
        $cookieName = $this->cookieName;
        $expireTime = $this->expireTime;
        $path = $this->path;
        $domain = $this->domain;
        $secure = $this->secure;
        $httpOnly = $this->httpOnly;

        // 등록된 쿠키가 없으면 추가한다.
        /*
            if (isset($_COOKIE[$cookieName]) === false) {
                setcookie($cookieName, $cookieValue, $expireTime, $path, $domain, $secure, $httpOnly);
            }
        */

        // 이미 등록 된 쿠기가 있는 경우 연장
        setcookie($cookieName, $cookieValue, $expireTime, $path, $domain, $secure, $httpOnly);
    }

    /**
     * 쿠키 삭제
     */
    public function deleteCookie() : void
    {
        $today = date('Ymd');
        $cookieName = $this->cookieName;

        if (isset($_COOKIE[$cookieName]) === true) {
            setCookie($cookieName, '', strtotime($today . '-1 years'));
        }
    }

    /**
     *  적용대상 변경
     *
     * @param string $target
     */
    public function setTarget(string $target) : void
    {
        $this->target = $target;
    }

    /**
     * API 호출 시 로그 저장
     */
    public function saveApiCallLog() : void
    {
        $headers = getallheaders();
        $apiRules = $this->apiRules;

        $logDate = date('YmdHis');

        $httpForwardedIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        $contentType = $headers['Content-Type'] ?? '';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $httpUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $requestSchema = $_SERVER['REQUEST_SCHEME'] ?? '';
        //$requestURI = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        $type = $apiRules[$scriptName]['type'];
        $ip = empty($httpForwardedIp) === false ? $httpForwardedIp : $remoteAddr;

        $parameter = ($requestMethod === 'POST') ? $this->postData() : $_GET;

        if (count($parameter) >= 0) {
            $parameter = (string)json_encode($parameter);
        }

        $requestInfo = json_encode([
            'content_type' => $contentType,
            'request_method' => $requestMethod,
            'http_user_agent' => $httpUserAgent,
            'request_schema' => $requestSchema,
            'remote_addr' => $remoteAddr,
            'http_x_forwarded_for' => $httpForwardedIp,
            //'request_uri' => $requestURI,
            //'script_name' => $scriptName,
        ]);

        $cApiCallQ = $this->emsQuery->getQueryInsertApiCallLog($logDate, $ip, $type,  $scriptName, $parameter, $requestInfo);
        $this->db->squery($cApiCallQ);
    }
}