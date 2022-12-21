<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\InfoEnergy;
use Http\Command\InfoStatus;
use Http\Command\InfoFinedust;
use Http\Command\InfoPopup;
use Http\Command\InfoEnvironment;

/**
 * Class InfoParser
 */
class InfoParser implements IParser
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
            case InfoEnergy:
            case InfoUsage:
            case InfoFacilities:
				// 정보감시 조회
				$command = new InfoEnergy();
				break;
            case InfoStatus :
                // 정보감시 (부하별) 조회
                $command = new InfoStatus();
                break;
			case InfoFinedust:
				// 미세먼지 조회
				$command = new InfoFinedust();
				break;
			case InfoPopup:
			    // 기준값 팝업
				$command = new InfoPopup();
				break;
            case InfoEnvironment:
                // 실내환경정보 조회
                $command = new InfoEnvironment();
                break;
        }

        return $command;
    }
}