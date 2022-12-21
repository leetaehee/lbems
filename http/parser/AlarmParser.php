<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\HindranceAlarm;
use Http\Command\HindranceStatus;
use Http\Command\HindranceExcel;
use Http\Command\HindranceInfo;

/**
 * Class AlarmParser
 */
class AlarmParser implements IParser
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
            case HindranceAlarm:
                $command = new HindranceAlarm();
                break;
            case HindranceStatus:
                $command = new HindranceStatus();
                break;
            case HindranceExcel:
                $command = new HindranceExcel();
                break;
            case HindranceInfo:
                $command = new HindranceInfo();
                break;
        }

        return $command;
    }
}