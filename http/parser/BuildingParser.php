<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\BuildingManager;
use Http\Command\BuildingList;
use Http\Command\MobileFloorInfo;

/**
 * Class BuildingParser
 */
class BuildingParser implements IParser
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
            case BuildingManager:
                $command = new BuildingManager();
                break;
            case BuildingList:
                $command = new BuildingList();
                break;
            case MobileFloorInfo:
                $command = new MobileFloorInfo();
                break;
		}

        return $command;
    }
}