<?php
namespace http\command;

use EMS_Module\Utility;
use EMS_Modules\MobileMenu;

/**
 * Class MobileFrame
 */
class MobileFrame extends Command
{
    /** @var MobileMenu|null mobileMenu */
    private ?MobileMenu $mobileMenu = null;

    /**
     * Frame constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->mobileMenu = new MobileMenu();
    }

    /**
     * Frame destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $sessionComplexPk = $_SESSION['mb_ss_complex_pk'];
        $this->sensorObj = $this->getSensorManager($sessionComplexPk);

        // 업체 정보 조회
        $buildingData = $this->getBuildingInfoData($sessionComplexPk);

        // 메뉴정보 조회
        $menuData = $this->getMakeMenuData($sessionComplexPk);

        // 뷰에 데이터 바인딩
        $data = [
            'menu_data' => $menuData,
            'building_data' => $buildingData,
        ];
        $this->data = $data;
        return true;
    }

    /**
     * 메뉴 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return array $fcData
     */
    private function getMakeMenuData(string $complexCodePk) : array
    {
        $fcData = [];

        $this->mobileMenu->setSensorObj($complexCodePk, $this->sensorObj);
        $fcData = [
            'groups' => $this->mobileMenu->getMenuGroup(),
            'menus' => $this->mobileMenu->getMenu(),
        ];

        return $fcData;
    }

    /**
     * 건물 기본정보 조회
     *
     * @param string $complexCodePk
     * @return array $fcData
     *
     * @throws \Exception
     */
    private function getBuildingInfoData(string $complexCodePk) : array
    {
        $fcData = [];

        $rBuildingQ = $this->mobileQuery->getQueryBuildingInfo($complexCodePk);
        $buildingData = $this->query($rBuildingQ);

        $fcData = [
            'name' => Utility::getInstance()->updateDecryption($buildingData[0]['name']),
            'addr' => Utility::getInstance()->updateDecryption($buildingData[0]['addr']),
        ];

        return $fcData;
    }
}