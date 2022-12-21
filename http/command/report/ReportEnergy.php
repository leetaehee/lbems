<?php
namespace Http\Command;

use EMS_Module\Utility;

use EMS_Module\Usage;

/**
 * Class ReportEnergy 조회
 */
class ReportEnergy extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * ReportEnergy constructor.
     */
	public function __construct() 
	{
		parent::__construct();

        $this->usage = new Usage();
	}

    /**
     * ReportEnergy destructor.
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
     * @return bool|null
     *
     * @throws \Exception
     */
	public function execute(array $params) :? bool
	{
        $data = [
           	'data' => [],
           	'price' => []
        ];

        $sensors = [];
        $keySensors = [];
        $keySensor = [];
        $addOptions = [];

        $usage = $this->usage;

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $this->sensorObj = $this->getSensorManager($complexCodePk);

		$option = isset($params[0]['value']) === true ? $params[0]['value'] : '0';
		$dateType = isset($params[1]['value']) === true ? $params[1]['value'] : '0';
		$date = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : '0000';
		$floor = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : '';
		$room = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : '';
		$energyKey = isset($params[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']) : '';
		$dong = isset($params[6]['value']) === true ? Utility::getInstance()->removeXSS($params[6]['value']) : '';

        $sensor = '';
        $solarType = '';

		if ($option === 11) {
			// 태양광일 때 발전량만 조회
			$solarType = 'I';
		}

		if ($option === 0) {
		    // 건물 위치 센서 조회
            $sensor = $usage->getBuildingLocationSensor($complexCodePk, $dong, $floor, $room);
        }

        if (is_null($this->sensorObj) === false) {
            $keySensors = $this->sensorObj->getSpecialSensorKeyName();
            if (is_null($keySensors[$energyKey]) === false) {
                // 설비별 및 에너지 키 이름 & 배열로 넘어와 조회 하는 경우
                $keySensor = $keySensors[$energyKey];
            }
        }

        $energyKey = ($option === 11) ? 'solar_in' : $energyKey;

        if (count($keySensors) > 0 && count($keySensor) > 0) {
            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'solar_type' => $solarType,
                'is_use_next_date' => false,
                'energy_name' => $energyKey,
            ];
            $data = $usage->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $date, $addOptions, $keySensor);
        } else {
            $addOptions = [
                'dong' => $dong,
                'floor' => $floor,
                'room' => $room,
                'solar_type' => $solarType,
                'is_use_next_date' => false,
                'sensor' => $sensor,
                'energy_name' => $energyKey,
            ];
            $data = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
        }

        $customUnits = $this->sensorObj->getCustomUnit();

		$this->data = [
		    'data' => $data['data'],
            'price' => $data['price'],
            'custom_unit' => $customUnits[$energyKey]
        ];

		return true;
	}
}