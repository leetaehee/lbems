<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Login;
use Http\Command\Logout;

/**
 * Class LoginParser
 */
class LoginParser implements IParser
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
			case Login:
				$command = new Login();
				break;
			case Logout:
				$command = new Logout();
				break;
		}

		return $command;
	}
}