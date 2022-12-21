<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\EnergyButton;
use Http\Command\MenuAuthority;
use Http\Command\MenuLocation;

/**
 * Class MenuParser
 */
class MenuParser implements IParser
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
            case EnergyButton:
                $command = new EnergyButton();
                break;
            case MenuLocation:
                $command = new MenuLocation();
                break;
            case MenuAuthority:
                $command = new MenuAuthority();
                break;
        }

        return $command;
    }
}