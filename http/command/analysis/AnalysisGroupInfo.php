<?php
namespace Http\Command;

use EMS_Module\Config;

/**
 * Class AnalysisGroupInfo  분석 > 비교분석에서 에너지 키 조회
 */
class AnalysisGroupInfo extends Command
{
    /**
     * AnalysisGroupInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AnalysisGroupInfo Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수.
     *
     * @param array $params
     *
     * @return bool
     */
    public function execute(array $params) :? bool
    {
        // 에너지원, 용도별, 설비별로 SelectBox 데이터 추출
        $data = [];

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);

        $this->sensorObj = $this->getSensorManager($complexCodePk);
        if (is_null($this->sensorObj) === false) {
            $data = $this->getSensorManager($complexCodePk)->getEnergyPartData();
        }

        if (count($data) === 0) {
            $this->data = [
                'Error' => 'Empty',
            ];
            return true;
        }

        $data['sensor_type_no'] = Config::SENSOR_TYPE_NO;

        // 뷰에 보여질 데이터 추가
        $this->data = $data;

        return true;
    }
}