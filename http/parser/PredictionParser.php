<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\EnergyPrediction;
use Http\Command\SolarPrediction;
use Http\Command\MobilePrediction;

/**
 * Class PredictionParser
 */
class PredictionParser implements IParser
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
            case EnergyPrediction:
                $command = new EnergyPrediction();
                break;
            case SolarPrediction:
                $command = new SolarPrediction();
                break;
            case MobilePrediction:
                $command = new MobilePrediction();
                break;
		}

        return $command;
    }
}