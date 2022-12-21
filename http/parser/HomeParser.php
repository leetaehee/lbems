<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\MobileHome;

/**
 * Class HomeParser
 */
class HomeParser implements IParser
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
            case MobileHome:
                $command = new MobileHome();
                break;
        }

        return $command;
    }
}