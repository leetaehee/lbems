<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class MenuLocation 메뉴 아이디 정보 조회
 */
class MenuLocation extends Command
{
    /**
     * MenuLocation constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MenuLocation destructor.
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

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $groupId = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        $menuId = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : 0;
        $menuGroup = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : '';
        $menuLink = isset($params[3]['value']) === true ? Utility::getInstance()->removeXSS($params[3]['value']) : '';
		$groupPage = isset($params[4]['value']) === true ? Utility::getInstance()->removeXSS($params[4]['value']) : '';
        $menuName = isset($params[5]['value']) === true ? Utility::getInstance()->removeXSS($params[5]['value']) : '';

		$filePath = (strpos($menuLink, 'undefined') !== false) ? $menuGroup : $menuGroup.'/'.$menuLink;

        // 메뉴 아이디 정보 조회
        $menuData = $this->getMenuId($complexCodePk, $groupId, $menuId, $filePath, $groupPage, $menuName);

        $data['menu'] = $menuData;

        $this->data = $data;
        return true;
    }

    /**
     * 메뉴 테이블에서 아이디 정보 추출
     *
     * @param string $complexCodePk
     * @param int $groupId
     * @param int $menuId
     * @param string $filePath
	 * @param string $groupPage
     * @param string $menuName
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getMenuId(string $complexCodePk, int $groupId, int $menuId, string $filePath, string $groupPage, string $menuName) : array
    {
        $fcData = [];

        $rMenuIdQ = '';
		$separators = explode('/',$filePath);

        if (count($separators) === 1) {
            $filePath .= "/{$groupPage}.html";
        }

        if (is_int($menuId) === true && $menuId >= 0 && count($separators) > 1) {
            // bems_menu 테이블 참조 (메뉴가 그룹화 되어있는 경우)
            $rMenuIdQ = $this->emsQuery->getMenuIdx($complexCodePk, $filePath);
        } else {
            // bems_menu_group 테이블 참조
            $rMenuIdQ = $this->emsQuery->getMenuGroupIdx($complexCodePk, $menuName);
        }
        // 실행
        $menus = $this->query($rMenuIdQ);
        if (isset($menus[0]) === true && empty($menus[0]) === false) {
            $fcData = $menus[0];
        }

        return $fcData;
    }
}