<?php
namespace Http\Parser;

use Http\Command\ArrangeTime;
use Http\Command\Command;
use Http\Command\ArrangeDay;
use Http\Command\ArrangeMonth;
use Http\Command\AddElechotMdmt;
use Http\Command\AddEleventMdmt;
use Http\Command\AddElectricMdmt;
use Http\Command\AddElectricAllMdmt;
use Http\Command\AddElechotTbmt;
use Http\Command\AddElectricAllTbmt;
use Http\Command\AddElectricAllNedOb;
use Http\Command\MonitorAlarmOn;
use Http\Command\MonitorAlarmOff;
use Http\Command\ArrangeFinedustDay;
use Http\Command\AddElectricMeterNtek;
use Http\Command\AddFinedust;
use Http\Command\ArrangeCo2Day;
use Http\Command\ArrangeCo2Month;
use Http\Command\ArrangeFinedustMonth;
use Http\Command\AddElectricAllScnr;
use Http\Command\ArrangeEfficiencyDay;
use Http\Command\ArrangeEfficiencyMonth;
use Http\Command\ArrangeEfficiencyTime;
use Http\Command\ArrangeAiPrediction;
use Http\Command\AddElectricAllBangbae;
use Http\Command\AddElectricAllDado;
use Http\Command\AddElectricAllKhc;
use Http\Command\AddMeterCnc;
use Http\Command\AddMeterTOC;
use Http\Command\AddComplexDataTOC;
use Http\Command\ArrangeStatusTypeDay;
use Http\Command\ArrangeStatusTypeMonth;
use Http\Command\AddElectricAllHjecc;
use Http\Command\AddElectricAllSct;
use Http\Command\AddElectricAllBhmt;
use Http\Command\AddElectricAllKsbc;
use Http\Command\AddElectricAllKfl;

/**
 * Class WatchdogParser
 */
class WatchdogParser implements IParser 
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
            case ArrangeTime:
                // 시간통계
                $command = new ArrangeTime();
                break;
            case ArrangeDay:
                // 일통계
                $command = new ArrangeDay();
                break;
            case ArrangeMonth:
                // 월통계
                $command = new ArrangeMonth();
                break;
            case ArrangeFinedustDay:
                // 미세먼지 일 통계
                $command = new ArrangeFinedustDay();
                break;
            case ArrangeFinedustMonth:
                // 미세먼지 월 통계
                $command = new ArrangeFinedustMonth();
                break;
            case ArrangeCo2Day:
                // Co2 일 통계
                $command = new ArrangeCo2Day();
                break;
            case ArrangeCo2Month:
                // Co2 월 통계
                $command = new ArrangeCo2Month();
                break;
            case ArrangeEfficiencyDay:
                // 역률 일통계
                $command = new ArrangeEfficiencyDay();
                break;
            case ArrangeEfficiencyMonth:
                // 역률 월통계
                $command = new ArrangeEfficiencyMonth();
                break;
            case ArrangeEfficiencyTime:
                // 역률 시간통계
                $command = new ArrangeEfficiencyTime();
                break;
            case ArrangeStatusTypeDay:
                // 경부하,중부하,최대부하 일통계
                $command = new ArrangeStatusTypeDay();
                break;
            case ArrangeStatusTypeMonth:
                // 경부하,중부하,최대부하 월통계
                $command = new ArrangeStatusTypeMonth();
                break;
            case AddElechotMdmt:
                // 무등산 - 전열
                $command = new AddElechotMdmt();
                break;
            case AddEleventMdmt:
                // 무등산 - 환기
                $command = new AddEleventMdmt();
                break;
            case AddElectricAllMdmt:
                // 무등산 - 전체전력
                $command = new AddElectricAllMdmt();
                break;
            case AddElechotTbmt:
                // 태백산 - 전열
                $command = new AddElechotTbmt();
                break;
            case AddElectricAllTbmt:
                // 태백산 - 전체전력
                $command = new AddElectricAllTbmt();
                break;
            case AddElectricAllNedOb:
                // 대전네드사옥 - 전체전력
                $command = new AddElectricAllNedOb();
                break;
            case AddElectricAllScnr:
                // 빛사랑어린이집 - 전체전력
                $command = new AddElectricAllScnr();
                break;
            case AddElectricAllBangbae:
                // 방배동근린생활시설 - 전체전력
                $command = new AddElectricAllBangbae();
                break;
            case AddElectricAllDado:
                // 다도해국립공원 - 전체전력
                $command = new AddElectricAllDado();
                break;
            case AddElectricAllKhc:
                // 김해 행정복지센터 - 전체전력
                $command = new AddElectricAllKhc();
                break;
            case AddElectricAllHjecc:
                // 장애인 내일키움 직업교육센터 - 전체전력
                $command = new AddElectricAllHjecc();
                break;
            case AddElectricAllSct:
                // 새마을중앙연수원 - 전체전력
                $command = new AddElectricAllSct();
                break;
            case AddElectricAllBhmt:
                // 북한산국립공원 - 전체전력
                $command = new AddElectricAllBhmt();
                break;
            case AddElectricAllKsbc:
                // 김해소상공인물류센터 - 전체전력
                $command = new AddElectricAllKsbc();
                break;
            case AddElectricAllKfl:
                // 한국식품연구원 - 전체전력
                $command = new AddElectricAllKfl();
                break;
            case MonitorAlarmOn:
                // 장애 알람 - on
                $command = new MonitorAlarmOn();
                break;
            case MonitorAlarmOff:
                // 장애 알람 - off
                $command = new MonitorAlarmOff();
                break;
            case AddElectricMdmt:
                // 무등산 - raw data
                $command = new AddElectricMdmt();
                break;
            case AddFinedust:
                // 미세먼지 데이터 수신
                $command = new AddFinedust();
                break;
            case AddElectricMeterNtek:
                // 엔텍 데이터 수신
                $command = new AddElectricMeterNtek();
                break;
            case AddMeterCnc:
                // CNC 데이터 수신
                $command = new AddMeterCnc();
                break;
            case ArrangeAiPrediction:
                // AI 예측 정보 조회
                $command = new ArrangeAiPrediction();
                break;
            case AddMeterTOC:
                // TOC 데이터 전달
                $command = new AddMeterTOC();
                break;
            case AddComplexDataTOC:
                // TOC 건물정보 전달
                $command = new AddComplexDataTOC();
                break;
		}

        return $command;
    }
}