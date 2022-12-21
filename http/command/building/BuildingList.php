<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class BuildingList
 */
class BuildingList extends Command
{
    /**
     * BuildingList constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * BuildingList destructor.
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
        $rBuildingListQ = $this->emsQuery->getBuildingAllList();
        $buildingData = $this->query($rBuildingListQ);

        $data['building_data'] = $this->getTransData($buildingData);

        $this->data = $data;
        return true;
    }

    /**
     * 암호화 된 데이터를 복호화 시킴
     *
     * @param array $data
     *
     * @return array
     */
    private function getTransData(array $data) : array
    {
        $fcData = $data;

        for ($fcIndex = 0; $fcIndex < count($data); $fcIndex++) {
            $value = $data[$fcIndex]['name'];
            $fcData[$fcIndex]['name'] = Utility::getInstance()->updateDecryption($value);
        }

        return $fcData;
    }
}