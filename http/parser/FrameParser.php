<?php
namespace Http\Parser;

use Http\Command\Command;
use http\command\MobileFrame;

/**
 * Class FrameParser
 */
class FrameParser implements IParser
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
            case MobileFrame:
                $command = new MobileFrame();
                break;
        }

        return $command;
    }
}