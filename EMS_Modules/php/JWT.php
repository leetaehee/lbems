<?php
namespace EMS_Module;

use Database\DbModule;

/**
 * Class JWT
 */
class JWT
{
    /** @var JWT|null $instance 싱글톤 객체 */
    private static ?JWT $instance = null;

    /** @var string $authorizationType 인증타입 */
    private string $authorizationType = 'Bearer';

    /** @var string $algorithm 암호화 방식  */
    private string $algorithm = Config::JWT_MAKE_ALGORITHM;

    /** @var int $accessTokenDateType access token 주기 */
    private int $accessTokenDateType = Config::ACCESS_TOKEN_DATE_TYPE;

    /** @var int $refreshTokenDateType refresh token 주기 */
    private int $refreshTokenDateType = Config::REFRESH_TOKEN_DATE_TYPE;

    /** @var EMSQuery|null $emsQuery 쿼리 객체 */
    private ?EMSQuery $emsQuery = null;

    /** @var DbModule|null $db DB 모듈  */
    private ?DbModule $db = null;

    /**
     * JWT Construct.
     */
    private function __construct()
    {
        // 외부에서 접근 못하도록 막음
    }

    /**
     * 초기화
     */
    public function initialize() : void
    {
        $this->db = new DbModule();
        $this->emsQuery = new EMSQuery();
    }

    /**
     * 싱글톤 객체 생성
     *
     * @return JWT
     */
    public static function getInstance() : JWT
    {
        if (isset(self::$instance) === false) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 토큰 생성
     *
     * @param string $secretKey
     * @param string $tokenType
     * @param array $payloads
     *
     * @return string
     */
    public function makeToken(string $secretKey, string $tokenType, array $payloads = []) : string
    {
        $fcToken = '';

        if (empty($secretKey) === true) {
            return $fcToken;
        }

        if (count($payloads) === 0) {
            return $fcToken;
        }

        $tokenTime = $this->getTokenTime($tokenType);
        $payloads['expire_date'] = $tokenTime;

        $header = json_encode([
            'algorithm' => $this->algorithm,
            'type' => 'JWT'
        ]);

        $payLoad = json_encode($payloads);
        $signature = hash($this->algorithm, $header . $payLoad . $secretKey);

        if (empty($header) === false
            && empty($payLoad) === false
            && empty($signature) === false) {
            $fcToken = Utility::getInstance()->updateEncryption($header . '.'. $payLoad . '.' . $signature);
        }

        return $fcToken;
    }

    /**
     * 로그인 상태 확인
     *
     * @param string $secretKey
     *
     * @return array
     */
    public function getCheckLoginStatus(string $secretKey) : array
    {
        $fcData = [
            'result' => false,
            'reason' => '',
        ];

        $header = $this->getAuthorization();
        if (empty($header) === true) {
            $fcData['reason'] = ErrAuthorizationToken;
            return $fcData;
        }

        $payLoads = $this->getTokenPayLoad($header, $secretKey);
        if (count($payLoads) === 0) {
            $fcData['reason'] = ErrTokenAuthorization;
            header('HTTP/1.0 401 Unauthorized');
            return $fcData;
        }

        $id = $payLoads['id'];

        // 초기화
        $this->initialize();

        $rAccountStateQ = $this->emsQuery->getQuerySelectApiAccountState($id);
        $this->db->querys($rAccountStateQ);

        $accounts = $this->db->getData();

        $checkId = $accounts[0]['id'];
        $target = $accounts[0]['target'];
        $accessToken = $accounts[0]['access_token'];
        $refreshToken = $accounts[0]['refresh_token'];

        $fcData['reason'] = ErrTokenAuthorization;

        if (empty($accessToken) === true
            && empty($refreshToken) === true) {
            header('HTTP/1.0 401 Unauthorized');
            return $fcData;
        }

        if ($header !== $accessToken) {
            header('HTTP/1.0 401 Unauthorized');
            return $fcData;
        }

        if (empty($checkId) === true) {
            $fcData['reason'] = ErrLoginNotInfo;
            return $fcData;
        }

        $accessTokenTime = $this->getCheckToken($accessToken, $secretKey);
        $refreshTokenTime = $this->getCheckToken($refreshToken, $secretKey);

        $tokenResult = $this->getCheckTokenExpireTime($accessTokenTime, $refreshTokenTime);
        if ($tokenResult['status'] === 'both') {
            $dTokenQ = $this->emsQuery->getQueryDeleteJwt($id);
            $this->db->squery($dTokenQ);
            header('HTTP/1.0 401 Unauthorized');
            return $fcData;
        }

        if (empty($tokenResult['status']) === false) {
            $this->updateToken($id, $secretKey, $tokenResult['status']);
            return $fcData;
        }

        $fcData['result'] = true;
        $fcData['reason'] = '';

        $fcData = [
            'result' => true,
            'reason' => '',
            'id' => $checkId,
            'target' => $target,
        ];

        return $fcData;
    }

    /**
     * 토큰 확인
     *
     * @param string $token
     * @param string $secretKey
     *
     * @return string
     */
    public function getCheckToken(string $token, string $secretKey) : string
    {
        // 만료일자를 반환
        $fcString = '';

        $tokenDecode = Utility::getInstance()->updateDecryption($token);

        $tokenPartitions = explode('.', $tokenDecode);
        if (count($tokenPartitions) < 3) {
            return $fcString;
        }

        $payLoads = json_decode($tokenPartitions[1], true);
        $signature = $tokenPartitions[2];

        if (hash($this->algorithm, $tokenPartitions[0] . $tokenPartitions[1] . $secretKey) !== $signature) {
            // 시그니쳐 비교
            return $fcString;
        }

        return $payLoads['expire_date'];
    }

    /**
     * 토큰 만료시간 체크
     *
     * @param string $accessTokenTime
     * @param string $refreshTokenTime
     *
     * @return string[]
     */
    public function getCheckTokenExpireTime(string $accessTokenTime, string $refreshTokenTime) : array
    {
        $todayTime = time();
        
        $result = [
            'status' => ''
        ];

        /*
            $todayTime = strtotime('20220706133030');
            $accessTokenTime = strtotime('20220706095000');
            $refreshTokenTime = strtotime('20220706112000');
            echo date('Y-m-d H:i:s', $todayTime) .": " . date('Y-m-d H:i:s', $accessTokenTime) ."=======> " .  date('Y-m-d H:i:s', $refreshTokenTime) . "\n\n\n";
        */

        if ($todayTime > $accessTokenTime && $todayTime > $refreshTokenTime) {
            // 토큰이 모두 만료 된 경우 로그인 요청
            $result['status'] = 'both';
            return $result;
        }
        
        if ($todayTime > $accessTokenTime && $todayTime < $refreshTokenTime) {
            // access token 만료 되었어도 refresh token 있다면 access token 갱신
            $result['status'] = 'access_token';
            return $result;
        }
        
        if ($todayTime < $accessTokenTime && $todayTime > $refreshTokenTime) {
            // refresh token 만료되었으나, access token 살아있다면 refresh token 갱신
            $result['status'] = 'refresh_token';
            return $result;
        }

        return $result;
    }

    /**
     * 토큰 내용 중 페이로드 조회
     *
     * @param string $token
     * @param string $secretKey
     *
     * @return array
     */
    public function getTokenPayLoad(string $token, string $secretKey) : array
    {
        $fcPayLoads = [];

        $tokenDecode = Utility::getInstance()->updateDecryption($token);

        $tokenPartitions = explode('.', $tokenDecode);
        if (count($tokenPartitions) < 3) {
            return $fcPayLoads;
        }

        $signature = $tokenPartitions[2];

        if (hash($this->algorithm, $tokenPartitions[0] . $tokenPartitions[1] . $secretKey) !== $signature) {
            // 시그니쳐 비교
            return $fcPayLoads;
        }

        $result = json_decode($tokenPartitions[1], true);
        if (is_array($result) === true) {
            $fcPayLoads = $result;
        }

        return $fcPayLoads;
    }

    /**
     * 토큰 갱신
     *
     * @param string $id
     * @param string $secretKey
     * @param string $tokenColumn
     */
    public function updateToken(string $id, string $secretKey, string $tokenColumn) : void
    {
        // 초기화
        $this->initialize();

        $today = date('YmdHis');

        $fcPayLoads = [
            'make_date' => strtotime($today),
            'expire_date' => '',
            'id' => $id
        ];

        $token = $this->makeToken($secretKey, $tokenColumn, $fcPayLoads);
        if (empty($token) === false) {
            $uTokenQ = $this->emsQuery->getQueryUpdateJwt($id, $tokenColumn, $token);
            $this->db->squery($uTokenQ);
        }

        header('Authorization: ' . $token);
        header('HTTP/1.0 401 Unauthorized');
    }
    

    /**
     * 토큰 타입에 대해 시간 조회
     *
     * @param $tokenType
     *
     * @return int
     */
    public function getTokenTime($tokenType) : int
    {
        $fcTokenTime = 0;
        $fcToday = date('YmdHis');

        $accessTokenDateType = $this->accessTokenDateType;
        $refreshTokenDateType = $this->refreshTokenDateType;

        switch ($tokenType) {
            case 'access_token' :
                $fcTokenTime = strtotime($fcToday . "+{$accessTokenDateType} day");
                break;
            case 'refresh_token' :
                $fcTokenTime = strtotime($fcToday . "+{$refreshTokenDateType} day");
                break;
        }

        return $fcTokenTime;
    }

    /**
     * 사용자가 요청한 Authorization 조회
     *
     * @return string
     */
    public function getAuthorization() : string
    {
        $fcToken = '';

        $headers = getallheaders();
        $authorizationType = $this->authorizationType;

        $authorization = $headers['Authorization'] ?? '';
        $authorizationData = Utility::getInstance()->getExplodeData($authorization);

        if (count($authorizationData) < 2) {
            return $fcToken;
        }

        $userAuthorizationType = $authorizationData[0];
        if ($userAuthorizationType !== $authorizationType) {
            return $fcToken;
        }

        $fcToken = $authorizationData[1];

        return $fcToken;
    }
}