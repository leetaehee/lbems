<?php
namespace EMS_Module;

use Database\DbModule;

/**
 * Class Menu 메뉴
 */
class Menu
{
    /** @var EMSQuery|null $emsQuery 쿼리 객체 */
    private ?EMSQuery $emsQuery = null;

    /** @var DbModule|null $db 데이터베이스 객체 */
    private ?DbModule $db = null;

    /** @var string $menuRootClassName 좌측 사이드바에 대한 클래스명 */
    private string $menuRootClassName = 'lnb';

    /** @var string $mainLiClassName  메뉴그룹과 서브그룹을 묶은항목 클래스명*/
    private string $mainLiClassName = 'dept';

    /** @var string $mainClassName 메뉴그룹명에  대한 클래스명 */
    private string $mainClassName = 'menu';

    /** @var string $subRootClassName 서브메뉴그룹에 대한   클래스명 */
    private string $subRootClassName = 'sub-menu';

    /** @var string $subClassName 현재 사용되지 않음 */
    private string $subClassName = '';

    /** @var string $selectName 메뉴가 클릭 되었을 때.. */
    private string $selectName = 'on';

    /** @var string $arrowClassName 메뉴 접기 클래스명 */
    private string $arrowClassName = 'lnb_up';

    /** @var string $skinType 스킨 종류 */
    private string $skinType = 'default';

    /** @var string $iconFolder 아이콘 경로  */
    private string $iconFolder = '';

    /** @var array $menu 메뉴 리스트  */
    private array $menu = [];

    /** @var array $menuKeys 메뉴그룹, 메뉴 키 리스트 */
    private array $menuKeys = [];

    /** @var array $menuIcon 메뉴 아이콘 */
    private array $menuIcon = [];

    /** @var array $superMenu 권한 */
    private array $superMenu = [];

    /** @var string $defaultPage 일반관리자 디폴트 화면  */
    private string $defaultPage = 'dashboard/dashboard_mdmt.html';

    /** @var string $defaultSuperPage 슈퍼관리자 디폴트 화면 */
    private string $defaultSuperPage = 'dashboard/dashboard_mdmt.html';

    /**
     * MobileMenuModule constructor.
     */
    public function __construct()
    {
        $this->emsQuery = new EMSQuery();
        $this->db = new DbModule();
    }

    /**
     * MobileMenuModule destructor
     */
    public function __destruct()
    {
    }

    /**
     * 개발자가 직접 메뉴 설정 (개발자 외에 사용 금지)
     */
    private function getMenuByArray()
    {
        // 메뉴설정
        $this->menu = Config::DEFAULT_MENUS;
        // 메뉴 그룹 아이콘 설정
        $this->menuIcon = Config::DEFAULT_MENU_ICONS;
        // 메뉴 그룹 권한 설정
        $this->superMenu = Config::DEFAULT_MENU_AUTHORITY;
    }

    /**
     * 서브메뉴 출력
     *
     * @param string $groupKey
     * @param array $arr
     * @param string $subRoot
     * @param string $subClass
     * @param int $index
     * @param string $menuSelect
     * @param string $super
     *
     * @return string
     */
    private function getSubMenu(string $groupKey, array $arr, string $subRoot, string $subClass, int $index, string $menuSelect, string $super) : string
    {
        $superMenu = $this->superMenu;
        $menuKeys = $this->menuKeys;

        $sub = "<ul class='${subRoot}'>";

        foreach ($arr as $key => $value) {
            if (array_key_exists($key, $superMenu) && $superMenu[$key] > $super) {
                continue;
            }

            // 메뉴 키
            $menu = $menuKeys[$groupKey][$key];

            $temp = "<li><a href='index.php?page=${value}&group={$index}&menu={$menu}'>$key</a></li>";
            $sub .= $temp;
        }
        $sub .= "</ul>";

        return $sub;
    }

    /**
     * 메뉴별 아이콘 생성
     *
     * @param string $name
     *
     * @return string
     */
    private function getIcon(string $name) : string
    {
        $menuIcon = $this->menuIcon;
        $menuSkin = $this->skinType;
        $icon = '';

        // 메뉴 아이콘 설정
        $iconDirectory = Config::SKIN_DIRECTORY;
        $this->iconFolder = $iconDirectory[$menuSkin]['menu_icon'];

        if (array_key_exists($name, $menuIcon)) {
            $path = $this->iconFolder.$menuIcon[$name];
            $icon = "<img src='${path}'>";
        }

        return $icon;
    }

    /**
     * 메뉴 생성
     *
     * @param array $arr
     * @param string $selected
     * @param string $super
     *
     * @return string
     */
    private function makeMenu(array $arr, string $selected, string $super) : string
    {
        $rootClass = $this->menuRootClassName;
        $mainLiClass = $this->mainLiClassName;
        $mainClass = $this->mainClassName;
        $subRoot = $this->subRootClassName;
        $subClass = $this->subClassName;
        $selectName = $this->selectName;
        $superMenu = $this->superMenu;
        $arrowClassName = $this->arrowClassName;
        $menuKeys = $this->menuKeys;
        $menuSelect = '';
        $menu = '';

        $index = 0;
        foreach ($arr as $key => $value) {
            if (array_key_exists($key, $superMenu) && $superMenu[$key] > $super) {
                continue;
            }

            $menuSelect = '';

            if ($index == $selected) {
                $menuSelect = $selectName;
            }

            $icon = $this->getIcon($key);
            $main = "<a class='${mainClass} ${menuSelect} ${arrowClassName}'>${icon}${key}</a>";
            $sub  = '';

            if (is_array($value) == true) {
                // 서브메뉴 그룹 키
                $subGroupKey = $menuKeys[$key]['sub_group_key'];

                $sub = $this->getSubMenu($key, $value, $subRoot, $subClass, $subGroupKey, $menuSelect, $super);
            } else {
                // 메뉴그룹 키
                $groupKey = $menuKeys[$key];

                $main = "<a class='${mainClass} ${menuSelect}' href='index.php?page=${value}&group={$groupKey}'>";
                $main .= $icon .'';
                $main .= $key . '</a>';
            }

            $temp = "<li class='${mainLiClass}'>".$main.$sub."</li>";
            $menu .= $temp;

            $index++;
        }

        return $menu;
    }

    /**
     * 로그인한 관리자의 단지정보 조회
     *
     * @param string $complexCodePk
     *
     * @return mixed
     */
    private function getComplexName(string $complexCodePk) : string
    {
        $rComplexQ = $this->emsQuery->getComplexNameByCode($complexCodePk);
        $this->db->querys($rComplexQ);

        $result = $this->db->getData();

        return Utility::getInstance()->updateDecryption($result[0]['name']);
    }

    /**
     * DB에서 메뉴 정보 조회
     *
     * @param string $complexCodePk
     *
     * @return array
     */
    private function getMenuFromDB(string $complexCodePk) : array
    {
        $ss_id = $_SESSION['ss_id'];

        $menuData = [];
        $iconData = [];
        $authorityData = [];
        $keyData = [];

        $rMenuQ = $this->emsQuery->getMenuData($complexCodePk);
        $this->db->querys($rMenuQ);

        $data = $this->db->getData();
        if (count($data) === 0) {
            //데이터 없는 경우 디폴트 메뉴 조회- 등록된 메뉴가 없는경우 getMenuByArray를 타기 때문에 아래 로직은 영향없음
            $rDefaultMenuQ = $this->emsQuery->getDefaultMenuData();
            $this->db->querys($rDefaultMenuQ);

            $data = $this->db->getData();
        }

        for ($i = 0; $i < count($data); $i++) {
            $menuGroupName = $data[$i]['group_menu_name'];
            $menuName = $data[$i]['menu_name'];

            $menuUserGroupAuthority = Config::MENU_USER_AUTHORITY[$ss_id];
            $menuUserDetailAuthority = $menuUserGroupAuthority[$menuGroupName];

            if (isset($menuUserGroupAuthority) === true
                && array_key_exists($menuGroupName, $menuUserGroupAuthority) === false) {
                continue;
            }

            if (isset($menuUserDetailAuthority) === true
                && in_array($menuName, $menuUserDetailAuthority) === false) {
                continue;
            }

            $groupId = $data[$i]['group_id'];
            $menuId = $data[$i]['menu_id'];
            $authority = $data[$i]['authority'];
            $icon = $data[$i]['icon'];
            $url = $data[$i]['url'];
            $menuName = $data[$i]['menu_name'];
			$isUseDashboard = $data[$i]['is_use_dashboard'];

            if (empty($menuId) === false) {
                // 서브 메뉴
                $menuData[$menuGroupName][$menuName] = $url;

                $keyData[$menuGroupName]['sub_group_key'] = $groupId;
                $keyData[$menuGroupName][$menuName] = $menuId;
            } else {
                // 그룹 메뉴
                $menuData[$menuGroupName] = $url;
                $keyData[$menuGroupName] = $groupId;
            }

            if (array_key_exists($menuGroupName, $iconData) === false) {
                // 메뉴 그룹 아이콘
                $iconData[$menuGroupName] = $icon;
            }

            if (array_key_exists($authority, $authorityData) === false) {
                // 권한
                $authorityData[$menuGroupName] = $authority;
            }

			if ($isUseDashboard === 'Y') {
                // 대시보드 설정된 메뉴가 있다면 변경
                $this->defaultPage = $url;
                $this->defaultSuperPage = $url;
            }
        }

        // 대시보드 리스트 가져오기- 대시보드가 두개인 경우 첫번째가 보이도록 함
        $rDashboardListQ = $this->emsQuery->getDashboardList($complexCodePk);
        $this->db->querys($rDashboardListQ);

        $dashboards = $this->db->getData();
        if (count($dashboards) > 1) {
            $this->defaultPage = $dashboards[0]['url'];
            $this->defaultSuperPage = $dashboards[0]['url'];
        }

        return [
            'menu_data' => $menuData,
            'authority_data' => $authorityData,
            'icon_data' => $iconData,
            'key_data' => $keyData
        ];
    }

    /**
     * DB에 있는 메뉴 정보로 셋팅
     *
     * @param string $complexCodePk
     */
    private function setMenu(string $complexCodePk) : void
    {
        $data = $this->getMenuFromDB($complexCodePk);

        $this->menu = $data['menu_data'];
        $this->menuIcon = $data['icon_data'];
        $this->superMenu = $data['authority_data'];
        $this->menuKeys = $data['key_data'];
    }

    /**
     * 메뉴 테이블 체크
     *
     * @param string $complexCodePk
     * @param string $dbname
     * @param string $findTableName
     *
     * @return int
     */
    private function getMenuTableExist(string $complexCodePk, string $dbname, string $findTableName) : int
    {
        // 메뉴 테이블이 존재하는지 조회
        $rFindMenuQ = $this->emsQuery->getTableExist($dbname, $findTableName);
        $this->db->querys($rFindMenuQ);
        $data = $this->db->getData();

        $menuTableCount = count($data);
        if ($menuTableCount === 0) {
            return (string)'Table Exist Error';
        }

        // 메뉴 테이블이 존재 한다면 메뉴가 몇개 있는지 확인한다.
        $rMenuGroupCountQ = $this->emsQuery->getMenuGroupCount($complexCodePk);
        $this->db->querys($rMenuGroupCountQ);

        $data = $this->db->getData();

        return (int)$data[0]['count'];
    }

    /**
     * 메인 함수
     *
     * @param string $complexCodePk
     * @param string $dbname
     * @param string $skinType
     * @param int $selected
     * @param int $super
     *
     * @return array
     */
    public function getMenu(string $complexCodePk, string $dbname, string $skinType, int $selected = -1, int $super = 70) : array
    {
        $version = Config::WEB_VERSION;

        $isMenuExistResult = $this->getMenuTableExist($complexCodePk, $dbname, 'bems_menu_group');

        if (empty($skinType) === false) {
            $this->skinType = $skinType;
        }

        if (is_string($isMenuExistResult) === true && $isMenuExistResult === 'Table Exist Error') {
            $this->getMenuByArray();
        }  else {
            // 메뉴 테이블이 존재하면 DB에서 가져온다.
            $this->setMenu($complexCodePk);
        }

        // 로그인 유저의 단지 정보 조회
        $complexName = $this->getComplexName($complexCodePk);

       // 사이트 오픈일자
        $rServiceDateQ = $this->emsQuery->getQuerySelectServiceStartDate($complexCodePk);
        $this->db->querys($rServiceDateQ);

        $serviceDateData = $this->db->getData();

        $rootClass = $this->menuRootClassName;
        $menuTag = "<div class='${rootClass}'><p>" .$complexName. "</p><ul>";
        $arr = $this->menu;
        $ret = $this->makeMenu($arr, $selected, $super);
        $menuTag .= $ret;

        $menuTag .= "</ul>";
        $menuTag .= "<div class='ac t20 fcGray' style='width: 240px; float: right'>{$version}</div>";
        $menuTag .= "</div>";

        return [
            'menu_tag' => $menuTag,
            'complex_name' => $complexName,
            'service_start_date' => $serviceDateData[0]['service_start_date'],
            'default_page' => $this->defaultPage,
            'default_super_page' => $this->defaultSuperPage
        ];
    }
}