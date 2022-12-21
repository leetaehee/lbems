<?php
namespace EMS_Modules;

use Http\SensorManager;

use EMS_Module\Config;
use EMS_Module\SensorInterface;

/**
 * Class MobileMenu 모바일 메뉴
 */
class MobileMenu
{
    /** @var array $menus */
    private array $menus = [];

    /** @var array $menuGroups */
    private array $menuGroups = [];

    /** @var SensorManager|null $sensorManager */
    protected ?SensorManager $sensorManager = null;

    /** @var Object|null $sensorObj */
    protected ?Object $sensorObj = null;

    /**
     * MobileMenu constructor.
     */
    public function __construct()
    {
        //setMenuData 호출할 것
    }

    /**
     * 메뉴 설정
     *
     * @param string $complexCodePk
     * @param SensorInterface|null $sensorObj
     */
    public function setSensorObj(string $complexCodePk, SensorInterface $sensorObj = null) : void
    {
        if ($sensorObj === null) {
            $this->sensorManager = new SensorManager();
            $this->sensorObj = $this->sensorManager->getSensorObject($complexCodePk);
        } else {
            $this->sensorObj = $sensorObj; // 의존성 주입
        }

        // 메뉴, 메뉴그룹 설정
        $this->setMenuData();
    }

    /**
     * 메뉴 조회
     *
     * @return array $menus
     */
    public function getMenu() : array
    {
        $menus = $this->menus;
        $defaultMenus = Config::DEFAULT_MOBILE_MENUS;

        $dataCount = 0;
        if (isset($menus) === true) {
            $dataCount = count($menus);
        }

        if ($dataCount === 0) {
            $menus = $defaultMenus;
        }

        return $menus;
    }

    /**
     * 업체에 메뉴 그룹이 정해지지 않은 경우 디폴트 값 조회
     *
     * @return array
     */
    public function getMenuGroup() : array
    {
        $menuGroups = $this->menuGroups;
        $defaultMenuGroups = Config::DEFAULT_MENU_GROUPS;

        $dataCount = 0;
        if (isset($menuGroups) === true) {
            $dataCount = count($menuGroups);
        }

        if ($dataCount === 0) {
            $menuGroups = $defaultMenuGroups;
        }

        return $menuGroups;
    }

    /**
     * 메뉴 데이터 조회
     *
     */
    public function setMenuData() : void
    {
        $menuInfo = $this->sensorObj->getMobileMenuInfo();

        $this->menus = $menuInfo['menu'];
        $this->menuGroups = $menuInfo['group'];
    }
}