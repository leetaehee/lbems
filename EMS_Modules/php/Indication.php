<?php
namespace EMS_Module;

use Http\Command\Command;
use Http\SensorManager;

/**
 * Class Indication
 */
class Indication
{
    /** @var Command|null $command */
    private ?Command $command = null;

    /** @var EmsQuery|null $emsQuery */
    private ?EMSQuery $emsQuery = null;

    /** @var Usage|null $usage */
    private ?Usage $usage = null;

    /** @var SensorManager|null $sensorManager */
    private ?SensorManager $sensorManager = null;

    /** @var float $scalingFactor 환산계수 */
    private float $scalingFactor = 2.75;

    /**
     * Indication constructor.
     *
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->emsQuery = new EMSQuery();
        $this->usage = new Usage();
        $this->sensorManager = new SensorManager();
    }

    /**
     * Indication destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 온실가스 배출량 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $date
     *
     * @return float
     *
     * @throws \Exception
     */
    public function getCo2Emission(string $complexCodePk, int $dateType, string $date): float
    {
        $value = 0;

        $date = empty($date) === true ? date('Ymd') : $date;

        // 일 도시가스/전기getUsedGas 사용량
        $usedGasVal = $this->getUsed($complexCodePk, 1, $dateType, $date, 'gas');
        $usedElectricVal = $this->getUsed($complexCodePk, 0, $dateType, $date, 'electric');

        $value = $this->calcCo2Emission($usedGasVal, $usedElectricVal);

        return $value;
    }

    /**
     * 자립률 및 등급 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $date
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getIndependencePercent(string $complexCodePk, int $dateType, string $date): array
    {
        $value = 0;

        $date = empty($date) === true ? date('Ymd') : $date;

        $usedHotwaterVal = $this->getUsed($complexCodePk, 7, $dateType, $date, 'electric_howater');
        $usedHeatingVal = $this->getUsed($complexCodePk, 8, $dateType, $date, 'electric_heating');
        $usedColdVal = $this->getUsed($complexCodePk, 4, $dateType, $date, 'electric_cold');
        $usedLightVal = $this->getUsed($complexCodePk, 3, $dateType, $date, 'electric_light');
        $usedVentVal = $this->getUsed($complexCodePk, 10, $dateType, $date, 'electric_vent');
        $usedSolarVal = $this->getUsed($complexCodePk, 11, $dateType, $date, 'solar');

        $usedData = [
            'electric_hotwater' => $usedHotwaterVal,
            'electric_heating' => $usedHeatingVal,
            'electric_cold' => $usedColdVal,
            'electric_light' => $usedLightVal,
            'electric_vent' => $usedVentVal,
            'solar' => $usedSolarVal
        ];

        $value = $this->calcIndependencePercent($complexCodePk, $usedData);

        return $value;
    }

    /**
     * 에너지 자립률 및 등급 조회
     *
     * @param string $complexCodePk
     * @param array $usedData
     * @return array
     *
     * @throws \Exception
     */
    private function calcIndependencePercent(string $complexCodePk, array $usedData): array
    {
        /**
         * 단위 면적당 생산량 = (태양광발전량 * 2.75)/건축면적
         * 단위 면적당 소비량 = (전기급탕+전기난방+전기냉방+전기조명+전기환기)*2.75/건축면적
         * (단위 면적 적용하지 않은 생산량, 소비량의 경우 건축면적 나누지 말 것)
         *
         * 1차: 에너지 등급 = 100 * (단위면적당 생산량 / (단위면적당 생산량 + 단위면적당 소비량))
         * 2차: 100 * (태양광 발전량 / 총 전기 사용량)
         *
         * 환산계수- 석유로 전기를 생산할 때 석유에 발생하는 값
         */
        $fcData = [];

        // 환산계수
        $scalingFactor = $this->scalingFactor;

        $hotwaterVal = $usedData['electric_hotwater'];
        $heatingVal = $usedData['electric_heating'];
        $coldVal = $usedData['electric_cold'];
        $lightVal = $usedData['electric_light'];
        $ventVal = $usedData['electric_vent'];
        $solarVal = $usedData['solar'];

        // 에너지등급
        $value = 0;

        // 건축면적조회
        $rBuildingAreaQ = $this->emsQuery->getQueryComplexLandArea($complexCodePk);
        $complexData = $this->command->query($rBuildingAreaQ);
        $buildingArea = $complexData[0]['building_area'];

        // 생산량
        $productionUsed = $solarVal * $scalingFactor;
        if ($productionUsed > 0) {
            $productionAreaUsed = $productionUsed / $buildingArea;
        } else {
            $productionAreaUsed = 0;
        }

        // 소비량
        $totalElectricUsed = ($hotwaterVal + $heatingVal + $coldVal + $lightVal + $ventVal);
        $consumptionUsed = $totalElectricUsed * $scalingFactor;
        if ($consumptionUsed > 0) {
            $consumptionAreaUsed = $consumptionUsed / $buildingArea;
        } else {
            $consumptionAreaUsed = 0;
        }

        if ($productionUsed > 0) {
            // 에너지 등급
            //$value =  100 * ($productionAreaUsed/($consumptionAreaUsed + $productionAreaUsed));

            // 2020.11.11 개선안 = 100 * (태양광 발전량 / 총 전기 사용량)
            $value = 0;
            if ($totalElectricUsed > 0) {
                $value = 100 * ($solarVal / $totalElectricUsed);
            }
        }

        $fcData[0] = $value; // 에너지등급
        $fcData[1] = $productionUsed; // 생산량
        $fcData[2] = $consumptionUsed; // 소비량

        return $fcData;
    }

    /**
     * 온실가스 배출량 계산
     *
     * @param int $usedGasVal
     * @param int $usedElectricVal
     *
     * @return float
     */
    private function calcCo2Emission(int $usedGasVal, int $usedElectricVal): float
    {
        $value = 0;

        $gasEmission = $usedGasVal * 38.9 * 15.272 * 44 / 12 / 1000;
        $electricEmission = $usedElectricVal * 0.424;

        $value = $gasEmission + $electricEmission;

        return $value;
    }

    /**
     * 사용량 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $energyName
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getUsed(string $complexCodePk, int $option, int $dateType , string $date, string $energyName): int
    {
        $query = '';

        $command = $this->command;
        $usage = $this->usage;

		// 전기는 한전전체전력으로 한다.
        $sensor = $this->getSensorNo($complexCodePk, $option);

        $addOptions = [
            'sensor' => $sensor,
        ];

        if (empty($energyName) === false) {
            $addOptions['energy_name'] = $energyName;
        }

        if ($dateType === 1) {
            $date = $usage->getDateByOption($date, $dateType);
            $date = $usage->getLastDate($date, $dateType);
        } elseif ($dateType === 0) {
            $date = $usage->getDateByOption($date, $dateType);
        } elseif ($dateType === 2) {
            $date = $usage->getDateByOption($date, $dateType);
            $date = $usage->getLastDate($date, $dateType);
        }

        $d = $this->usage->getUsageSumData($command, $complexCodePk, $option, $dateType, $date, $addOptions);

        return (int)$d['current']['data'];
    }

    /**
     * 에너지원/용도별/설비별에 따른 센서 번호 조회
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @return string
     */
    private function getSensorNo(string $complexCodePk, int $option): string
    {
        $fcString = '';
        $all = 'all';

        $sensorObj = $this->sensorManager->getSensorObject($complexCodePk);

        if ($option === 0) {
            // 건물 위치 센서 조회
            $fcString = $this->usage->getBuildingLocationSensor($complexCodePk, $all, $all, $all);
        }

        return $fcString;
    }

    /**
     * 사용분포 구하기
     *
     * @param string $targetColumn
     * @param string $totalColumn
     *
     * @return float
     *
     */
    public function getUseDistribution(string $targetColumn, string $totalColumn) : float
    {
        $value = 0;

        if ($totalColumn < 1) {
            $value = 0;
        } else {
            $value = (($targetColumn/$totalColumn) * 100);
        }

        return floor($value);
    }

    /**
     * 자립률에 대해 등급 조회
     *
     * @param int $value
     *
     * @return string
     */
    public function getIndependenceGrade(int $value) : string
    {
        $fcGrade = '-';

        if ($value > 0) {
            if ($value >= 20  && $value < 40) {
                $fcGrade = '5등급';
            } else if($value >= 40  && $value < 60) {
                $fcGrade = '4등급';
            } else if($value >= 60  && $value < 80) {
                $fcGrade = '3등급';
            } else if($value >= 80  && $value < 100) {
                $fcGrade = '2등급';
            } else if($value >= 100) {
                $fcGrade = '1등급';
            } else {
                $fcGrade = '0등급';
            }
        }

        return $fcGrade;
    }
}