<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\HolidayApi;

/**
 * Class CalendarParser
 */
class CalendarParser implements IParser
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
            case HolidayApi:
                $command = new HolidayApi();
                break;
        }

        return $command;
    }
}