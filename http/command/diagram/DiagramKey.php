<?php
namespace Http\Command;

/**
 * Class DiagramKey 계통도 업체별 정보 조회
 */
class DiagramKey extends Command
{
    /** @var string $errorResult */
    private string $errorResult = 'Error';

    /**
     * DiagramKey constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * DiagramKey destructor.
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
     * @return bool
     */
    public function execute(array $params) :? bool
    {
        $ss_complex_pk = $_SESSION['ss_complex_pk'];
        $complexCodePk = $this->getSettingComplexCodePk($ss_complex_pk);

        $data = [];

        // 업체별 센서 정보를 조회하기 위한 정보 할당
        $this->sensorObj = $this->getSensorManager($complexCodePk);

        $floorKeyData = $this->sensorObj->getFloorInfo(); // 층 정보 조회
        if ($this->getValidateCheck($floorKeyData) === false) {
            $data['Error'] = $this->errorResult;
            $this->data = $data;
            return true;
        }

        $diagramData = $this->sensorObj->getDiagramKeyInfo(); // 계통도 항목 정보 조회
        if ($this->getValidateCheck($diagramData) === false) {
            $data['Error'] = $this->errorResult;
            $this->data = $data;
            return true;
        }

        $data = [
            'floor_key_data' => $floorKeyData,
            'used_key_data' => $diagramData['used'],
            'distribution_key_data' => $diagramData['distribution'],
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 업체별 정보가 있는지 유효성 검증
     *
     * @param array $data
     *
     * @return bool
     */
    private function getValidateCheck(array $data) : bool
    {
        $isValid = true;
        if (count($data) === 0) {
            $isValid = false;
        }

        return $isValid;
    }
}