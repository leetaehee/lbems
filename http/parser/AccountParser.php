<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\ChangePassword;
use Http\Command\MakeApiAccountKey;

/**
 * Class AccountParser
 */
class AccountParser implements IParser
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
            case ChangePassword:
                $command = new ChangePassword();
                break;
            case MakeApiAccountKey:
                $command = new MakeApiAccountKey();
                break;
        }

        return $command;
    }
}