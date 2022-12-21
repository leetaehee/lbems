<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\WeatherFinedust;
use Http\Command\WeatherOpenApi;
use Http\Command\WeatherTempHumiCur;
use Http\Command\WeatherMinistryFinedust;

/**
 * Class WeatherParser
 */
class WeatherParser implements IParser 
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
            case WeatherFinedust:
                $command = new WeatherFinedust();
                break;
            case WeatherOpenApi:
                $command = new WeatherOpenApi();
                break;
            case WeatherTempHumiCur:
                $command = new WeatherTempHumiCur();
                break;
            case WeatherMinistryFinedust:
                $command = new WeatherMinistryFinedust();
                break;
		}

        return $command;
    }
}
