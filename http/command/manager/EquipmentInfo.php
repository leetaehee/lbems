<?php
namespace Http\Command;

/**
 * Class EquipmentInfo 장비관리 팝업 조회
 */
class EquipmentInfo extends Command
{
    /**
     * EquipmentInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * EquipmentInfo destructor.
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
    public function execute(array $params): ?bool
    {
        $data = [];

        $sensorNo = isset($params[0]['value']) === true ? $params[0]['value'] : '';
        $option = isset($params[1]['value']) === true ? $params[1]['value'] : 0;

        if (empty($sensorNo) === true) {
            $data['error'] = 'dataError';

            $this->data = $data;
            return true;
        }

        $data['equipment_data'] = $this->getEquipmentDataById($sensorNo, $option);

        $this->data = $data;
        return true;
    }

    /**
     * 장비 상세 데이터 조회
     *
     * @param string $sensorNo
     * @param int $option
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEquipmentDataById(string $sensorNo, int $option) : array
    {
        $fcData = [];

        $rEquipmentQ = $this->emsQuery->getQueryEquipmentDataBySensorNo($option, $sensorNo);
        $rEquipmentData = $this->query($rEquipmentQ);

        $fcData = $rEquipmentData[0];

        if (count($fcData) < 1) {
            $fcData = [];
        }

        return $fcData;
    }
}