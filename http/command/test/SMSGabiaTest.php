<?php
namespace Http\Command;

/**
 * Class SMSGabiaTest 가비아 문자 요청 테스트
 */
class SMSGabiaTest extends Command
{
    /**
     * SMSGabiaTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SMSGabiaTest destructor.
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
        // 커밋 테스트;
        $devOptions = $this->devOptions;

        $curl = curl_init();

        $message = '케빈랩 Rest API 테스트 중입니다.';

        $smsKey = $devOptions['SMS_KEY'];
        $smsId = $devOptions['SMS_ID'];
        $smsSensorNumber = $devOptions['SMS_SENDER_NUMBER'];

        $base64AppKey = base64_encode("{$smsId}:{$smsKey}");

        // 초기화
        $curl = curl_init();

        // 인증정보 확인 후 access_token 정보 취득
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.gabia.com/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic {$base64AppKey}",
            ],
        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);
        if ($err) {
            $this->data = [
              'message' => 'Curl Error #: ' . $err
            ];

            return true;
        }

        $oAuths = json_decode($response, true);

        $accessToken = $oAuths['access_token'];
        if ($accessToken === '' || $accessToken === null) {
            $this->data = [
                'message' => 'Curl Error #: No access Token.',
            ];
            return true;
        }

        $authorizationKey = base64_encode("{$smsId}:{$accessToken}");

        // access 토큰 정보가 있다면 문자 발송을 진행..
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sms.gabia.com/api/send/sms',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "phone=01027891039&callback={$smsSensorNumber}&message={$message}&refkey=[[RESTAPITEST1549847130]]",
            CURLOPT_HTTPHEADER => [
              "Content-Type: application/x-www-form-urlencoded",
              "Authorization: Basic {$authorizationKey}",
            ],
        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $this->data = [
                'message' => 'cURL Error #: ' . $err,
            ];
            return true;
        }

        $this->data = [];
        return true;
    }
}