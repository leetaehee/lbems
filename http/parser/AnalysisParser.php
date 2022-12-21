<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\AnalysisTotal;
use Http\Command\AnalysisEnergy;
use Http\Command\AnalysisPeriod;
use Http\Command\AnalysisFloor;
use Http\Command\AnalysisGroupInfo;
use Http\Command\AnalysisZero;
use Http\Command\AnalysisZeroTest;

/**
 * Class AnalysisParser
 */
class AnalysisParser implements IParser
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
            case AnalysisTotal:
                $command = new AnalysisTotal();
                break;
            case AnalysisEnergy:
                $command = new AnalysisEnergy();
                break;
            case AnalysisPeriod:
                $command = new AnalysisPeriod();
                break;
            case AnalysisFloor:
                $command = new AnalysisFloor();
                break;
            case AnalysisGroupInfo:
                $command = new AnalysisGroupInfo();
                break;
            case AnalysisZero:
                $command = new AnalysisZero();
                break;
            case AnalysisZeroTest:
                $command = new AnalysisZeroTest();
                break;
        }

        return $command;
    }
}