<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\DashboardTest;
use Http\Command\MailerTest;
use Http\Command\CacheRawDataTest;
use Http\Command\SMSGabiaTest;
use Http\Command\ElectricPriceTest;
use Http\Command\DailyElectricLibraryPrice;
use Http\Command\AreaUsedTest;
use Http\Command\EncryptionTest;
use Http\Command\SecretKeyTest;
use Http\Command\EfficiencyTest;
use Http\Command\ApiTest;

/**
 * Class TestParser
 */
class TestParser implements IParser
{
    /**
     * request 에 따라 Command 객체 반환
     *
     * @param string $request
     *
     * @return Command
     */
    public function getCommand(string $request) : Command
    {
        $command = null;

        switch ($request) {
            case DashboardTest:
                $command = new DashboardTest();
                break;
            case MailerTest:
                $command = new MailerTest();
                break;
            case CacheRawDataTest:
                $command = new CacheRawDataTest();
                break;
            case SMSGabiaTest:
                $command = new SMSGabiaTest();
                break;
            case ElectricPriceTest:
                $command = new ElectricPriceTest();
                break;
            case DailyElectricLibraryPrice:
                $command = new DailyElectricLibraryPrice();
                break;
            case AreaUsedTest:
                $command = new AreaUsedTest();
                break;
            case EncryptionTest:
                $command = new EncryptionTest();
                break;
            case SecretKeyTest:
                $command = new SecretKeyTest();
                break;
            case EfficiencyTest:
                $command = new EfficiencyTest();
                break;
            case ApiTest:
                $command = new ApiTest();
                break;
        }

        return $command;
    }
}