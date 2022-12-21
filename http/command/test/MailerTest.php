<?php
namespace Http\Command;

use Module\Mail;

/**
 * Class MailerTest
 */
class MailerTest extends Command
{
    /**
     * MailerTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MailerTest Destructor.
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
    public function execute(array $params) :? bool
    {
        $devOptions = $this->devOptions;
        $mailClass = new Mail($devOptions['MAIL_TYPE']);

        $mailData = [
            'to' => 'lastride25@naver.com',
            'subject' => '케빈랩 메일테스트입니다.',
            'content' => '메일테스트 중입니다.'
        ];
        $mailClass->mailTransmit($mailData['to'], $mailData['subject'], $mailData['content']);

        $this->data = [];
        return true;
    }
}