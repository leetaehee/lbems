<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Usage;

/**
 * Class InfoPopup
 */
class InfoPopup extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * InfoPopupPous constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 사용량 모듈 객체 생성
        $this->usage = new Usage();
    }

    /**
     * InfoPopupPous destructor.
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
        $mode = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : '';
        $option = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : 0;
        $menu = isset($params[2]['value']) === true ? Utility::getInstance()->removeXSS($params[2]['value']) : '';

        // 미세먼지, co2의 경우
        $columns = [];
        if (empty($menu) === false) {
            if ($menu === 'environment') {
                $columns = ['limit_val_co2', 'limit_val_finedust'];
            } elseif ($menu === 'finedust') {
                $columns = ['limit_val_finedust'];
            }
        }

        if ($mode === 'getStandards') {
            // 기준값 보여주기
            $data['standard_data'] = $this->getStandardPopupProcess($complexCodePk, $option, $columns);
        }

        if ($mode === 'saveStandards') {
            // 기준값 저장하기
            $this->setStandardProcess($complexCodePk, $option, $params, $columns);
        }

        $this->data = $data;

        return true;
    }

    /**
     * 기준값
     *
     * @param array $standards
     * @param string|null $column
     *
     * @return array
     */
    private function getStandardData(array $standards, string $column = null) : array
    {
        $fcData = [];

        if ($column === 'limit_val_finedust') {
            $fcData = [
                'pm10' => $standards[0],
                'pm25' => $standards[1],
            ];
        } else if ($column === 'limit_val_co2') {
            $fcData = [
                'co2' => $standards[0],
            ];
        } else {
            $fcData = [
                'hour'=> $standards[0],
                'day'=> $standards[1],
                'month'=> $standards[2],
                'year'=> $standards[3],
            ];
        }

        return $fcData;
    }

    /**
     * 기준값 변경
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $standards
     * @param array $params
     * @param string|null $column
     *
     * @throws \Exception
     */
    private function setStandardData(string $complexCodePk, int $option, array $standards, array $params, string $column = null) : void
    {
        if ($column === 'limit_val_finedust') {
            $pm10 = empty($params[3]['value']) === true ? $standards[0] : $params[3]['value'];
            $pm25 = empty($params[4]['value']) === true ? $standards[1] : $params[4]['value'];

            $value = $pm10 . '/' . $pm25;
        } else if ($column === 'limit_val_co2') {
            $co2 = empty($params[5]['value']) === true ? $standards[0] : $params[5]['value'];

            $value = $co2;
        } else {
            $hour = empty($params[3]['value']) === true ? $standards[0] : $params[3]['value'];
            $day = empty($params[4]['value']) === true ? $standards[1] : $params[4]['value'];
            $month = empty($params[5]['value']) === true ? $standards[2] : $params[5]['value'];
            $year = $standards[3];

            $value =  $hour .'/'. $day .'/'. $month .'/'. $year;
        }

        $rStandardQ = $this->emsQuery->updateStandardValue($complexCodePk, $option, $value, $column);
        $this->squery($rStandardQ);
    }

    /**
     * 기준값 조회를 처리하는 그룹
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $columns
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getStandardPopupProcess(string $complexCodePk, int $option, array $columns) : array
    {
        $fcData = [];

        $columnCount = count($columns);
        $usage = $this->usage;

        if ($columnCount === 1 || $columnCount === 0) {
            // 컬럼이 1개만 전달된 경우
            $column = $columns[0];

            // 기준값 조회
            $standardData = $usage->getReference($this, $complexCodePk, $option, $column);
            // '/' 기준으로 나누기
            $standards = explode('/', $standardData);
            // 데이터 가공
            $fcData = $this->getStandardData($standards, $column);
        }

        if ($columnCount > 1) {
            // 컬럼이 여러개 온 경우
            for ($fcIndex = 0; $fcIndex < $columnCount; $fcIndex++) {
                $column = $columns[$fcIndex];

                // 기준값 조회
                $standardData = $usage->getReference($this, $complexCodePk, $option, $column);
                // '/' 기준으로 나누기
                $standards = explode('/', $standardData);
                // 데이터 가공
                $fcData[$column] = $standards;
            }
        }

        return $fcData;
    }

    /**
     * 기준값 변경을 처리하는 그룹
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $params
     * @param array $columns
     *
     * @throws \Exception
     */
    private function setStandardProcess(string $complexCodePk, int $option, array $params, array $columns) : void
    {
        $columnCount = count($columns);
        $usage = $this->usage;

        if ($columnCount === 1 || $columnCount === 0) {
            // 컬럼이 1개만 전달된 경우
            $column = $columns[0];

            // 기준값 조회
            $standardData = $usage->getReference($this, $complexCodePk, $option, $column);
            // '/' 기준으로 나누기
            $standards = explode('/', $standardData);

            // 기준값 변경처리
            $this->setStandardData($complexCodePk, $option, $standards, $params, $column);
        }

        if ($columnCount > 1) {
            // 컬럼이 여러개 온 경우
            for ($fcIndex = 0; $fcIndex < $columnCount; $fcIndex++) {
                $column = $columns[$fcIndex];

                // 기준값 조회
                $standardData = $usage->getReference($this, $complexCodePk, $option, $column);
                // '/' 기준으로 나누기
                $standards = explode('/', $standardData);

                // 기준값 변경처리
                $this->setStandardData($complexCodePk, $option, $standards, $params, $column);
            }
        }
    }
}