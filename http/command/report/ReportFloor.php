<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Usage;

/**
 * Class ReportFloor
 */
class ReportFloor extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * ReportFloor constructor.
     */
	public function __construct()
    {
		parent::__construct();

        $this->usage = new Usage();
	}

    /**
     * ReportFloor destructor.
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
		$data = [];
		$complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
		$option = isset($params[0]['value']) == true ? $params[0]['value'] : '0';
		$chart = isset($params[1]['value']) == true ? $params[1]['value'] : '0';
		$date = isset($params[2]['value']) == true ? Utility::getInstance()->removeXSS($params[2]['value']) : '0000';

        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 층별 사용량 조회
        $data = $this->getReportFloorData($complexCodePk, $option, $chart, $date);

        // 뷰에 데이터 바인딩
		$this->data = $data;

		return true;
	}

    /**
     * 층별 데이터 조회
     * 
     * @param string $complexCodePk
     * @param int $option
     * @param int $dateType
     * @param string $date
     *
     * @return array
     *
     * @throws \Exception
     */
	private function getReportFloorData(string $complexCodePk, int $option, int $dateType, string $date) : array
    {
        $fcData = [];
        $floorSensors = [];
        $addOptions = [];

        $usage = $this->usage;

        if ($option === 0 && is_null($this->sensorObj) === false) {
            $floorSensors = $this->sensorObj->getElectricFloorSensor();
        }

        if (count($floorSensors) === 0) {
            // 층별 센서정보 미정으로 되어있는 경우
            $floors = $this->sensorObj->getFloorInfo();
            for ($i = 0; $i < count($floors); $i++) {
                $tempFloor = $floors[$i];

                $addOptions = [
                    'floor' => $tempFloor,
                    'is_use_next_date' => false,
                    'energy_name' => 'electric', // 전기만 사용하는 메뉴라서..
                ];
                $fcData[$tempFloor] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
            }
        }

        foreach ($floorSensors as $floor => $value) {
            if ($floor === 'all') {
                continue;
            }

            $addOptions = [
                'floor' => $floor,
                'sensor' => $floorSensors[$floor]['all'],
                'is_use_next_date' => false,
                'energy_name' => 'electric',
            ];
            $fcData[$floor] = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $date, $addOptions);
        }

        return $fcData;
    }
}