<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\CacheDailyTimeMeter;
use Http\Command\CacheKepcoInfo;

/**
 * Class CacheParser
 */
class CacheParser implements IParser
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
            case CacheKepcoInfo:
                $command = new CacheKepcoInfo();
                break;
        }

        return $command;
    }
}