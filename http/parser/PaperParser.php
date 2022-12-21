<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Paper;
use Http\Command\PaperExcel;

/**
 * Class PaperParser
 */
class PaperParser implements IParser 
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
			case Paper:
				$command = new Paper();
				break;
			case PaperExcel:
				$command = new PaperExcel();
				break;
		}

        return $command;
    }
}
