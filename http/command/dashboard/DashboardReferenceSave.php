<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class DashboardReferenceSave
 */
class DashboardReferenceSave extends Command
{
    /** @var Usage|null $usage 사용량 요금 조회  */
    private ?usage $usage = null;

    /**
     * DashboardReferenceSave constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 사용량 모듈 객체 생성
        $this->usage = new Usage();
    }

    /**
     * DashboardReferenceSave destructor.
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
     * @return bool|mixed
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $complexCodePk = $_SESSION['ss_complex_pk'];
        $option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;

        // 기준갑 변경
        $this->setStandard($complexCodePk, $option, $params);

        return true;
    }

    /**
     * 기준값 변경
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $params
     *
     * @throws \Exception
     */
    private function setStandard(string $complexCodePk, int $option, array $params) : void
    {
        $usage = $this->usage;

        // 기준값 원본 데이터 조회
        $standardData = $usage->getReference($this, $complexCodePk, $option);

        // '/' 기준으로 나누기
        $standards = explode('/', $standardData);

        $year = empty($params[1]['value']) === true ? $standards[3] : Utility::getInstance()->removeXSS($params[1]['value']);
        $month = empty($params[2]['value']) === true ? $standards[2] : Utility::getInstance()->removeXSS($params[2]['value']);
        $day = empty($params[3]['value']) === true ? $standards[1] : Utility::getInstance()->removeXSS($params[3]['value']);
        $hour = empty($params[4]['value']) === true ? $standards[0] : Utility::getInstance()->removeXSS($params[4]['value']);

        $value =  $hour .'/'. $day .'/'. $month .'/'. $year;

        // 변경 사항 반영
        $rStandardQ = $this->emsQuery->updateStandardValue($complexCodePk, $option, $value);
        $this->squery($rStandardQ);
    }
}
