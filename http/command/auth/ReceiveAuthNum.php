<?php
namespace Http\Command;

use EMS_Module\Utility;

use Module\Mail;

/**
 * Class ReceiveAuthNum 인증번호 수신
 */
class ReceiveAuthNum extends Command
{
    /** @var Mail|null $mailDriver 메일 드라이버 */
    private ?Mail $mailDriver = null;

    /**
     * ReceiveAuthNum constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->mailDriver = new Mail($this->devOptions['MAIL_TYPE']);
    }

    /**
     * ReceiveAuthNum destructor.
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
            'is_validate' => true,
        ];

        $name = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $email = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '';

        $validateResult = $this->getValidateCheck($name, $email);

        $data['is_validate'] = $validateResult['is_validate'];
        if ($data['is_validate'] === false) {
            $this->data = $validateResult;
            return true;
        }

        $authValidTime = date('YmdHis');
        $authNumber = $this->makeAuthNumber($validateResult, $email, $authValidTime);

        $data = [
            'auth_number' => $authNumber,
            'make_datetime' => $authValidTime,
        ];
        $this->data = $data;
        return true;
    }

    /**
     * 유효성 검증
     *
     * @param string $name
     * @param string $email
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getValidateCheck(string $name, string $email) : array
    {
        $fcData = [];

        if (empty($name) === true) {
            return [
                'is_validate' => false,
                'key' => 'name',
                'status' => 'empty',
            ];
        }

        if (empty($email) === true) {
            return [
                'is_validate' => false,
                'key' => 'email',
                'status' => 'empty',
            ];
        }

        // 양방향 암호화
        $encryptName = Utility::getInstance()->updateEncryption($name);
        $encryptEmail = Utility::getInstance()->updateEncryption($email);

        $rAdminExistQ = $this->emsQuery->getQuerySelectAdminAccountExist($encryptName, $encryptEmail);
        $existData = $this->query($rAdminExistQ);

        $existCount = (int)$existData[0]['cnt'];
        if ($existCount === 0) {
            return [
                'is_validate' => false,
                'key' => 'not right',
            ];
        }

        $isFirstLogin = (int)$existData[0]['is_first_login'];
        if ($isFirstLogin === 1) {
            return [
                'is_validate' => false,
                'key' => 'first login',
            ];
        }

        $fcData = [
            'admin_pk' => $existData[0]['admin_pk'],
        ];

        return $fcData;
    }

    /**
     * 인증번호 생성 후 발송 하기
     *
     * @param array $userData
     * @param string $email
     * @param string $authValidTime
     *
     * @return string
     *
     * @throws \Exception
     */
    private function makeAuthNumber(array $userData, string $email, string $authValidTime) : string
    {
        $mailDriver = $this->mailDriver;

        $adminPk = $userData['admin_pk'];

        // 인증번호 6자리 생성
        $authNumber = sprintf('%06d', rand(000000, 999999));

        // 인증번호 저장
        $dbAuthNumber = Utility::getInstance()->updateEncryption($authNumber);
        $uAuthNumberQ = $this->emsQuery->getQueryUpdateAuthNumber($adminPk, $dbAuthNumber, $authValidTime);
        $this->squery($uAuthNumberQ);

        // 인증번호 발송
        $mails = [
            'subject' => "비밀번호 인증번호입니다.",
            'content' => "요청하신 인증번호는 {$authNumber} 입니다. 감사합니다.",
        ];
        $mailDriver->mailTransmit($email, $mails['subject'], $mails['content']);

        return $authNumber;
    }
}