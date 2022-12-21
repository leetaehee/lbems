<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Facility;
use Http\Command\FacilityEfficiency;

/**
 * Class FacilityParser
 */
class FacilityParser implements IParser
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
            case Facility:
                $command = new Facility();
                break;
            case FacilityEfficiency:
                $command = new FacilityEfficiency();
                break;
        }

        return $command;
    }
}