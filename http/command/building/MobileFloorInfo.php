<?php
namespace Http\Command;

/**
 * Class MobileFloorInfo
 */
class MobileFloorInfo extends Command
{
    /**
     * MobileFloorInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MobileFloorInfo FloorInfo.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수.
     *
     * @param array $params
     *
     * @return bool|null
     */
    public function execute(array $params) :? bool
    {
        $data = [];
        $sessionComplexPk = $_SESSION['mb_ss_complex_pk'];

        // 건물별 정보를 가져오기 위해 정보 셋팅
        $this->sensorObj = $this->getSensorManager($sessionComplexPk);

        // 층, 룸별 데이터 조회
        $floorData = $this->getFloorData();
        if ($floorData === null) {
            $data['error'] = 'Error';
            $this->data = $data;
            return true;
        }

        $data['floor_data'] = $floorData;

        $this->data = $data;
        return true;
    }

    /**
     * 층, 룸별 데이터 조회
     *
     * @return array
     */
    private function getFloorData() :? array
    {
        $fcData = $fcFloorData = [];

        $fcFloorData = $this->sensorObj->getControlDeviceInfo();
        if (count($fcFloorData) === 0) {
            return null;
        }

        foreach ($fcFloorData as $key => $items) {
            $fcData[$key] = array_keys($items);
        }
        return $fcData;
    }
}