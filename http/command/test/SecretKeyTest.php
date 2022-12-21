<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class SecretKeyTest 양방향 암호화/복호화시 사용할 키 생성
 */
class SecretKeyTest extends Command
{
    /**
     * SecretKeyTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SecretKeyTest destructor.
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
    public function execute(array $params): ?bool
    {
        //$secretKey = Utility::getInstance()->getSecretKey();
        //echo "secret = " . $secretKey ."\n";

        $key = '';
        $test = ''; // 이메일
        $test = ''; // 핸드폰
        $test = ''; // 태백산
        $test = ''; // 주소
        $encryptValue = '';

        //$encryptValue = Utility::getInstance()->updateEncryption($test);
        //echo 'encrypt=' . $encryptValue ."\n";
        //echo 'length=' . strlen($encryptValue) ."\n";

        $decryptValue = Utility::getInstance()->updateDecryption($encryptValue);
        echo 'decrypt=' . $decryptValue ."\n";

        //echo 'hash = ' . hash("sha256", "1234dfdsfdsfsdfsdfdfd567") . "\n\n";

        $this->data = [];
        return true;
    }
}