<?php
namespace Http\Command;

/**
 * Class MonitoringInfo 모니터링 센서 정보 조회
 */
class MonitoringInfo extends Command
{
    /**
     * Instrument constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Instrument destructor.
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

        // 층별 계측기 정보 조회
        $rInstrumentQ = $this->emsQuery->getQuerySelectInstrumentStatusByFloor($complexCodePk);
        $rInstruments = $this->query($rInstrumentQ);

        // 뷰에 데이터 전달.
        $data = [
            'monitor_sensors' => $rInstruments
        ];

        $this->data = $data;
        return true;
    }
}