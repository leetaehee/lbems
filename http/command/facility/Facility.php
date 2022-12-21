<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Utility;

/**
 * Class Facility
 */
class Facility extends Command
{
    /** @var Usage|null $usage 사용량 */
    private ?Usage $usage = null;

    /**
     * Facility constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->usage = new Usage();
    }

    /**
     * Facility destructor.
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
        $floor = 'all';
        $room = 'all';
        $sensor = '';

        $complexCodePk = $this->getSettingComplexCodePk($_SESSION['ss_complex_pk']);
        $dateType = isset($params[0]['value']) == true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        $date = isset($params[1]['value']) == true ? Utility::getInstance()->removeXSS($params[1]['value']) : '0000-00-00';

        $this->sensorObj = $this->getSensorManager($complexCodePk);
        if (is_null($this->sensorObj) === true) {
            $data['error'] = null;
            $this->data = $data;
            return true;
        }

        // 설비 내역 조회
        $data = $this->getFacilityUsedData($complexCodePk, $dateType, $date, $floor, $room, $sensor);

        // 뷰에 데이터 바인딩
        $this->data = $data;
        return true;
    }

    /**
     * 설비 내역 조회
     *
     * @param string $complexCodePk
     * @param int $dateType
     * @param string $date
     * @param string $floor
     * @param string $room
     * @param string $sensor
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getFacilityUsedData(string $complexCodePk, int $dateType, string $date, string $floor, string $room, string $sensor) :? array
    {
        $fcData = [];
        $nows = [];
        $totals = [];
        $keySensor = [];
        $keySensors = [];
        $addOptions = [];

        $obj = $this->usage;

        // 주기에 따른 조회 날짜 출력
        $periods = $this->getPeriod($date, $dateType);
        $startDate = $periods['start_date'];
        $endDate = $periods['end_date'];

        // 에너지원, 용도별, 설비별 키 정보 조회
        $analysisData = $this->sensorObj->getEnergyPartData();
        if (count($analysisData) === 0) {
            return null;
        }

        // 키 네임으로 센서 정보 찾고자 하는 경우
        $separatedData = $this->sensorObj->getSpecialSensorKeyName();

        $facilityData = $analysisData['facility'];
        foreach ($facilityData as $key => $values) {
            $addOptions['energy_name'] = $key;
            $option = (int)$values['option'];
            if (is_null($separatedData[$key]) === false) {
                $keySensor = $separatedData[$key];
            }

            if (is_array($separatedData) === true && count($keySensor) > 0) {
                if ($dateType === 0) {
                    $addOptions['is_use_next_date'] = false;
                    $nows = $obj->getEnergyDataBySensor($this, $complexCodePk, $option, $dateType, $startDate, $addOptions, $keySensor);
                } else {

                    $nows = $obj->getEnergyDataByRangeBySensor($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions, $keySensor);
                }
            } else {
                $addOptions['sensor'] = $sensor;
                if ($dateType === 0) {
                    $addOptions['is_use_next_date'] = false;

                    $nows = $obj->getEnergyData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);
                } else {
                    $nows = $obj->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, $startDate, $endDate, $addOptions);
                }
            }

            // 사용량 합계 조회
            $addOptions = [
                'energy_name' => $key,
                'is_use_next_date' => false,
                'separated_sensors' => Utility::getInstance()->arrayKeyCheckResult($key, $separatedData),
            ];

            if ($dateType === 1) {
                // 월로 검색 시 시작일~종료일 추가 되어야 함
                $addOptions['start_date'] = $startDate;
                $addOptions['end_date'] = $endDate;
            }

            $total = $obj->getUsageSumData($this, $complexCodePk, $option, $dateType, $startDate, $addOptions);

            $fcData[$key]  = [
                'list' => $nows['data'],
                'total' => $total['current']['data'],
            ];
        }

        return $fcData;
    }

    /**
     * 주기에 따른 검색 날짜 조회
     *
     * @param string $date
     * @param string $dateType
     *
     * @return array $fcData
     */
    private function getPeriod(string $date, string $dateType) : array
    {
        $fcData = [];

        switch ($dateType)
        {
            case 0:
                // 금년
                $endMonth = date('Y-m', strtotime($date));
                $temp = explode('-', $endMonth);

                // 시작(월)~종료(월)
                $startDate = $temp[0];
                $endDate = $temp[0];

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                break;
            case 1:
            case 6:
                // 금월
                $date = date('Y-m-d', strtotime($date));
                $temp = explode('-', $date);
                $endDay = date('t', strtotime($date));

                // 시작일~종료일
                $startDate = $temp[0] . '' . $temp[1] . '01';
                $endDate = $temp[0] . '' . $temp[1] . '' . $endDay;

                $fcData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
                break;
            case 2:
                // 금일
                $today = date('Ymd', strtotime($date));

                $fcData = [
                    'start_date' => $today,
                    'end_date' => $today
                ];
                break;
        }

        return $fcData;
    }
}