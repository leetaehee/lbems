<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class AddComplexDataTOC TOC 건물정보 전송
 */
class AddComplexDataTOC extends Command
{
    /**
     * AddComplexDataTOC Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AddComplexDataTOC Destructor.
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
        // TOC 건물정보 조회
        $rBuildingQ = $this->emsQuery->getQuerySelectTocBuildingData();
        $buildingData = $this->query($rBuildingQ);

        // TOC 건물정보 가공
        $this->makeTocBuildingData($buildingData);

        $this->data = [];
        return true;
    }

    /**
     * TOC 건물정보 가공
     *
     * @param array $data
     *
     * @throws \Exception
     */
    private function makeTocBuildingData(array $data) : void
    {
        if ($this->isDevMode() === true) {
            return;
        }

        if (count($data) === 0) {
            return;
        }

        $tocURL = $this->devOptions['TOC_URL'] . '/set_complex_info';
        $method = 'POST';

		$httpHeaders = [
            "toc-key:" . $this->devOptions['TOC_KEY']
        ];

        for ($fcIndex = 0;  $fcIndex < count($data); $fcIndex++) {
            $complexCodePk = $data[$fcIndex]['complex_code_pk'];

            // 건물애 대한 현황 조회
            $rBuildingStatusQ = $this->emsQuery->getQuerySelectBuildingStatusData($complexCodePk);
            $buildingStatus = $this->query($rBuildingStatusQ);

            $siteType = $this->siteType;

            $buildingCnt = $siteType === 'lbems' ? $buildingStatus[0]['cnt'] : "0";
            $factoryCnt = $siteType === 'fems' ? $buildingStatus[0]['cnt'] : "0";

            $fcData = [
                'system' => $siteType,
                'complex_code' => $complexCodePk,
                'name' => Utility::getInstance()->updateDecryption($data[$fcIndex]['name']),
                'post_code' => $data[$fcIndex]['post_code'],
                'latitude' => $data[$fcIndex]['lat'],
                'longitude' => $data[$fcIndex]['lon'],
                'house_count' => "0",
                'building_count' => $buildingCnt,
                'store_count' => "0",
                'factory_count' => $factoryCnt,
            ];

            // 건물정보 추가
			$fcResult = Utility::getInstance()->curlProcess($tocURL, $method, $httpHeaders, $fcData);

            print_r($fcResult);
        }
    }
}