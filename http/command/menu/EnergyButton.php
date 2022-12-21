<?php
namespace Http\Command;

/**
 * Class EnergyButton 에너지원별 버튼 생성 
 */
class EnergyButton extends Command
{
    /**
     * EnergyButton constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * EnergyButton destructor.
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
        $complexCodePk = $_SESSION['ss_complex_pk'];
        $groupId = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $menuId = isset($params[1]['value']) === true ? $params[1]['value'] : 0;
        
        // 메뉴 버튼 내역 조회
        $buttonData = $this->getEnergyButtonData($complexCodePk, $groupId, $menuId);

        // view 에 데이터 바인딩 
        $this->data = $buttonData;

        return true;
    }

    /**
     * 메뉴 버튼 내역 조회
     * 
     * @param string $complexCodePk
     * @param int $groupId
     * @param int $menuId
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getEnergyButtonData(string $complexCodePk, int $groupId, int $menuId) :? array
    {
        $fcData = [];

        if (empty($groupId) === true && empty($menuId) === true) {
            return null;
        }

        // 해당 단지, 메뉴에 대해서 버튼 정보 조회
        $rEnergyButtonQ = $this->emsQuery->getEnergyButtonByMenu($complexCodePk, $groupId, $menuId);
        $fcData = $this->query($rEnergyButtonQ);

        return $fcData;
    }
}