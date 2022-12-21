<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class MakeApiAccountKey API에 사용되는 비밀번호, 클라이언트 키, 헤더값 등 생성
 */
class MakeApiAccountKey extends Command
{
    /**
     * MakeApiAccountKey Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MakeApiAccountKey Destructor.
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
        $data = [];

        $id = '';
        $password = '';
        //password = Utility::getInstance()->getPasswordHashValue('');
        $clientSecretKey = '';
        $ivKey = '';

        // 클라이언트 키, iv 키 생성
        /*
            $secretKey = Utility::getInstance()->getSecretKey(32);
            $iv = Utility::getInstance()->getSecretKey(16);

            echo "secret_key = " . $secretKey  . " \n\n";
            echo "iv = " . $iv . " \n\n";

            $key = bin2hex($secretKey);
            $iv = bin2hex($iv);

            echo "====> client key = " . $key . "\n\n\n";
            echo "====> iv key = " . $iv . "\n\n\n";

            echo "===>" . hex2bin($key) . "\n\n\n";
            echo "===> " . hex2bin($iv) . "\n\n";

            exit;
        */

        /*
            $encryptionId = openssl_encrypt('kevinlab', 'aes-256-cbc', $key, 1, $iv);
            $encryptionId = bin2hex($encryptionId);

            $encryptionPW = openssl_encrypt('kevin003', 'aes-256-cbc', $key, 1, $iv);
            $encryptionPW = bin2hex($encryptionPW);

            //echo "===>" . $encryptionId . "(byte=" .strlen($encryptionId) .") \n\n";
            echo "===>" . $encryptionPW . "(byte=" .strlen($encryptionPW) .") \n\n";

            $decryptionId = hex2bin($encryptionId);
            $decryptionPw = hex2bin($encryptionPW);

            $decryptionId = openssl_decrypt($decryptionId, 'aes-256-cbc', $key, 1, $iv);
            $decryptionPw = openssl_decrypt($decryptionPw, 'aes-256-cbc', $key, 1, $iv);
            echo "===>" . $decryptionId ."\n\n ==>" . $decryptionPw . "\n\n";

            exit;
        */

        // 암호화
        $options = [
            'secret_key' => $clientSecretKey,
            'iv_key' => $ivKey,
        ];

        $id = Utility::getInstance()->updateEncryption($id, $options);
        $password =  Utility::getInstance()->updateEncryption($password, $options);

        //echo "==========> 결과 \n";
        //echo "> clientSecretKey = {$clientSecretKey} \n";
        //echo "> ivKey = {$ivKey} \n";
        //echo "> key = {$key} \n";
        echo "> id = {$id} \n";
        echo "> password = {$password} \n";
        //echo "> 크기 = " . strlen($clientSecretKey) . ", ". strlen($key) . "\n\n";
        //echo "> 패스워드 단방향 암호화 = " . Utility::getInstance()->getPasswordHashValue($password) . "\n\n";
        //echo  "> 복호화 아이디 = " . Utility::getInstance()->updateDecryption($id, $options) . "\n\n\n";

        $this->data = $data;
        return true;
    }
}