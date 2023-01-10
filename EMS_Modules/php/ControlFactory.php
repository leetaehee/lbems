<?php
namespace EMS_Module;

/**
 * Class ControlFactory 제어 팩토리
 */
class ControlFactory
{
    /**
     * ControlFactory Constructor.
     */
    public function __construct()
    {
    }

    /**
     * ControlFactory Destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 에어컨 제조사에 따른 객체 생성
     *
     * @param string $complexCodePk
     * @param string $company
     *
     * @return AirConditioner|null
     */
    private function makeObj(string $complexCodePk, string $company) :? AirConditioner
    {
        $obj = null;

        switch ($company) {
            case 'lg' :
                // LG
                $obj = new LgAirConditioner($complexCodePk, $company);
                break;
            case 'samsung' :
                // Samsung
                $obj = new SamsungAirConditioner($complexCodePk, $company);
                break;
        }

        return $obj;
    }

    /**
     * 제어 처리
     *
     * @param string $complexCodePk
     * @param string $company
     * @param string $type
     * @param array $options
     *
     * @return array
     */
    public function processControl(string $complexCodePk, string $company, string $type, array $options = []) : array
    {
        $result = '';

        $temps = $options;

        $controlObj = $this->makeObj($complexCodePk, $company);
        if (is_null($controlObj) === true) {
            return $result;
        }

        $id = isset($temps['id']) === true ? $temps['id'] : '';
        $statusType = isset($temps['status_type']) === true ? $temps['status_type'] : '';
        $isDisplay = isset($temps['is_display']) === true ? $temps['is_display'] : false;

        $options = [
            'status_type' => $statusType,
        ];

        switch ($type) {
            case 'read' :
                // 제어 상태 조회
                $options['is_display'] = $isDisplay;

                $result = $controlObj->getStatus($complexCodePk, $id, $options);
                break;
            case 'process' :
                // 제어 상태 처리
                $options['parameter'] = $temps['parameter'];
                $result = $controlObj->setStatus($complexCodePk, $id, $options);
                break;
        }

        return $result;
    }
}