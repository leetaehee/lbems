<?php
namespace Http\Command;

/**
 * Class BuildingManager
 */
class BuildingManager extends Command
{
	/**
	 * BuildingManager constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * BuildingManager destructor.
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
            'building_info' => [],
        ];

        $ss_complex_pk = $_SESSION['ss_complex_pk'];
		$option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $energyKey = isset($params[1]['value']) === true ? $params[1]['value'] : '';
        $complexCodePk = isset($params[2]['value']) === true ? $params[2]['value'] : '';

        $complexCodePk = empty($complexCodePk) === true ? $this->getSettingComplexCodePk($ss_complex_pk) : $complexCodePk;

        // 센서정보 조회 하기 위해 정보 생성
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 건물정보 조회
        $data['building_info'] = $this->getBuildingInfoData($complexCodePk, $option, $energyKey);

        // 뷰에 데이터 바인딩
		$this->data = $data;
		return true;
	}

    /**
     * 건물정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     * @param string $energyKey
     *
     * @return array
     *
     * @throws \Exception
     */
	private function getBuildingInfoData(string $complexCodePk, int $option, string $energyKey) : array
    {
        $fcData = [];

        $floorString = '';
        $query = '';

        $isElectricOption = $option === 0 && $energyKey === 'electric' ? true : false;

        $query = $this->emsQuery->getQueryBuildingInfoSensor($complexCodePk, $option, $floorString);

        if (is_int($option) === true && $option !== 999 && $isElectricOption === false) {
            $floorString = $this->getFloorQuery($energyKey, $option);
            $query = $this->emsQuery->getQueryBuildingInfoSensor($complexCodePk, $option, $floorString);
        } else if ($isElectricOption === false) {
            $query = $this->emsQuery->getQueryComplexInfoAll($complexCodePk);
        }

        if (empty($query) === false) {
            $fcData = $this->query($query);
        } else {
            $electricSensors = $this->sensorObj->getElectricFloorSensor();
            if (count($electricSensors) > 1) {
                foreach ($electricSensors as $k => $v) {
                    if ($k === 'all') {
                        continue;
                    }

                    $fcData[] = [
                        'home_dong_pk' => '',
                        'home_ho_pk' => '',
                        'home_ho_nm' => '',
                        'home_grp_pk' => $k,
                        'home_dong_cnt' => 1,
                    ];
                }
            }
        }

        return $fcData;
    }

    /**
     * 센서별로 검색 하는 경우 해당 센서에 층만 보이도록 검색 쿼리 생성
     *
     * @param string $energyKey
     * @param int $option
     *
     * @return string
     */
	private function getFloorQuery(string $energyKey, int $option) : string
    {
        $fcFloorKeyString = '';
        $keys = $keySensors = [];

        if (is_null($this->sensorObj) === false) {
            $keySensors = $this->sensorObj->getSpecialSensorKeyName();
        }

        if (is_null($keySensors[$energyKey]) === false) {
            $keys = array_keys($keySensors[$energyKey]);

            for ($fcIndex = 0; $fcIndex < count($keys); $fcIndex++) {
                if ($fcIndex === 0) {
                    $fcFloorKeyString = "'" . $keys[$fcIndex] . "'";
                } else {
                    $fcFloorKeyString .= ",'" . $keys[$fcIndex] . "'";
                }
            }
        }

        return $fcFloorKeyString;
    }
}