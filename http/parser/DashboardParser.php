<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\Dashboard;
use Http\Command\DashboardFloor;
use Http\Command\DashboardFloorFacility;
use Http\Command\DashboardReferenceSave;

/**
 * Class DashboardParser
 */
class DashboardParser implements IParser 
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
            case Dashboard:
                // 대시보드 전체
                $command = new Dashboard();
                break;
            case DashboardFloor:
                // 대시보드 세부
                $command = new DashboardFloor();
                break;
            case DashboardFloorFacility:
                // 대시보드 세부(설비별 기능 포함)
                $command = new DashboardFloorFacility();
                break;
            case DashboardReferenceSave:
                // 목표값 기준값 설정
                $command = new DashboardReferenceSave();
                break;
        }

        return $command;
    }
}
