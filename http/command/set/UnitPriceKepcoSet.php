<?php
namespace Http\Command;

use Database\DbModule;

use EMS_Module\Utility;

/**
 * Class UnitPriceKepcoSet - Kepco 기준으로 에너지 단가 정보 수정
 */
class UnitPriceKepcoSet extends Command
{
    /** @var DbModule|null $priceDb 전기 요금 객체 */
    private ?DbModule $priceDb = null;

    /**
     * UnitPriceKepcoSet constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $devOptions = $this->devOptions;
        $priceDBInfo = [
            'DB_TYPE' => $devOptions['FEE_DB_TYPE'],
            'DB_HOST' => $devOptions['FEE_DB_HOST'],
            'DB_PORT' => $devOptions['FEE_DB_PORT'],
            'DB_ID' => $devOptions['FEE_DB_ID'],
            'DB_PASSWORD' => $devOptions['FEE_DB_PASSWORD'],
            'DB_SID' => $devOptions['FEE_DB_SID'],
        ];

        $this->priceDb = new DbModule($priceDBInfo);
    }

    /**
     * UnitPriceKepcoSet destructor.
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

        $costNo = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $formStr = isset($params[1]['value']) === true ? $params[1]['value'] : [];

        $formData = $this->getFormData($costNo, $formStr);
        if ($formData === null) {
            $data['error'] = 'dataError';
			$this->data = $data;
            return true;
        }

        $validate = $this->getFormValidate($formData);
        if ($validate === false) {
            $data['error'] = 'validateError';
			$this->data = $data;
            return true;
        }

        // 요금정보 변경
        $uPriceQ = $this->electricPriceQuery->getQueryUpdatePriceInfo($costNo, $formData);
        $this->priceDb->squery($uPriceQ);

        $this->data = $data;
        return true;
    }

    /**
     * 폼 데이터 검증 후 배열로 반환받기
     *
     * @param int $costNo
     * @param string $formString
     *
     * @return array|null
     */
    private function getFormData(int $costNo, string $formString) :? array
    {
        $fcData = [];

        if (empty($costNo) === true || $costNo < 1) {
            return null;
        }

        if (empty($formString) === true || strlen($formString) < 1) {
            return null;
        }

        parse_str($formString, $fcData);

        return Utility::getInstance()->removeXSSFromFormData($fcData);
    }


    /**
     * 폼 데이터 유효성 검증
     *
     * @param array $forms
     *
     * @return bool
     */
    private function getFormValidate(array $forms) : bool
    {
        if (isset($forms['popup_defaultPrice']) === true && is_numeric($forms['popup_defaultPrice']) === false) {
            return false;
        }

        if (isset($forms['popup_cost']) === true && is_numeric($forms['popup_cost']) === false) {
            return false;
        }

        return true;
    }
}