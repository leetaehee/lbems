<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Usage;
use EMS_Module\Finedust;
use EMS_Module\Indication;
use EMS_Module\Utility;

/**
 * Class AnalysisTotal 종합분석
 */
class AnalysisTotal extends Command
{
    /** @var Usage|null $usage 사용량 | 요금  */
    private ?Usage $usage = null;

    /** @var Finedust|null $finedust 온습도 */
    private ?Finedust $finedust = null;

    /** @var Indication|null $indication 자립률 */
    private ?Indication $indication = null;

	/**
	 * AnalysisTotal constructor.
	 */
	public function __construct()
	{
		parent::__construct();

        $this->usage = new Usage();
        $this->finedust = new Finedust();
        $this->indication = new Indication($this);
	}

	/**
	 * AnalysisTotal destructor.
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool
     *
     * @throws \Exception
     */
	public function execute(array $params) :? bool
	{
        $data = [];
        $energyGroup = [];
        $d = [];

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
		$dateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
		$date = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : '0000-00-00';
        $floor = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 'all';
        $room = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : 'all';
        $dong = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : 'all';

		$tempDate = $date;

        // 주기에 따른 조회 날짜 출력
        $periods = $this->getPeriod($tempDate, $dateType);
        $startDate = $periods['start_date'];
        $endDate = $periods['end_date'];

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 분석 > 종합분석에서 에너지 사용현황 조회
        $energyGroup = $this->getAnalysisTotalUseData($complexCodePk, $dateType, $startDate, $endDate, $dong, $floor, $room);

        // 용도별 사용분포도 조회
        $distributionData = $this->getUsageDistributionData($complexCodePk, $floor, $energyGroup);
        if ($distributionData === false) {
            $data['Error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $data = [
            'energy_group' => $energyGroup,
            'usages' => $distributionData,
            'params' => $params,
        ];

        if (in_array($complexCodePk, Config::FINEDUST_SENSOR_USE_GROUP) === true) {
            // 분석 > 종합분석에서 온습도 조회
            $finedustGroup = $this->getAnalysisFinedustUseData($complexCodePk, $dateType, $tempDate);

            $data['finedusts'] = [
                'temperatures' => $finedustGroup['temperatures'],
                'humiditys' => $finedustGroup['humiditys']
            ];
        }
        
        // 뷰에 데이터 바인딩
		$this->data = $data;
		return true;
	}

    /**
     * 분석 > 종합분석에서 에너지 사용현황 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $startDate
     * @param string $endDate
     * @param string $dong
     * @param string $floor
     * @param string $room
     *
     * @return array
     *
     * @throws \Exception
     */
	private function getAnalysisTotalUseData(string $complexCodePk, int $dateType, string $startDate, string $endDate, string $dong, string $floor, string $room) : array
    {
        $fcData = [];
        $addOptions = [];

        $obj = $this->usage;

        $energyPartData = $this->sensorObj->getEnergyPartData();
        $separatedData = $this->sensorObj->getSpecialSensorKeyName();

        foreach ($energyPartData as $group => $groupData) {
            foreach ($groupData as $key => $values) {

                if ($key !== 'electric'
                    && in_array($key, Config::SPECIAL_ENERGY_DATA) === true) {
                    continue;
                }

                $option = (int)$values['option'];
                $sensor = '';

                if ($option === 0) {
                    // 건물 위치 센서 조회
                    $sensor = $this->usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
                }

                $addOptions = [
                    'dong' => $dong,
                    'floor' => $floor,
                    'room' => $room,
                    'sensor' => $sensor,
                    'energy_name' => $key,
                    'is_use_next_date' => false,
                    'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData),
                ];

                if ($dateType === 1) {
                    // 월은 1~말일로 조회
                    $addOptions['start_date'] = $startDate;
                    $addOptions['end_date'] = $endDate;
                }

                $d = $obj->getUsageSumData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);

                if ($key === 'electric_ghp') {
                    $key = 'gas';
                }

                if (count($d) === 0) {
                    continue;
                }

                $fcData[$group][$key] = $d['current'];
                $fcData[$group][$key]['option'] = $option;
            }
        }

        $facilityData = is_null($fcData['facility']) === true ? [] : $fcData['facility'];
        $fcData = $this->addItem('power_train', $fcData, $facilityData);

        // 태양광 정보 가져오기
        $solarKeys = $this->sensorObj->getSolarSensor();
        $solarOption = 11;

        // 발전량 조회
        $addOptions = [
            'solar_type' => 'I',
            'sensor' => $solarKeys['in'],
            'is_use_next_date' => false,
        ];

        if ($dateType === 1) {
            $addOptions['start_date'] = $startDate;
            $addOptions['end_date'] = $endDate;
        }

        $d = $obj->getUsageSumData($this, $complexCodePk, $solarOption, $dateType, $startDate, $addOptions);

        $fcData['solar']['in'] = $d['current'];

        // 소비량
        $addOptions['solar_type'] = 'O';
        $addOptions['sensor'] = $solarKeys['out'];

        $d = $obj->getUsageSumData($this, $complexCodePk, $solarOption, $dateType, $startDate, $addOptions);

        $fcData['solar']['out'] = $d['current'];
        $fcData['solar']['option'] = 11;

        return $fcData;
    }

    /**
     * 분석 > 종합분석에서 온습도 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $date
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getAnalysisFinedustUseData(string $complexCodePk, int $dateType, string $date) : array
    {
        $fcData = [];

        $obj = $this->finedust;

        $fcData['temperatures'] = $obj->getFinedustData($this, $complexCodePk, $dateType, $date, 'temperature');
        $fcData['humiditys'] = $obj->getFinedustData($this, $complexCodePk, $dateType, $date, 'humidity');

        return $fcData;
    }

    /**
     * 용도별 사용 분포도 조회
     *
     * @param string $complexCodePk
     * @param string $floor
     * @param array $data
     *
     * @return array
     */
    private function getUsageDistributionData(string $complexCodePk, string $floor, array $data) : array
    {
        $fcData = [];

        $indication = $this->indication;
        $electricBuildingExceptSolarInfo = Config::ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO;

        $electricUsed = 0;
        $solarUsed = 0;

        // 전기 사용량
        if (isset($data['energy']['electric']['data']) === false) {
            $electricUsed = 0;
        } else {
            $electricUsed = (int)$data['energy']['electric']['data'];
        }

        // 태양광 소비량
        if (isset($data['solar']['out']['data']) === false) {
            $solarUsed = 0;
        } else {
            $solarUsed = (int)$data['solar']['out']['data'];
        }

        // 전체전기 + 태양광 소비량
        $buildingAllElectric = $electricUsed;
        if (in_array($complexCodePk, $electricBuildingExceptSolarInfo) === false) {
            $buildingAllElectric += $solarUsed;
        }

        $usageData = $data['usage']; // 용도별 추출
        foreach ($usageData as $key => $used) {
            if (isset($used['data']) === false) {
                continue;
            }

            $useDistribution = $indication->getUseDistribution($used['data'], $buildingAllElectric);
            $fcData[$key] = $useDistribution;
        }

        return $fcData;
    }

    /**
     * 에너지원 항목 추가
     *
     * @param string $addItemKey
     * @param array $originItems
     * @param array $addItems
     *
     * @return array
     */
    private function addItem(string $addItemKey, array $originItems, array $addItems) : array
    {
        $fcData = $originItems;

        if ($addItemKey === 'power_train'
            && (array_key_exists('feed_pump', $addItems) === true && array_key_exists('sump_pump', $addItems) === true)) {
            // 급수펌프,배수펌프 존재 할경우  동력 사용량 = 급수 + 배수
            $fcData = $originItems;

            $feedPump = $addItems['feed_pump']['data'];
            $sumpPump = $addItems['sump_pump']['data'];
            $usageSum = $feedPump + $sumpPump;

            $fcData['usage']['power_train']['data'] = $usageSum;
            $fcData['usage']['power_train']['price'] = 0; // 해당 메뉴에서 금액은 조회하지 않아 0으로..
            $fcData['usage']['power_train']['option'] = 0;
        }

        return $fcData;
    }

    /**
     * 주기에 따른 검색 날짜 조회
     *
     * @param string $date
     * @param int $dateType
     *
     * @return array $fcData
     */
    private function getPeriod(string $date, int $dateType) : array
    {
        $fcData = [];

        switch ($dateType)
        {
            case 0:
                // 금년
                $endMonth = date('Y-m', strtotime($date));
                $temp = explode('-', $endMonth);

                // 시작(월)~종료(월)
                $startDate = $temp[0];
                $endDate = $temp[0];

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                break;
            case 1:
            case 6:
                // 금월
                $date = date('Y-m-d', strtotime($date));
                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));

                $fcData = [
                    'start_date' => $today,
                    'end_date' => $today
                ];
                break;
        }

        return $fcData;
    }
}
