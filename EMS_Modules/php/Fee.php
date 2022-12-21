<?php
namespace EMS_Module;

use Http\Command\Command;
use EMS_Module\Utility;

use Module\FileCache;

/**
 * Class Fee 요금
 */
class Fee
{
    /** @var Command|null $command  */
    private ?Command $command = null;

    /** @var EMSQuery|null $emsQuery */
    private ?EMSQuery $emsQuery = null;

    /** @var bool|true $isUseDateKey */
    private bool $isUseDateKey = true;

    /** @var bool|false $isArea */
    private bool $isArea = false;

    /** @var array $caches */
    private array $caches = [];

    /**
     * Fee constructor.
     */
    public function __construct()
    {
        $this->emsQuery = new EMSQuery();

        $fileCache = new FileCache('kepco_info', 'complex');
        $this->caches = $fileCache->cacheLoad();
    }

    /**
     * Fee destructor.
     */
    public function __destruct()
    {
    }

    /**
     * property 초기화
     *
     * @param Command $command
     * @param bool $isDateKey
     * @param bool $isArea
     */
    private function init(Command $command, bool $isDateKey, bool $isArea)
    {
        $this->command = $command;
        $this->isUseDateKey = $isDateKey;
        $this->isArea = $isArea;
    }

    /**
     * 요금 조회
     *
     * @param Command $command
     * @param string $dateKey
     * @param int $usage
     * @param int $option
     * @param string $energyName
     * @param string $complexCodePk
     * @param string $date
     * @param int $dateType
     * @param bool $isDateKey
     * @param bool $isArea
     *
     * @return float
     *
     * @throws \Exception
     */
    public function getPrice(Command $command, string $dateKey, int $usage, int $option, string $energyName, string $complexCodePk, string $date, int $dateType, bool $isDateKey, bool $isArea) : float
    {
        $currentFee = 0.0;

        $this->init($command, $isDateKey, $isArea);

        $tempUsage = $this->getUnitUsed($option, $usage);
        if ($tempUsage < 1) {
            return (double)$currentFee;
        }

        $option = Utility::getInstance()->getChangedOption($option, $energyName);
        if ($option === 1 || $option === 2) {
            $currentFee = $this->calcPrice($usage, $complexCodePk, $option);
            return $currentFee;
        }

        if (Config::FEE_METHOD === 'Library') {
            $currentFee = $this->getElectricPriceByLibrary($complexCodePk, $option, $dateType, $date, $dateKey, $usage);
            return $currentFee;
        }

        $currentFee = $this->getElectricPrice($usage, $complexCodePk, $date);

        return $currentFee;
    }

    /**
     * 단위 환산 했을 때 값 조회
     *
     * @param int $option
     * @param int $usage
     *
     * @return int
     */
    private function getUnitUsed(int $option, int $usage) : int
    {
        $divisors = Config::DIVISOR_VALUES;

        if ($this->isArea === true) {
            return $usage;
        }

        return $usage/$divisors[$option];
    }


    /**
     * 전기 요금 조회
     *
     * @param int $usage
     * @param string $complexCodePk
     * @param string $date
     *
     * @return float
     *
     * @throws \Exception
     */
    private function getElectricPrice(int $usage, string $complexCodePk, string $date) : float
    {
        $price = 0;

        $selQuery = $this->emsQuery->getQueryElecCost($complexCodePk, $date);
        $elecCost = $this->command->query($selQuery);

        if (count($elecCost) <= 0 || count($elecCost[0]) <= 0) {
            return $price;
        }

        $price = $this->calcElecPrice($usage, $elecCost);

        /*
            if ($dateType == 0) {
                // year
                if($usage > 0){
                }else{
                    $price = 0;
                }
            } else {
                $price = $this->calcElecPriceOnlyUsed($usage, $elecCost);
            }
        */

        return (double)$price;
    }

    /**
     * 요금 계산 (전기 제외)
     *
     * @param int $usage
     * @param string $complexCodePk
     * @param int $option
     *
     * @return float
     *
     * @throws \Exception
     */
    public function calcPrice(int $usage, string $complexCodePk, int $option) : float
    {
        $unitCost = 0;

        $query = $this->emsQuery->getQueryCost($complexCodePk, $option);
        $data = $this->command->query($query);

        if (count($data) <= 0 || count($data[0]) <=0 ){
            return $unitCost;
        }

        $unitCostFloat = (float)($data[0]['unit_cost']);

        return round($usage * $unitCostFloat);
    }

    /**
     * 요금제 라이브러리 적용하기 위한 시작일 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $dateKey
     * @param int $usage
     *
     * @return float
     *
     * @throws \Exception
     */
    private function getElectricPriceByLibrary(string $complexCodePk, int $option, int $dateType, string $date, string $dateKey, int $usage) : float
    {
        $infos = $this->getElectricPriceInfo($complexCodePk);

        $divisors = Config::DIVISOR_VALUES;
        $divisor = $divisors[$option];

        $price = 0;
        $usage = $usage/$divisor; // Wh -> kWh 변환

        // 시작일,종료일 조회
        $dateInfo = $this->getStartAndEndDate($complexCodePk, $option, $date, $dateKey, $dateType);
        $startDate = $dateInfo['start'];
        $isDefaultPrice = $this->getIsDefaultPrice($dateType, $startDate);

        if (is_string($startDate) === true && strlen($startDate) > 8) {
            $startDate = substr($startDate, 0, 8);
        }

        $params = [
            'electricType' => $infos['electricType'],
            'typeGubun' => $infos['typeGubun'],
            'typeGubun2' => $infos['typeGubun2'],
            'typeSelect' => $infos['typeSelect'],
            'contractUseVal' => $infos['contractUseVal'],
            'startDate' => $startDate,
            'endDate' => date('Y-m-d', strtotime($dateInfo['end'])),
            'useValue' => $usage,
            'elecDefaultType' => $isDefaultPrice,
        ];

        $electricPrice = new \ElectricPrice();
        $price = $electricPrice->priceInfo($params);

        $price = $usage <= 0 ? 0 : $price; // 사용량이 0이면 요금 0으로 처리..
        $price = $price < 0 ? 0 : $price; // 마이너스인 경우 0처리..

        return (double)$price;
    }

    /**
     * 요금 라이브러리 적용 시 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getElectricPriceInfo(string $complexCodePk) : array
    {
        $fcCaches = $this->caches[$complexCodePk];

        if (is_array($fcCaches) === false) {
            $fcCaches = [];
        }

        if (count($fcCaches) < 1) {
            // db에서 요금 정보 조회
            $rKepcoPriceinfoQ = $this->emsQuery->getQuerySelectComplexPriceInfo($complexCodePk);
            $fcCaches = $this->command->query($rKepcoPriceinfoQ);
            return $fcCaches[0]; // 0번째 항목으로 덮어씌우기..
        }

        return $fcCaches;
    }

    /**
     * 요금 라이브러리 산업용 사용 시, 기본요금 반영 여부 조회
     *
     * @param int $dateType
     * @param string $date
     *
     * @return string
     */
    private function getIsDefaultPrice(int $dateType, string $date) : string
    {
        $isUseDateKey = $this->isUseDateKey;
        $isArea = $this->isArea;

        $isDefaultPrice = 'Y'; // 디폴트는 요금 라이브러의 기본요금을 포함한다.

        if ($isArea === true) {
            // 단위 면적 적용 시에는 기본요금 제외
            $isDefaultPrice = 'N';
            return $isDefaultPrice;
        }

        if ($isUseDateKey === true
            && strlen($date) === 8
            && $dateType > 1) {
            $isDefaultPrice = 'N';
            return $isDefaultPrice;
        }

        return $isDefaultPrice;
    }

    /**
     * 전력 라이브러리 사용 하기 위해  시작일, 종료일 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $date
     * @param string $dateKey
     * @param int $dateType
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getStartAndEndDate(string $complexCodePk, int $option, string $date, string $dateKey, int $dateType) : array
    {
        $fcData = [
            'start' => $date,
            'end' => $date,
        ];

        $isDateKey = (bool)$this->isUseDateKey;

        switch ($dateType) {
            case 0:
                // 금년
                $date = $isDateKey === false ? "{$date}01" : $dateKey;
                $date .= '01';

                $rDueDayQ = $this->emsQuery->getDueday($option, $complexCodePk);
                $d = $this->command->query($rDueDayQ);

                $temps = Utility::getInstance()->getDateFromDueday($d[0]['closing_day'], $date);

                $fcData['start'] = $temps['start'];
                $fcData['end'] = $temps['end'];

                /*
                    $rStartDueQ = $this->emsQuery->getQuerySelectStartDueDate($complexCodePk, $option, $date);
                    $dates = $this->command->query($rStartDueQ);
                    $startDate = $dates[0]['st_date'];
                */
                break;
            case 1:
                // 금월
                if ($isDateKey === false) {
                    // 마감일 다음날부터 요금 조회하도록 수정..
                    $rClosingQ = $this->emsQuery->getDueday($option, $complexCodePk);
                    $data = $this->command->query($rClosingQ);

                    $closingDay = (int)$data[0]['closing_day'];
                    if ($closingDay === 99) {
                        $tempDate = date('Ymd', strtotime($date . '01'));
                        $closingDay = date('t', strtotime($tempDate));
                    }

                    $date = "{$date}{$closingDay}";
                } else {
                    $date = $dateKey;
                }


                //$startDate = date('Ymd', strtotime($date . '+1 day'));
                $startDate = date('Ymd', strtotime($date));

                $fcData['start'] = $startDate;
                $fcData['end'] = $startDate;
                break;
            case 3:
                // 시간
                if (strlen($date) > 8) {
					$date = substr($date, 0, 8);

                    $fcData['start'] = $date;
                    $fcData['end'] = $date;
                }
        }

        /*
            if (empty($startDate) === true) {
                $startDate = $date;
            }
        */

        return $fcData;
    }

    /**
     * 요금 계산
     *
     * @param int $usage
     * @param array $unitCost
     *
     * @return int
     */
    private function calcElecPrice(int $usage, array $unitCost) : int
    {
        if (empty($usage) === true) {
            return 0; // kwh 확인해야 함 ......
        }

        $len = count($unitCost);
        $temp = (float)0.0;
        $total = (float)0.0;
        $basic = 0;
        $price = 0;

        if ($usage >= 1000) {
            $usage = floor($usage / 1000); //kWh 단위로, 소수점은 버림
        } else {
            // 1000 Wh (1 kWh) 일 때는 0으로 처리
            $usage = 0;
        }

        for ($i = 0; $i < $len; $i++) {
            $unitUsage = $unitCost[$i]['USED'];
            $basicRate = $unitCost[$i]['BASE_PRICE'];
            $price = $unitCost[$i]['UNIT_COST'];
            $basic = $basicRate;

            if ($unitUsage >= $usage) {
                $total += ($usage - $temp) * $price;
                $temp += ($usage - $temp);
                break;
            } else {
                $total += ($unitUsage - $temp) * $price;
                $temp += ($unitUsage - $temp);
            }
        }

        if ($usage - $temp > 0) {
            $total += ($usage - $temp) * $price;
        }

        if ($usage <= 200) {
            $total -= 4000;
        }

        $total = $total + (int)$basic;

        if ($total < 1000) {
            $total = 1000;
        }

        $temp1 = round($total * 0.1); //부가가치세(원미만 4사 5입)
        $temp2 = floor(($total * 0.037) / 10) * 10; //전력산업기반기금(10원미만 절사)
        $total = $total + $temp1 + $temp2;
        $total = floor($total / 10) * 10; //(10원미만 절사)

        return $total;
    }
}