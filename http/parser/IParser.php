<?php
namespace Http\Parser;

use Http\Command\Command;

/**
 * Interface IParser
 */
interface IParser
{
    /**
     * request 에 따라 Command 객체 반환
     *
     * @param string $request
     *
     * @return Command
     */
	public function getCommand(string $request) : Command;
}