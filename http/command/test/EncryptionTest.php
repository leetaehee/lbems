<?php
namespace Http\Command;

/**
 * Class EncryptionTest 암호화/복호화 테스트
 */
class EncryptionTest extends Command
{
    /**
     * EncryptionTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * EncryptionTest destructor.
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
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        //$encodePassword = '';
        //echo "decode passwrod = " . base64_decode($encodePassword) . "\n";

        //$encodePasword = '';
        //echo "encode password = " . password_hash($encodePasword, PASSWORD_DEFAULT);

        $this->data = $data;
        return true;
    }
}