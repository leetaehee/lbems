<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\ManagerBuilding;
use Http\Command\ManagerLogin;
use Http\Command\ManagerAuthority;
use Http\Command\Equipment;
use Http\Command\EquipmentSet;
use Http\Command\EquipmentInfo;
use Http\Command\PasswordInitialize;

/**
 * Class ManagerParser
 */
class ManagerParser implements IParser
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
			case ManagerBuilding:
				$command = new ManagerBuilding();
				break;
			case ManagerLogin:
				$command = new ManagerLogin();
				break;
			case ManagerAuthority:
				$command = new ManagerAuthority();
				break;
            case Equipment:
                $command = new Equipment();
                break;
            case EquipmentSet:
                $command = new EquipmentSet();
                break;
            case EquipmentInfo:
                $command = new EquipmentInfo();
                break;
            case PasswordInitialize:
                $command = new PasswordInitialize();
                break;
		}

        return $command;
    }
}