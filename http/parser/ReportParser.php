<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\ReportEnergy;
use Http\Command\ReportFloor;
use Http\Command\ReportPeriod;

/**
 * Class ReportParser
 */
class ReportParser implements IParser
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
            case ReportEnergy:
                $command = new ReportEnergy();
                break;
            case ReportFloor:
                $command = new ReportFloor();
                break;
            case ReportPeriod:
                $command = new ReportPeriod();
                break;
        }

        return $command;
    }
}