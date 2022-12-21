<?php
namespace Module;

/**
 * Class Mail 메일 전송 클래스
 */
class Mail
{
    /** @var Object $driver 메일 드라이버  */
    private $driver = null;

    /** @var string $charSet 캐릭터셋 설정 */
    private $charSet = 'UTF-8';

    /** @var bool $SMTPAuth  SMTPAuth 설정 */
    private $SMTPAuth = true;

    /** @var array $envOptions env 목록 */
    private $envOptions = [];

    /**
     * Mail Constructor.
     *
     * @param string $mailType
     */
    public function __construct(string $mailType)
    {
        $this->setDriver($mailType);
        $this->envOptions = parse_ini_file(ConfigFile);
    }

    /**
     * Mail Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 메일 전송
     *
     * @param string $toAddress
     * @param string $subject
     * @param string $content
     *
     * @return bool
     *
     * @throws \phpmailerException
     */
    public function mailTransmit(string $toAddress, string $subject, string $content) : bool
    {
        $result = true;

        $driver = $this->driver;
        $className = get_class($driver);

        $mailData = [
            'to' => $toAddress,
            'subject' => $subject,
            'content' => $content,
        ];

        if ($className === 'PHPMailer') {
            $result = $this->mailTransmitByPHPMailer($driver, $mailData);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * 메일 드라이버 객체 생성
     *
     * @param string $mailType
     */
    private function setDriver(string $mailType) :void
    {
        if ($mailType === 'PHPMailer') {
            $this->driver = new \PHPMailer();
        }
    }

    /**
     * PHP Mailer로 메일 전송
     *
     * @param \PHPMailer $obj
     * @param array $mailData
     *
     * @return bool
     *
     * @throws \phpmailerException
     */
    private function mailTransmitByPHPMailer(\PHPMailer $obj, array $mailData) : bool
    {
        $envOptions = $this->envOptions;

        $obj->IsSMTP();
        $obj->CharSet = $this->charSet;
        $obj->SMTPSecure = $envOptions['SMTP_SECURE'];
        $obj->Host = $envOptions['MAIL_HOST'];
        $obj->Port = $envOptions['MAIL_PORT'];
        $obj->Username = $envOptions['MAIL_USERNAME'];
        $obj->Password = $envOptions['MAIL_PASSWORD'];
        $obj->SMTPAuth = $this->SMTPAuth;
        $obj->From = $envOptions['KEVIN_EMAIL'];
        $obj->FromName = $envOptions['MAIL_FROM_NAME'];
        $obj->Subject = $mailData['subject'];
        $obj->msgHTML($mailData['content']);
        $obj->addAddress($mailData['to']);

        return $obj->send();
    }
}