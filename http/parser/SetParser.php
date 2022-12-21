<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\SetInfo;
use Http\Command\SetSave;
use Http\Command\SetStandard;
use Http\Command\SetStandardSave;
use Http\Command\SetStandardCode;
use Http\Command\UnitPrice;
use Http\Command\SetUnitPrice;
use Http\Command\UnitPriceKepco;
use Http\Command\UnitPriceKepcoInfo;
use Http\Command\UnitPriceKepcoSet;
use Http\Command\Instrument;
use Http\Command\MonitoringInfo;

/**
 * Class SetParser
 */
class SetParser implements IParser
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
            case SetInfo:
                // 설정 > 기본정보 조회
                $command = new SetInfo();
                break;
            case SetSave:
                // 설정 > 기본정보 설정
                $command = new SetSave();
                break;
            case SetStandard:
                // 설정 > 목표값/기준값 조회
                $command = new SetStandard();
                break;
            case SetStandardSave:
                // 설정 > 목표값/기준값 설정
                $command = new SetStandardSave();
                break;
            case SetStandardCode:
                // 설정 > 목표값/기준값 코드 조회
                $command = new SetStandardCode();
                break;
            case UnitPrice:
                // 설정 > 에너지 단가관리 조회
                $command = new UnitPrice();
                break;
            case SetUnitPrice:
                // 설정 > 에너지 단가관리 설정
                $command = new SetUnitPrice();
                break;
            case UnitPriceKepco:
                // 설정, 관리자설정 > 에너지 단가관리 조회 (kepco 기준)
                $command = new UnitPriceKepco();
                break;
            case UnitPriceKepcoInfo:
                // 설정, 관리자설정 > 에너지 단가관리에서 개별 항목 조회 (kepco 기준)
                $command = new UnitPriceKepcoInfo();
                break;
            case UnitPriceKepcoSet:
                // 설정, 관리자설정 > 에너지 단가관리 정보 수정 (kepco 기준)
                $command = new UnitPriceKepcoSet();
                break;
            case Instrument:
                // 설정 > 계측기현황
                $command = new Instrument();
                break;
            case MonitoringInfo:
                // 설정 > 계측기현황에서 층별 센서 정보 조회
                $command = new MonitoringInfo();
                break;
        }

        return $command;
    }
}