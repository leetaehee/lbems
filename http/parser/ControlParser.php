<?php
namespace Http\Parser;

use Http\Command\Command;

use Http\Command\Control;
use Http\Command\ControlSet;

/**
 * Class ControlParser
 */
class ControlParser implements IParser
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
            case Control :
                $command = new Control();
                break;
            case ControlSet :
                $command = new ControlSet();
                break;
        }

        return $command;
    }
}