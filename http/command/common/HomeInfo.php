<?php
namespace Http\Command;

/**
 * Class HomeInfo
 */
class HomeInfo extends Command
{
    /**
     * HomeInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * HomeInfo destructor.
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
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [
            'complex_info' => [],
        ];

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $option = isset($params[0]['value']) === true ? $params[0]['value'] : 0;
        $energyKey = isset($params[1]['value']) === true ? $params[1]['value'] : '';
        $formComplexCodePk = isset($params[2]['value']) === true ? $params[2]['value'] : '';
        $complexCodePk = empty($formComplexCodePk) === false ? $formComplexCodePk : $complexCodePk;

        // 세대 정보 조회
        $data['home_info'] = $this->getHomeInfoData($complexCodePk, $option);

        $this->data = $data;
        return true;
    }

    /**
     * 세대 정보 조회
     *
     * @param string $complexCodePk
     * @param int $option
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getHomeInfoData(string $complexCodePk, int $option) : array
    {
        $fcData = [];

        if (is_int($option) === true) {
            $rhomeQ = $this->emsQuery->getQueryHomeInfoSensor($complexCodePk, $option);
        } else {
            $rhomeQ = $this->emsQuery->getQueryHomeInfoAll($complexCodePk);
        }

        $fcData = $this->query($rhomeQ);

        return $fcData;
    }
}