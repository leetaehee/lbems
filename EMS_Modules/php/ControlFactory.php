<?php
namespace EMS_Module;

/**
 * Class ControlFactory 제어 팩토리
 */
class ControlFactory
{
    /** @var string|null $company 에어컨 제조사 */
    private ?string $company = null;

    /**
     * ControlFactory Constructor.
     *
     * @param string $company
     */
    public function __construct(string $company)
    {
        $this->company = $company;
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
     *
     * @return AirConditioner|null
     */
    private function makeObj(string $complexCodePk) :? AirConditioner
    {
        $obj = null;

        switch ($this->company) {
            case 'lg' :
                // Lg
                $obj = new LgAirConditioner($complexCodePk);
                break;
            case 'samsung' :
                // Samsung
                $obj = new SamsungAirConditioner($complexCodePk);
                break;
        }

        return $obj;
    }

    /**
     * 제어 처리
     *
     * @param string $complexCodePk
     * @param string $type
     * @param array $options
     *
     * @return array
     */
    public function processControl(string $complexCodePk, string $type, array $options = []) : array
    {
        $result = '';

        $controlObj = $this->makeObj($complexCodePk);
        if (is_null($controlObj) === true) {
            return $result;
        }

        $company = $this->company;
        $id = isset($options['id']) === true ? $options['id'] : '';
        $statusType = isset($options['status_type']) === true ? $options['status_type'] : '';

        $options = [
            'status_type' => $statusType,
        ];

        switch ($type) {
            case 'read' :
                // 제어 상태 조회
                $result = $controlObj->getStatus($complexCodePk, $company, $id, $options);
                break;
            case 'process' :
                // 제어 상태 처리
                //$result = $controlObj->setStatus($complexCodePk, $company, $id, $options);
                break;
        }

        return $result;
    }
}