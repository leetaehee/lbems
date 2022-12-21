<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class AddMeterTOC  TOC 데이터 전송
 */
class AddMeterTOC extends Command
{
    /**
     * AddMeterTOC Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddMeterTOC Destructor.
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
        $sensorTypes = Config::SENSOR_TYPES;
        $sendDate = date('YmdHis');

        for ($i = 0; $i < count($sensorTypes); $i++) {
            $option = $i;
            $sensorType = $sensorTypes[$option];

            if (in_array($option, Config::TOC_ENERGY_TYPE_DATA) === false) {
                continue;
            }

            $searchQuery = '';

            switch ($option) {
                case 0 :
                    // 한전 메인 전력으로 검색
                    $searchQuery = Utility::getInstance()->makeWhereClause('home', 'home_grp_pk', '0M');
                    break;
                case 11 :
                    // 태양광 발전량 검색
                    $searchQuery = Utility::getInstance()->makeWhereClause('sensor', 'inout', 'I');
                    break;
            }

            // TOC 데이터 조회
            $rTocDataQ = $this->emsQuery->getQuerySelectTocData($option, $searchQuery);
            $tocData = $this->query($rTocDataQ);

            // TOC 데이터 전송
            $this->sendTocData($sendDate, $tocData, $sensorType);
        }

        // 센서별 검색이 아니라, 전체를 더해야 하는 경우
        $this->setFloorData($sendDate);

        $this->data = [];
        return true;
    }

    /**
     * 한전 메인 전력을 갖고 있지 않고 층별 사용량을 모두 하는 경우
     *
     * @param string $sendDate
     *
     * @throws \Exception
     */
    private function setFloorData(string $sendDate) : void
    {
        $fcData = [];
        $fcSensorType = Config::SENSOR_TYPES[0];
        $fcOption = 0;

        $siteType = $this->siteType;
        $tocCalculateInfo = Config::TOC_CALCULATE_INFO[$siteType];

        for ($i = 0; $i < count($tocCalculateInfo); $i++) {
            $complexCodePk = $tocCalculateInfo[$i];

            $searchQuery = Utility::getInstance()->makeWhereClause('home', 'complex_code_pk', $complexCodePk);

            // TOC 데이터 조회
            $rTocDataQ = $this->emsQuery->getQuerySelectTocData($fcOption, $searchQuery);
            $tocData = $this->query($rTocDataQ);

            // TOC 데이터 전송
            $this->sendTocData($sendDate, $tocData, $fcSensorType);
        }
    }

    /**
     * TOC 데이터 전송
     *
     * @param string $sendDate
     * @param array $data
     * @param string $sensorType
     */
    private function sendTocData(string $sendDate, array $data, string $sensorType) : void
    {
        if ($this->isDevMode() === true) {
            return;
        }

        if (count($data) === 0) {
            return;
        }

		$tocURL = $this->devOptions['TOC_URL'] . '/set_energy_data';
        $method = 'POST';

		$httpHeaders = [
            "toc-key:" . $this->devOptions['TOC_KEY']
        ];

        for ($fcIndex = 0;  $fcIndex < count($data); $fcIndex++) {

            $complexCodePk = $data[$fcIndex]['complex_code_pk'];

            $fcData = [
                'system' => $this->siteType,
                'complex_code' => $complexCodePk,
                'arch_type' => $data[$fcIndex]['arch_type'],
                'meter_type' => $sensorType,
                'sensor_id' => $data[$fcIndex]['toc_sensor_sn'],
                'val_date' => $sendDate,
                'val' => $data[$fcIndex]['val'],
				'error_code' => '',
                "memo" => '',
                "fg_use" => ''
            ];

            // raw-data 추가
            $fcResult = Utility::getInstance()->curlProcess($tocURL, $method, $httpHeaders, $fcData);
        }
    }
}