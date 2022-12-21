<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Usage;
use EMS_Module\Config;

/**
 * Class SetStandardSave
 */
class SetStandardSave extends Command
{
    /** @var Usage|null $usage 사용량 | 요금 */
    private ?Usage $usage = null;

    /**
     * SetStandardSave constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * SetStandardSave destructor.
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
        $formArray = [];

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $formData = isset($params[1]['value']) === true ? $params[1]['value'] : 0;
        $energyKey = isset($params[2]['value']) === true ? $params[2]['value'] : '';

        // 폼 데이터 형식변경
        parse_str($formData, $formArray);
        $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

        $columns = [];
        if ($energyKey === 'finedust') {
            // 미세먼지 기준값 컬럼 조회
            $columns = ['limit_val_finedust'];
        } elseif ($energyKey === 'envrionment') {
            // 실내 환경정보의 경우
            $columns = ['limit_val_co2', 'limit_val_finedust'];
        }

        // 기준값 변경
        $result = $this->setStandardProcess($complexCodePk, $option, $formArray, $columns);
        if ($result === false) {
            $this->data = [
                'form-error' => false,
            ];
            return true;

        }

        $this->data = [];
        return true;
    }

    /**
     * 기준값 변경을 처리하는 그룹
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $params
     * @param array $columns
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function setStandardProcess(string $complexCodePk, int $option, array $params, array $columns) : bool
    {
        $columnCount = count($columns);
        $usage = $this->usage;

        $result = true;

        if ($columnCount === 1 || $columnCount === 0) {
            // 컬럼이 1개만 전달된 경우
            $column = $columns[0];

            // 기준값 변경처리
            $result = $this->setStandard($complexCodePk, $option, $params, $column);
        }

        if ($columnCount > 1) {
            // 컬럼이 여러개 온 경우
            for ($fcIndex = 0; $fcIndex < $columnCount; $fcIndex++) {
                $column = $columns[$fcIndex];

                // 기준값 변경처리
                $result = $this->setStandard($complexCodePk, $option, $params, $column);
            }
        }

        return $result;
    }

    /**
     * 기준값 변경
     *
     * @param string $complexCodePk
     * @param int $option
     * @param array $params
     * @param string|null $column
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function setStandard(string $complexCodePk, int $option, array $params, string $column = null) : bool
    {
        $sensorTypes = Config::SENSOR_TYPES;
        $usage = $this->usage;

        // 기준값 원본 데이터 조회
        $standardData = $usage->getReference($this, $complexCodePk, $option, $column);

        // '/' 기준으로 나누기
        $standards = explode('/', $standardData);

        if ($column === 'limit_val_finedust') {
            // 미세먼지에 대한 기준값 변경 처리..
            $fm10 = empty($params['finedust_fm10']) === true ? $standards[0] : $params['finedust_fm10'];
            $fm25 = empty($params['finedust_fm25']) === true ? $standards[1] : $params['finedust_fm25'];

            $value = $fm10 . '/' . $fm25;
        } elseif ($column === 'limit_val_co2') {
            $co2 = empty($params['finedust_co2']) === true ? $standards[0] : $params['finedust_co2'];
            $value = $co2;
        } else {
            // 에너지원에 대한 기준값 변경 처리..
            $sensorType = $this->getRealSensorType($params);

            $year = empty($params[$sensorType.'_year']) === true ? $standards[3] : $params[$sensorType.'_year'] ;
            $month = empty($params[$sensorType.'_month']) === true ? $standards[2] : $params[$sensorType.'_month'];
            $day = empty($params[$sensorType.'_day']) === true ? $standards[1] : $params[$sensorType.'_day'];
            $hour = empty($params[$sensorType.'_hour']) === true ? $standards[0] : $params[$sensorType.'_hour'];

            $value = $hour .'/'. $day .'/'. $month .'/'. $year;
        }

        if (strlen($value) > 60) {
            // 문자열 길이를 초과하는 경우 입력안함..
            // 기준값과 / 포함한 길이..
            return false;
        }

        // 변경 사항 반영
        $rStandardQ = $this->emsQuery->updateStandardValue($complexCodePk, $option, $value, $column);
        $this->squery($rStandardQ);

        return true;
    }

    /**
     * 업체마다 추가 되는 설비에 대해 센서 타입 추가 할 수 있도록 센서명 리턴
     *
     * @param array $standards
     *
     * @return string|false
     */
    private function getRealSensorType(array $standards) : string
    {
        $data = array_keys($standards);

        // 찾고자 하는 에너지원
        $findString = $data[0];

        $startPos = strpos($findString, '_hour');
        $sensorType = substr($data[0], 0, $startPos);

        return $sensorType;
    }
}