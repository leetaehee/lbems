<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Control;
use Http\Command\ControlSet;
use Http\Command\SamsungControl;
use Http\Command\SamsungControlSet;
use Http\Command\MobileControl;
use Http\Command\MobileControlSet;
use Http\Command\MobileSamsungControl;
use Http\Command\MobileSamsungControlSet;

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
            case SamsungControl :
                $command = new SamsungControl();
                break;
            case SamsungControlSet :
                $command = new SamsungControlSet();
                break;
            case MobileControl :
                $command = new MobileControl();
                break;
            case MobileControlSet :
                $command = new MobileControlSet();
                break;
            case MobileSamsungControl :
                $command = new MobileSamsungControl();
                break;
            case MobileSamsungControlSet :
                $command = new MobileSamsungControlSet();
                break;
        }

        return $command;
    }
}