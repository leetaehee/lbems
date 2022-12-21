<?php
namespace Http\Command;

use EMS_Module\MigrationQuery;
use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class MigrationAdminEncryptionIV  iv 키와 비밀키 이용해서 관리자 정보  암호화 마이그레이션
 */
class MigrationAdminEncryptionIV extends Command
{
    /**
     * MigrationAdminEncryptionIV Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MigrationAdminEncryptionIV Destructor
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
        $algorithm = Config::ENCRYPTION_ALGORITHM;

        $key = hex2bin('');
        $iv = hex2bin('');

        //$key = '';

        $migrationQuery = new MigrationQuery();

        $rAdminQ = $migrationQuery->getQuerySelectAdminEncryptionColumn();
        $adminData = $this->query($rAdminQ);

        for ($i = 0; $i < count($adminData); $i++) {
            $pk = $adminData[$i]['admin_pk'];
            $name = Utility::getInstance()->updateDecryption($adminData[$i]['name']);
            $email = Utility::getInstance()->updateDecryption($adminData[$i]['email']);
            $hp = Utility::getInstance()->updateDecryption($adminData[$i]['hp']);

            /*
            $name = openssl_encrypt($name, $algorithm, $key, false, str_repeat(chr(0), 16));
            $email = openssl_encrypt($email, $algorithm, $key, false, str_repeat(chr(0), 16));
            $hp = openssl_encrypt($hp, $algorithm, $key, false, str_repeat(chr(0), 16));
            */

            $name = openssl_encrypt($name, $algorithm, $key, 1, $iv);
            $email = openssl_encrypt($email, $algorithm, $key, 1, $iv);
            $hp = openssl_encrypt($hp, $algorithm, $key, 1, $iv);

            $name = base64_encode($name);
            $email = base64_encode($email);
            $hp = base64_encode($hp);

            $uAdminQ = $migrationQuery->getQueryUpdateAdminEncryptionColumn($pk, $name, $email, $hp);
            echo $uAdminQ . "\n\n";
            //$this->squery($uAdminQ);
        }

        $this->data = [];
        return true;
    }
}