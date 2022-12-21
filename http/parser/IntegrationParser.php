<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\IntegrationComplexSend;
use Http\Command\IntegrationElectricSend;
use Http\Command\IntegrationGasSend;
use Http\Command\IntegrationSolarSend;
use Http\Command\IntegrationFindedustSend;

/**
 * Class IntegrationParser
 */
class IntegrationParser implements IParser
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
            case IntegrationComplexSend :
                // 단지 전송 API
                $command = new IntegrationComplexSend();
                break;
            case IntegrationElectricSend :
                // 전기 사용 정보 API
                $command = new IntegrationElectricSend();
                break;
            case IntegrationGasSend:
                // 가스 사용 정보 API
                $command = new IntegrationGasSend();
                break;
            case IntegrationSolarSend:
                // 태양광 사용 정보 API
                $command = new IntegrationSolarSend();
                break;
            case IntegrationFindedustSend:
                // 환경센서 사용 정보 API
                $command = new IntegrationFindedustSend();
                break;
        }

        return $command;
    }
}