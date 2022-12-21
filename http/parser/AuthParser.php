<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\ReceiveAuthNum;
use Http\Command\ConfirmAuthInfo;

/**
 * Class AuthParser
 */
class AuthParser implements IParser
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
            case ReceiveAuthNum:
                $command = new ReceiveAuthNum();
                break;
            case ConfirmAuthInfo:
                $command = new ConfirmAuthInfo();
                break;
        }

        return $command;
    }
}