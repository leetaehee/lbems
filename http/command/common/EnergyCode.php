<?php
namespace Http\Command;

use EMS_Module\Config;

/**
 * Class EnergyCode
 */
class EnergyCode extends Command
{
    /**
     * EnergyCode constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * EnergyCode destructor.
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

        $ss_complex_pk = $_SESSION['ss_complex_pk'];
        $type = isset($params[0]['value']) === true ? $params[0]['value'] : '';
        $complexCodePk = isset($params[1]['value']) === true ? $params[1]['value'] : $ss_complex_pk;
        $isTypeFilter = isset($params[2]['value']) === true ? $params[2]['value'] : false;


        if (empty($complexCodePk) === true) {
            $data['Error'] = true;

            $this->data = $data;
            return true;
        }

        // 센서정보 조회를 위한 값 할당
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        // 뷰에 보여줄 데이터 바인딩
        $this->data['energy_code'] = $this->getEnergyCode($type, $isTypeFilter);
        return true;
    }

    /**
     * 에너지 코드 조회
     *
     * @param string $type
     * @param bool $isTypeFilter
     * @return array
     */
    private function getEnergyCode(string $type, bool $isTypeFilter) : array
    {
        $fcData = [];

        $separatedEnergyTypes = Config::SEPARATED_ENERGY_TYPES;
        $energyPartData = $this->sensorObj->getEnergyPartData();

        if (empty($type) === true) {
            return $energyPartData;
        }

        if ($isTypeFilter === false) {
            return $energyPartData[$type];
        }

        foreach ($energyPartData[$type] AS $key => $values) {

            if (in_array($key, $separatedEnergyTypes) === true) {
                continue;
            }

            $fcData[$key] = $values;
        }

        return $fcData;
    }
}