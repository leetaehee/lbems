<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\FileDownload;
use Http\Command\EnergyCode;
use Http\Command\HomeInfo;
use Http\Command\TmpSession;

class CommonParser implements IParser
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
            case FileDownload:
                $command = new FileDownload();
                break;
            case EnergyCode:
                $command = new EnergyCode();
                break;
            case HomeInfo:
                $command = new HomeInfo();
                break;
            case TmpSession:
                $command = new TmpSession();
                break;
        }

        return $command;
    }
}