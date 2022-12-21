<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Solar;
use Http\Command\SolarExcel;

/**
 * Class SolarParser
 */
class SolarParser implements IParser 
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
			case Solar:
				$command = new Solar();
				break;
            case SolarExcel:
                $command = new SolarExcel();
                break;
        }

        return $command;
    }
}