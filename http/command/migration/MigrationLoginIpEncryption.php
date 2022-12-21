<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\MigrationQuery;
use EMS_Module\Utility;

/**
 * Class MigrationLoginIpEncryption 로그인  ip를 암호화 마이그레이션
 */
class MigrationLoginIpEncryption extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

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

        $key = bin2hex('');
        $iv = bin2hex('');

        //$key = 'fYlhkaJLs6SI6rg';

        $migrationQuery = new MigrationQuery();

        $rLoginIpQ = $migrationQuery->getQuerySelectLoginIp();
        $rLoginIpData = $this->query($rLoginIpQ);

        for ($i = 0; $i < count($rLoginIpData); $i++) {
            $pk = $rLoginIpData[$i]['log_pk'];
            $ipAddr = Utility::getInstance()->updateDecryption($rLoginIpData[$i]['ip_addr']);

            /*
            $ipAddr = openssl_encrypt($ipAddr, $algorithm, $key, false, str_repeat(chr(0), 16));
            */

            $ipAddr = base64_encode(openssl_encrypt($ipAddr, $algorithm, $key, 1, $iv));

            $uEncryptionQ = $migrationQuery->getQueryUpdateLoginIpEncryption($pk, $ipAddr);
            echo $uEncryptionQ . "\n\n";
            //$this->squery($uEncryptionQ);
        }

        $this->data = [];
        return true;
    }
}