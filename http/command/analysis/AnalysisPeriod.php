<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class AnalysisPeriodMdmt 비교분석
 */
class AnalysisPeriod extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

	/**
	 * AnalysisPeriod constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->usage = new Usage();
	}

	/**
	 * AnalysisPeriod destructor.
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
        $this->sensorObj = $this->getSensorManager($complexCodePk);

		$dateType = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
		$floor = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : 'all';
		$room = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : 'all';
		$selectEnergy = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : '';
		$selectUsage = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : '';
		$selectFacility = isset($params[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']): '';
		$dong = isset($params[6]['value']) === true ? Utility::getInstance()->removeXSS($params[6]['value']) : 'all';

        $date = $this->baseDate;
        if ($dateType === 2) {
            $date = Utility::getInstance()->addDay($date, -1);
        }

        $date = $usage->getDateByOption($date, $dateType);

        // 에너지원별
        $energys = $this->getAnalysisData($complexCodePk, $date, $dateType, $dong, $floor, $room, 'energy', $selectEnergy);
        // 용도별
        $usages = $this->getAnalysisData($complexCodePk, $date, $dateType, $dong, $floor, $room, 'usage', $selectUsage);
        // 설비별
        $facilities = $this->getAnalysisData($complexCodePk, $date, $dateType, $dong, $floor, $room, 'facility', $selectFacility);
        // 커스텀 단위
        $customUnits = $this->sensorObj->getCustomUnit();

        // 뷰에 보여질 데이터 전달
        $data = [
            'energy' => $energys,
            'usage' => $usages,
            'facility' => $facilities,
            'custom_units' => $customUnits,
        ];
		$this->data = $data;

		return true;
	}

    /**
     * 데이터 조회
     *
     * @param string $complexCodePk
     * @param string $date
     * @param string $dateType
     * @param string $dong
     * @param string $floor
     * @param string $room
     * @param string $group
     * @param string $value
     *
     * @return array|null
     *
     * @throws \Exception
     */
	private function getAnalysisData(string $complexCodePk, string $date, string $dateType, string $dong, string $floor, string $room, string $group, string $value) :? array
    {
        $fcData = [];
        $data = [];
        $keySensors = [];
        $keySensor = [];
        $addOptions = [];

        $usage = $this->usage;

        if (is_null($this->sensorObj) === true) {
            return $fcData;
        }

        // 에너지원, 용도별, 설비별 키 정보 조회
        $analysisData = $this->sensorObj->getEnergyPartData();
        if (count($analysisData) === 0) {
            return null;
        }

        if (count($analysisData[$group]) === 0) {
            return $fcData;
        }

        // 에너지원 번호 조회
        $option = (int)$analysisData[$group][$value]['option'];

        $sensor = '';
        if ($option === 0) {
            // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        }

        // 키 네임으로 센서 정보 찾고자 하는 경우
        $keySensors = $this->sensorObj->getSpecialSensorKeyName();
        if (is_null($keySensors[$value]) === false) {
            $keySensor = $keySensors[$value];
        }

        $tempDate = $usage->getLastDate($date, $dateType);

        $addOptions = [
            'dong' => $dong,
            'floor' => $floor,
            'room' => $room,
            'energy_name' => $value,
        ];

        if (count($keySensors) > 0 && count($keySensor) > 0) {
            $data['now'] = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $date, $addOptions, $keySensor);
            $data['last'] = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $tempDate, $addOptions, $keySensor);
        } else {
            $addOptions['sensor'] = $sensor;
            $data['now'] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            $data['last'] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $tempDate, $addOptions);
        }

        $fcData[$value] = [
            'now' => $data['now'],
            'last' => $data['last'],
            'option' => $option
        ];

        return $fcData;
    }
}
