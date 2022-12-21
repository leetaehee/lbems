<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class InfoEnvironment 실내 환경 정보
 */
class InfoEnvironment extends Command
{
    /** @var array|array[]  $f25Data 초미세먼지 기준값*/
    private array $f25Data = [
        'good'=> [
            'min'=> 0,
            'max'=> 16,
            'status'=> '좋음',
            'img'=> 'dust_status_bg2',
            'imoticon'=> 'dust_status_img1'
        ],
        'normal'=> [
            'min'=> 16,
            'max'=> 51,
            'status'=> '보통',
            'img'=> 'dust_status_bg3',
            'imoticon'=> 'dust_status_img2'
        ],
        'bad'=> [
            'min'=> 51,
            'max'=> 101,
            'status'=> '나쁨',
            'img'=> 'dust_status_bg4',
            'imoticon'=> 'dust_status_img4'
        ],
        'v_bad'=> [
            'min'=> 101,
            'status'=> '매우나쁨',
            'img'=> 'dust_status_bg5',
            'imoticon'=> 'dust_status_img5'
        ]
    ];

    /** @var array|array[] $fCo2Data Co2 기준값 */
    private array $fCo2Data = [
        'v_good' => [
            'min'=> 0,
            'max'=> 450,
            'status'=> '매우좋음',
            'img'=> 'dust_status_bg1',
            'imoticon'=> 'dust_status_img1'
        ],
        'good'=> [
            'min'=> 451,
            'max'=> 1000,
            'status'=> '좋음',
            'img'=> 'dust_status_bg2',
            'imoticon'=> 'dust_status_img2'
        ],
        'normal'=> [
            'min'=> 1001,
            'max'=> 2000,
            'status'=> '보통',
            'img'=> 'dust_status_bg3',
            'imoticon'=> 'dust_status_img3'
        ],
        'bad'=> [
            'min'=> 2001,
            'max'=> 5000,
            'status'=> '나쁨',
            'img'=> 'dust_status_bg4',
            'imoticon'=> 'dust_status_img4'
        ],
        'v_bad'=> [
            'min'=> 5000,
            'status'=> '매우나쁨',
            'img'=> 'dust_status_bg5',
            'imoticon'=> 'dust_status_img5'
        ]
    ];

    /**
     * InfoEnvironment constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * InfoEnvironment destructor.
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
        $co2DateType = isset($params[0]['value']) === true ? Utility::getInstance()->removeXSS($params[0]['value']) : 0;
        $pm25DateType = isset($params[1]['value']) === true ? Utility::getInstance()->removeXSS($params[1]['value']) : 0;

        // 금일 데이터 조회
        $dailyData = $this->getFinedustDaily($complexCodePk);
        // 금월 데이터 조회
        $monthData = $this->getFinedustMonth($complexCodePk);
        // 금년 데이터 조회
        $yearData = $this->getFinedustYear($complexCodePk);

        $todayP = $dailyData['current'];
        $todayData = $this->getFinedustTodayStatus($todayP);

        // 뷰에 데이터 바인딩
        $data = [
            'daily_finedust' => [
                'data' => $dailyData['data'],
                'today'=> $todayData
            ],
            'month_finedust' => [
                'start_date'=> $monthData['start_date'],
                'end_date'=> $monthData['end_date'],
                'co2'=> $monthData['co2'],
                'pm25'=> $monthData['pm25'],
            ],
            'year_finedust' => [
                'start_year'=> $yearData['start_year'],
                'end_year'=> $yearData['end_year'],
                'pm25'=> $yearData['pm25'],
                'co2' => $yearData['co2'],
            ],
        ];

        $this->data = $data;
        return true;
    }

    /**
     * 금일 조회 (초미세먼지, CO2)
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getFinedustDaily(string $complexCodePk) : array
    {
        $startHour = 0;
        $endHour = date('H', strtotime("{$this->baseDateInfo['date_time']}"));

        // 초미세먼지 기준값 조회
        $rStandardPM25Q = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_finedust');
        $pm25StandData = $this->query($rStandardPM25Q);

        // CO2 기준값 조회
        $rStandardCO2Q = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_co2');
        $co2StandData = $this->query($rStandardCO2Q);

        $fdData = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $today = date('Y-m-d', strtotime($this->baseDateInfo['date']));

            $displayHour = $hour . '시';

            if ($hour < 10) {
                $hour = '0' . $hour;
            }

            $today .= ' ' . $hour . ':';

            //시간대별 합계 데이터 추출
            $rFinedustQ = $this->emsQuery->getInfoFineDustMeter($today, $complexCodePk);
            $rFdResult = $this->query($rFinedustQ);

            $hourKeyName = 'hour' . $hour;
            $fdData['deviceui'] = $rFdResult[0]['device_eui'];

            // 기준값
            $standards = explode('/', $pm25StandData[0]['val']);

            $fdData[$hourKeyName] = [
                'hour'=> $displayHour,
                'pm25'=> $rFdResult[0]['pm25'],
                'co2'=> $rFdResult[0]['co2'],
                'pm25_standard' => $standards[1],
                'co2_standard' => $co2StandData[0]['val']
            ];
        }

        // 현재시각 미세먼지
        $currentFd = $fdData[$hourKeyName];

        $startHour = $endHour + 1;
        $endHour = 23;
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $displayHour = $hour . '시';

            if ($hour < 10) {
                $hour = '0' . $hour;
            }

            $hourKeyName = 'hour' . $hour;

            // 기준값
            $standards = explode('/', $pm25StandData[0]['val']);

            $fdData[$hourKeyName] = [
                'hour'=> $displayHour,
                'pm25'=> 0,
                'co2'=> 0,
                'pm25_standard' => $standards[1],
                'co2_standard' => $co2StandData[0]['val']
            ];
        }

        return [
            'data'=> $fdData,
            'current'=> [
                'pm25'=> $currentFd['pm25'],
                'co2'=> $currentFd['co2'],
            ]
        ];
    }

    /**
     * 금월 조회 (초미세먼지, co2)
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getFinedustMonth(string $complexCodePk) : array
    {
        $today = $this->baseDateInfo['date'];
        $lastDay = date('t', strtotime($today));

        $month = date('Ym', strtotime($today));
        $startDate = $month . '01';
        $endDate = $month . $lastDay;

        // 초미세먼지 기준값 조회
        $rStandardPM25Q = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_finedust');
        $pm25StandData = $this->query($rStandardPM25Q);
        $standards = explode('/', $pm25StandData[0]['val']);

        // CO2 기준값 조회
        $rStandardCO2Q = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_co2');
        $co2StandData = $this->query($rStandardCO2Q);

        // 일통계 테이블 정의
        $dailyFinedustTable = [
            'co2'=> 'bems_stat_daily_co2',
            'pm25'=> 'bems_stat_daily_finedust_ultra',
        ];

        // 초 미세먼지(PM25) 조회
        $rPM25P = [
            'table'=> $dailyFinedustTable['pm25'],
            'start_date'=> $startDate,
            'end_date'=> $endDate,
            'complex_code_pk'=> $complexCodePk
        ];

        $rPM25Q = $this->emsQuery->getInfoFineDustMonth($rPM25P);
        $rPM25Result = $this->query($rPM25Q);

        // PM25 저장할 데이터 추가 배열 선언
        $infoPM25Data = [];
        // 인덱스 초기화
        $infoPM25Index = 0;
        // 일 카운트
        $dateCount = 0;

        for ($i = 0, $date = $startDate; $date <= $endDate; $i++, $date++) {
            $pm25 = $rPM25Result[$infoPM25Index];
            $pm25ValDate = $pm25['val_date'];
            $standardVal = $standards[1];

            if ($date == $pm25ValDate) {
                $val = $pm25['val'];
                $infoPM25Index = ($infoPM25Index+1);
                // 그래프에 출력되는 만큼만 카운트
                $dateCount = $dateCount + 1;
            } else {
                $val = 0;
                $infoPM25Index = $infoPM25Index;
            }

            $displayDate = date('j', strtotime($date)) . '일';

            // 미세먼지(pm10) 데이터 추가
            $infoPM25Data[] = [
                'date'=> $displayDate,
                'val'=> $val,
                'standard'=> $standardVal
            ];
        }

        // CO2 조회
        $rCO2P = [
            'table'=> $dailyFinedustTable['co2'],
            'start_date'=> $startDate,
            'end_date'=> $endDate,
            'complex_code_pk'=> $complexCodePk
        ];

        $rCO2Q = $this->emsQuery->getInfoFineDustMonth($rCO2P);
        $rCO2Result = $this->query($rCO2Q);

        // CO2 저장할 데이터 추가 배열 선언
        $infoCO2Data = [];
        // 인덱스 초기화
        $infoCO2Index = 0;
        // 일 카운트
        $dateCount = 0;

        for ($i = 0, $date = $startDate; $date <= $endDate; $i++, $date++) {
            $CO2 = $rCO2Result[$infoCO2Index];
            $co2ValDate = $CO2['val_date'];
            $standardVal = $co2StandData[0]['val'];

            if ($date == $co2ValDate) {
                $val = $CO2['val'];
                $infoCO2Index = ($infoCO2Index+1);
                // 그래프에 출력되는 만큼만 카운트
                $dateCount = $dateCount + 1;
            } else {
                $val = 0;
                $infoCO2Index = $infoCO2Index;
            }

            $displayDate = date('j', strtotime($date)) . '일';

            // CO2 데이터 추가
            $infoCO2Data[] = [
                'date'=> $displayDate,
                'val'=> $val,
                'standard'=> $standardVal
            ];
        }

        return [
            'start_date'=> $startDate,
            'end_date'=> $endDate,
            'co2'=> $infoCO2Data,
            'pm25'=> $infoPM25Data,
        ];
    }

    /**
     * 금년 조회 (초미세먼지, co2)
     *
     * @param string $complexCodePk
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getFinedustYear(string $complexCodePk) : array
    {
        $year = date('Y', strtotime($this->baseDateInfo['date']));

        $startYear = $year . '01';
        $endYear = $year . '12';

        // 초미세먼지 기준값 조회
        $rStandardPM25Q = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_finedust');
        $pm25StandData = $this->query($rStandardPM25Q);
        $standards = explode('/', $pm25StandData[0]['val']);

        // CO2 기준값 조회
        $rStandardCO2Q = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_co2');
        $co2StandData = $this->query($rStandardCO2Q);

        // 월통계 테이블 정의
        $monthFinedustTable = [
            'co2'=> 'bems_stat_month_co2',
            'pm25'=> 'bems_stat_month_finedust_ultra',
        ];

        // 초미세먼지(PM25) 조회
        $rPM25P = [
            'table'=> $monthFinedustTable['pm25'],
            'start_year'=> $startYear,
            'end_year'=> $endYear,
            'complex_code_pk'=> $complexCodePk
        ];
        $rPM25Q = $this->emsQuery->getInfoFineDustYear($rPM25P);
        $rPM25Result = $this->query($rPM25Q);

        // PM25 저장할 데이터 추가 배열 선언
        $infoPM25Data = [];
        // 인덱스 초기화
        $infoPM25Index = 0;
        // 일 카운트
        $dateCount = 0;

        for ($i = 0, $year = $startYear; $year <= $endYear; $i++, $year++) {
            $pm25 = $rPM25Result[$infoPM25Index];
            $pm25ValYear = $pm25['ym'];
            $standardVal = $standards[1];

            if ($year == $pm25ValYear) {
                $val = $pm25['val'];
                $infoPM25Index = ($infoPM25Index+1);
                // 그래프에 출력되는 만큼만 카운트
                $dateCount = $dateCount + 1;
            } else {
                $val = 0;
                $infoPM25Index = $infoPM25Index;
            }

            $temp = $year . '01';
            $displayDate = date('n', strtotime($temp)) . '월';

            // 미세먼지(pm25) 데이터 추가
            $infoPM25Data[] = [
                'date'=> $displayDate,
                'val'=> $val,
                'standard'=> $standardVal
            ];
        }

        // CO2 조회
        $rCO2P = [
            'table'=> $monthFinedustTable['co2'],
            'start_year'=> $startYear,
            'end_year'=> $endYear,
            'complex_code_pk'=> $complexCodePk
        ];

        $rCO2Q = $this->emsQuery->getInfoFineDustYear($rCO2P);
        $rCO2Result = $this->query($rCO2Q);

        // CO2 저장할 데이터 추가 배열 선언
        $infoCO2Data = [];
        // 인덱스 초기화
        $infoCO2Index = 0;
        // 일 카운트
        $dateCount = 0;

        for ($i = 0, $year = $startYear; $year <= $endYear; $i++, $year++) {
            $CO2 = $rCO2Result[$infoCO2Index];
            $co2ValYear = $CO2['ym'];
            $standardVal = $co2StandData[0]['val'];

            if ($year == $co2ValYear) {
                $val = $CO2['val'];
                $infoCO2Index = ($infoCO2Index+1);
                // 그래프에 출력되는 만큼만 카운트
                $dateCount = $dateCount + 1;
            } else {
                $val = 0;
                $infoCO2Index = $infoCO2Index;
            }

            $temp = $year . '01';
            $displayDate = date('n', strtotime($temp)) . '월';

            // CO2 데이터 추가
            $infoCO2Data[] = [
                'date'=> $displayDate,
                'val'=> $val,
                'standard'=> $standardVal
            ];
        }

        return [
            'start_year'=> $startYear,
            'end_year'=> $endYear,
            'pm25'=> $infoPM25Data,
            'co2'=> $infoCO2Data,
        ];
    }

    /**
     * 실시간 미세먼지 상태 가져오기
     *
     * @param array $param
     *
     * @return array
     */
    public function getFinedustTodayStatus(array $param) : array
    {
        $pm25 = floor($param['pm25']);
        $co2 = floor($param['co2']);

        // 초미세먼지, CO2 상태
        $f25Result = $fCo2Result = '';
        $bg25 = $bgCo2 = '';
        $imoticon25 = $imoticonCo2 = '';

        $f25Data = $this->f25Data;
        $fCo2Data = $this->fCo2Data;

        if ($pm25 < $f25Data['good']['max']) {
            $f25Result = $f25Data['good']['status'];
            $bg25 = $f25Data['good']['img'];
            $imoticon25 = $f25Data['good']['imoticon'];
        }

        if ($pm25 >= $f25Data['normal']['min'] ) {
            if ($pm25 < $f25Data['normal']['max']) {
                $f25Result = $f25Data['normal']['status'];
                $bg25 = $f25Data['normal']['img'];
                $imoticon25 = $f25Data['normal']['imoticon'];
            }
        }

        if ($pm25 >= $f25Data['bad']['min'] ) {
            if ($pm25 < $f25Data['bad']['max']) {
                $f25Result = $f25Data['bad']['status'];
                $bg25 = $f25Data['bad']['img'];
                $imoticon25 = $f25Data['bad']['imoticon'];
            }
        }

        if ($pm25 >= $f25Data['v_bad']['min'] ) {
            $f25Result = $f25Data['v_bad']['status'];
            $bg25 = $f25Data['v_bad']['img'];
            $imoticon25 = $f25Data['v_bad']['imoticon'];
        }

        // CO2
        if ($co2 < $fCo2Data['v_good']['max']) {
            $fCo2Result = $fCo2Data['v_good']['status'];
            $bgCo2 = $fCo2Data['v_good']['img'];
            $imoticonCo2 = $fCo2Data['v_good']['imoticon'];
        }

        if ($co2 >= $fCo2Data['good']['min'] ) {
            if ($co2 < $fCo2Data['good']['max']) {
                $fCo2Result = $fCo2Data['good']['status'];
                $bgCo2 = $fCo2Data['good']['img'];
                $imoticonCo2 = $fCo2Data['good']['imoticon'];
            }
        }

        if ($co2 >= $fCo2Data['normal']['min'] ) {
            if ($co2 < $fCo2Data['normal']['max']) {
                $fCo2Result = $fCo2Data['normal']['status'];
                $bgCo2 = $fCo2Data['normal']['img'];
                $imoticonCo2 = $fCo2Data['normal']['imoticon'];
            }
        }

        if ($co2 >= $fCo2Data['bad']['min'] ) {
            if ($co2 < $fCo2Data['bad']['max']) {
                $fCo2Result = $fCo2Data['bad']['status'];
                $bgCo2 = $fCo2Data['bad']['img'];
                $imoticonCo2 = $fCo2Data['bad']['imoticon'];
            }
        }

        if ($co2 >= $fCo2Data['v_bad']['min'] ) {
            $fCo2Result = $fCo2Data['v_bad']['status'];
            $bgCo2 = $fCo2Data['v_bad']['img'];
            $imoticonCo2 = $fCo2Data['v_bad']['imoticon'];
        }

        return [
            'pm_25' => $pm25,
            'pm_25_result' => $f25Result,
            'bg_25' => $bg25,
            'imoticon_25' => $imoticon25,
            'co2' => $co2,
            'co2_result' => $fCo2Result,
            'bg_co2' => $bgCo2,
            'imoticon_co2' => $imoticonCo2,
        ];
    }
}