<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Diagram;
use Http\Command\DiagramFacility;
use Http\Command\DiagramKey;
use Http\Command\MobileDiagram;

/**
 * Class DiagramParser
 */
class DiagramParser implements IParser 
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
			case Diagram:
				$command = new Diagram();
				break;
            case DiagramFacility:
                $command = new DiagramFacility();
                break;
            case DiagramKey:
                $command = new DiagramKey();
                break;
            case MobileDiagram:
                $command = new MobileDiagram();
                break;
        }

        return $command;
    }
}
