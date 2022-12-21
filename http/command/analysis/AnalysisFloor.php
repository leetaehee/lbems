<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class AnalysisFloor
 */
class AnalysisFloor extends Command
{
    /** @var Usage|null $usage 사용량 */
    private ?Usage $usage = null;

	/**
	 * AnalysisFloor constructor.
	 */
	public function __construct()
	{
		parent::__construct();

        $this->usage = new Usage();
	}

	/**
	 * AnalysisFloor destructor.
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
        $usage = $this->usage;

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $option = isset($params[0]['value']) == true ? $params[0]['value'] : 0;
		$dateType = isset($params[1]['value']) == true ? Utility::getInstance()->removeXSS($params[1]['value']) : 0;
		$date = isset($params[2]['value']) == true ? Utility::getInstance()->removeXSS($params[2]['value']) : '0000-00-00';
		$date = str_replace('-', '', $date);
		$date = $usage->getDateByOption($date, $dateType);
		$room = 'all';

        $date = $usage->getDateByOption($date, $dateType);

        // 센서정보 조회하기 위한 객체 설정
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 층별 사용정보 조회
        $data = $this->getFloorUseData($complexCodePk, $option, $dateType, $date, $room);

        // 뷰에 데이터 바인딩
		$this->data = $data;
		return true;
	}

    /**
     * 층별 사용 정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     * @param string $room
     *
     * @return array
     *
     * @throws \Exception
     */
	private function getFloorUseData(string $complexCodePk, int $option, int $dateType, string $date, string $room) : array
    {
        $fcData = [];
        $floorSensors = [];
        $addOptions = [];
        $homeData = [];

        $usage = $this->usage;

        $this->sensorObj = $this->getSensorManager($complexCodePk);
        if ($option === 0 && is_null($this->sensorObj) === false) {
            $floorSensors = $this->getSensorManager($complexCodePk)->getElectricFloorSensor();
        }

        // 주기에 따른 조회 날짜 출력 (현재)
        $dates = $this->getPeriod($date, $dateType);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        if (count($floorSensors) === 0) {
            // 층별 센서정보 미정으로 되어있는 경우
            $floors = $this->sensorObj->getFloorInfo();
            for ($i = 0; $i < count($floors); $i++) {
                $tempFloor = $floors[$i];

                $addOptions = [
                    'floor' => $tempFloor,
                    'room' => $room,
                    'is_use_next_date' => false,
                    'energy_name' => 'electric', // 전기에 대해서 함..
                ];

                if ($dateType === 0) {
                    $fcData[$tempFloor] = $usage->getEnergyData($this, $complexCodePk, $option,$dateType, $startDate, $addOptions);
                } else {
                    $fcData[$tempFloor] = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
                }

                $addOptions = [
                    'floor' => $tempFloor,
                    'room' => $room,
                    'energy_name' => 'electric',
                ];

                if ($dateType === 1) {
                    $addOptions['start_date'] = $startDate;
                    $addOptions['end_date'] = $endDate;
                }

                $homeData = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);

                $fcData[$tempFloor]['total_info'] = [
                    'used' => $homeData['current']['data'],
                    'price' => $homeData['current']['price'],
                ];
            }
        }

        foreach ($floorSensors as $floor => $value) {
            if ($floor === 'all') {
                continue;
            }

            $floorSensor = $floorSensors[$floor]['all'];
            $addOptions = [
                'floor' => $floor,
                'sensor' => $floorSensor,
                'is_use_next_date' => false,
                'energy_name' => 'electric', // 전기에 대해서 함.
            ];

            if ($dateType === 0) {
                $fcData[$floor] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
            } else {
                $fcData[$floor] = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
            }

            $addOptions = [
                'floor' => $floor,
                'sensor' => $floorSensor,
                'energy_name' => 'electric',
            ];

            if ($dateType === 1) {
                $addOptions['start_date'] = $startDate;
                $addOptions['end_date'] = $endDate;
            }

            //$homeData = $usage->getEnergyDataByHome($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
            $homeData = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
            $fcData[$floor]['total_info'] = [
                'used' => $homeData['current']['data'],
                'price' => $homeData['current']['price'],
            ];
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
                $temp = explode('-', $date);

                // 시작(월)~종료(월)
                $startDate = $temp[0];
                $endDate = $temp[0];

                $fcData = [
                    'start' => $startDate,
                    'end' => $endDate,
                ];
                break;
            case 1:
            case 6:
                // 금월
                $date = date('Y-m-d', strtotime($date . '01'));

                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                $fcData = [
                    'start' => $startDate,
                    'end' => $endDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));

                $fcData = [
                    'start' => $today,
                    'end' => $today
                ];
                break;
        }

        return $fcData;
    }
}
