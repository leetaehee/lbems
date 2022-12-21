<?php
namespace Http\Command;

use EMS_Module\MigrationQuery;
use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class MigrationComplexEncryptionIV iv 키와 비밀키 이용해서 단지 정보  암호화 마이그레이션
 */
class MigrationComplexEncryptionIV extends Command
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

        $rComplexQ = $migrationQuery->getQuerySelectComplexEncryptionColumn();
        $complexData = $this->query($rComplexQ);

        for ($i = 0; $i < count($complexData); $i++) {
            $complexCodePk = $complexData[$i]['complex_code_pk'];
            $name = Utility::getInstance()->updateDecryption($complexData[$i]['name']);
            $addr = Utility::getInstance()->updateDecryption($complexData[$i]['addr']);
            $tel = Utility::getInstance()->updateDecryption($complexData[$i]['tel']);
            $fax = Utility::getInstance()->updateDecryption($complexData[$i]['fax']);

            $manager = $complexData[$i]['manager'] ?? '';

            $manager = Utility::getInstance()->updateDecryption($manager);

            /*
            $name = openssl_encrypt($name, $algorithm, $key, false, str_repeat(chr(0), 16));
            $addr = openssl_encrypt($addr, $algorithm, $key, false, str_repeat(chr(0), 16));
            $tel = openssl_encrypt($tel, $algorithm, $key, false, str_repeat(chr(0), 16));
            $fax = openssl_encrypt($fax, $algorithm, $key, false, str_repeat(chr(0), 16));
            $manager = openssl_encrypt($manager, $algorithm, $key, false, str_repeat(chr(0), 16));
            */

            $name = openssl_encrypt($name, $algorithm, $key, 1, $iv);
            $addr = openssl_encrypt($addr, $algorithm, $key, 1, $iv);
            $tel = openssl_encrypt($tel, $algorithm, $key, 1, $iv);
            $fax = openssl_encrypt($fax, $algorithm, $key, 1, $iv);
            $manager = openssl_encrypt($manager, $algorithm, $key, 1, $iv);

            $name = base64_encode($name);
            $addr = base64_encode($addr);
            $tel = base64_encode($tel);
            $fax = base64_encode($fax);
            $manager = base64_encode($manager);

            $uComplexQ = $migrationQuery->getQueryUpdateComplexEncryptionColumn($complexCodePk, $name,  $addr, $tel, $fax, $manager);
            echo $uComplexQ . "\n";
            //$this->squery($uComplexQ);
        }

        $this->data = [];
        return true;
    }
}