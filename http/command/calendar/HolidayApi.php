<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class HolidayApi  공휴일  정보 받아오기
 */
class HolidayApi extends Command
{
    /**
     * HolidayApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * HolidayApi destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 함수 실행
     *
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $today = date('Ymd');
        //$today = date('Ymd', strtotime('20220401'));

        $result = $this->receiveHolidayData($today);

        // 공휴일 데이터 추가
        $this->makeHolidayData($result);

        $this->data = [];
        return true;
    }

    /**
     * 공휴일 api 받아오기
     *
     * @param string $date
     *
     * @return string
     */
    private function receiveHolidayData(string $date) : string
    {
        $fcResult = '';

        /*
         * getHoliDeInfo : 국경일
         * getRestDeInfo : 공휴일
         */

        //$apiUrl = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getHoliDeInfo';
        $apiUrl = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';
        $serviceKey = $this->devOptions['API_HOLIDAY_SERVICE_KEY'];

        $method = 'GET';

        $fcHttpHeader = [];
        $fcReqData = [
            'solYear' => date('Y', strtotime($date)),
            'solMonth' => date('m', strtotime($date)),
            'ServiceKey' => $serviceKey
        ];

        $fcData = Utility::getInstance()->curlProcess($apiUrl, $method, $fcHttpHeader, $fcReqData);

        if ($fcData === false) {
            return $fcResult;
        }

        $fcResult = $fcData['msg'];

        return $fcResult;
    }

    /**
     * 공휴일 데이터 추가
     *
     * @param string $response
     *
     * @throws \Exception
     */
    private function makeHolidayData(string $response) : void
    {
        if (empty($response) === true) {
            return;
        }

        $xml = simplexml_load_string($response);

       $resultCode = $xml->header->resultCode;
       if ($resultCode === null || $resultCode != '00') {
           return;
       }

       $items = $xml->body->items->item;

       if (count($items) === 0) {
           return;
       }

       for ($fcIndex = 0; $fcIndex < count($items); $fcIndex++) {
           $holidayName = $items[$fcIndex]->dateName;
           $isHoliday = $items[$fcIndex]->isHoliday;
           $holidayDate = $items[$fcIndex]->locdate;

           // 추가
           $cHolidayQ = $this->emsQuery->getQueryInsertOrUpdateHoliday($holidayDate, $holidayName, $isHoliday);
           $this->squery($cHolidayQ);
       }
    }
}