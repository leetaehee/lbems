<?php
namespace Http\Parser;

use Http\Command\Command;
use Http\Command\MigrationDailyTableUsed;
use Http\Command\MigrationMonthTableUsed;
use Http\Command\MigrationWeather;
use Http\Command\MigrationLoginIpEncryption;
use Http\Command\MigrationMeterNtek;
use Http\Command\MigrationMonthTableEfficiency;
use Http\Command\MigrationDailyTableStatus;
use Http\Command\MigrationMonthTableStatus;
use Http\Command\MigrationAdminEncryptionIV;
use Http\Command\MigrationComplexEncryptionIV;
use Http\Command\MigrationMeterCnc;
use Http\Command\MigrationMeterCopy;

/**
 * Class MigrationParser
 */
class MigrationParser implements IParser
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
            case MigrationDailyTableUsed:
                // 일통계 1일 누적량 마이그레이션
                $command = new MigrationDailyTableUsed();
                break;
            case MigrationMonthTableUsed:
                // 월통계 1개월 누적량 마이그레이션
                $command = new MigrationMonthTableUsed();
                break;
            case MigrationWeather:
                // bems_weather 건물명 암호화 마이그레이션
                $command = new MigrationWeather();
                break;
            case MigrationLoginIpEncryption:
                // 로그인 시 아이피 암호화 마이그레이션
                $command = new MigrationLoginIpEncryption();
                break;
            case MigrationMeterNtek:
                // 엔텍 데이터 미터에 추가
                $command = new MigrationMeterNtek();
                break;
            case MigrationMonthTableEfficiency:
                // 월 통계 효율 마이그레이션
                $command = new MigrationMonthTableEfficiency();
                break;
            case MigrationDailyTableStatus:
                // 일통계 부하별 마이그레이션
                $command = new MigrationDailyTableStatus();
                break;
            case MigrationMonthTableStatus:
                // 월통계 부하별 마이그레이션
                $command = new MigrationMonthTableStatus();
                break;
            case MigrationAdminEncryptionIV :
                // 관리자 테이블 암호화 마이그레이션
                $command = new MigrationAdminEncryptionIV();
                break;
            case MigrationComplexEncryptionIV:
                // 단지 테이블 암호화 마이그레이션
                $command = new MigrationComplexEncryptionIV();
                break;
            case MigrationMeterCnc :
                // cnc 테이블에서 raw data 마이그레이션
                $command = new MigrationMeterCnc();
                break;
            case MigrationMeterCopy :
                //  meter 데이터를  다른데로 복사
                $command = new MigrationMeterCopy();
                break;
        }

        return $command;
    }
}