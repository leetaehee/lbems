<?php
namespace Http\Command;

/**
 * Class InfoFinedust
 */
class InfoFinedust extends Command
{
	// 미세먼지
    /** @var array|array[] $f10Data pm10 미세먼지 */
	private array $f10Data = [
		'good'=> [
			'min'=> 0,
			'max'=> 31,
			'status'=> '좋음',
			'img'=> 'dust_status_bg1',
			'imoticon'=> 'dust_status_img1'
		],
		'normal'=> [
			'min'=> 31,
			'max'=> 81,
			'status'=> '보통',
			'img'=> 'dust_status_bg2',
			'imoticon'=> 'dust_status_img2'
		],
		'bad'=> [
			'min'=> 81,
			'max'=> 151,
			'status'=> '나쁨',
			'img'=> 'dust_status_bg3',
			'imoticon'=> 'dust_status_img3'
		],
		'vbad'=> [
			'min'=> 151,
			'status'=> '매우나쁨',
			'img'=> 'dust_status_bg4',
			'imoticon'=> 'dust_status_img4'
		]
	];

	/** @var array|array[] pm25 미세먼지  */
	private array $f25Data = [
		'good'=> [
			'min'=> 0,
			'max'=> 16,
			'status'=> '좋음',
			'img'=> 'dust_status_bg1',
			'imoticon'=> 'dust_status_img1'
		],
		'normal'=> [
			'min'=> 16,
			'max'=> 51,
			'status'=> '보통',
			'img'=> 'dust_status_bg2',
			'imoticon'=> 'dust_status_img2'
		],
		'bad'=> [
			'min'=> 51,
			'max'=> 101,
			'status'=> '나쁨',
			'img'=> 'dust_status_bg3',
			'imoticon'=> 'dust_status_img3'
		],
		'vbad'=> [
			'min'=> 101,
			'status'=> '매우나쁨',
			'img'=> 'dust_status_bg4',
			'imoticon'=> 'dust_status_img4'
		]
	];

    /**
     * InfoFinedust constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * InfoFinedust destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     *
     * @param $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
	public function execute(array $params) :? bool
    {
        $complexCodePk = $_SESSION['ss_complex_pk'];
		$p = $params[0];

		$dailyData = $this->getFinedustDaily($complexCodePk);
		$monthData = $this->getFinedustMonth($complexCodePk);
		$yearData = $this->getFinedustYear($complexCodePk);

		$todayP = $dailyData['current'];
		$todayData = $this->getFinedustTodayStatus($todayP);

		$data['dailyFinedust'] = [
			'fd'=> $dailyData['data'],
			'today'=> $todayData
		];

		$data['monthFinedust'] = [
			'start_date'=> $monthData['start_date'],
			'end_date'=> $monthData['end_date'],
			'pm10'=> $monthData['pm10'],
			'pm25'=> $monthData['pm25'],
			'pm1_0'=> $monthData['pm1_0'],
		];

		$data['yearFinedust'] = [
			'start_year'=> $yearData['start_year'],
			'end_year'=> $yearData['end_year'],
			'pm10'=> $yearData['pm10'],
			'pm25'=> $yearData['pm25'],
			'pm1_0'=> $yearData['pm1_0'],
		];

		$this->data = $data;

		return true;
	}

    /**
     * 금일 미세먼지 조회 (미세먼지, 초미세먼지, 극초미세먼지)
     *
     * @param string $complexCodePk
     *
     * @return array 금일 실시간 미세먼지 정보와 기준값 가짐
     *
     * @throws \Exception
     */
	public function getFinedustDaily(string $complexCodePk) : array
	{
		$startHour = 0;
        $endHour = date('H', strtotime($this->baseDateInfo['date_time']));

		// 미세먼지 기준값 조회
        $rStandardQ = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_finedust');
        $standData = $this->query($rStandardQ);
        $st = explode('/', $standData[0]['val']);
		
		$fs = $st[0];
		$fsu = $st[1];
		$fsu1 = $st[2];

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

			$fdCount = count($rFdResult);

			$hourKeyName = 'hour' . $hour;

			$fdData['deviceui'] = $rFdResult[0]['device_eui'];

			$fdData[$hourKeyName] = [
				'hour'=> $displayHour,
				'pm10'=> $rFdResult[0]['pm10'],
				'pm25'=> $rFdResult[0]['pm25'],
				'pm1_0'=> $rFdResult[0]['pm1_0'],
				'fs'=> $fs,
				'fsu'=> $fsu,
				'fsu1'=> $fsu1 
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

			$fdData[$hourKeyName] = [
				'hour'=> $displayHour,
				'pm10'=> 0,
				'pm25'=> 0,
				'pm1_0'=> 0,
				'fs'=> $fs,
				'fsu'=> $fsu,
				'fsu1'=> $fsu1
			];
		}

		return [ 
			'data'=> $fdData,
			'current'=> [
				'pm10'=> $currentFd['pm10'],
				'pm25'=> $currentFd['pm25'],
				'pm1_0'=> $currentFd['pm1_0'] 
			]
		];
	}

    /**
     * 금월 미세먼지 조회 (미세먼지, 초미세먼지, 극초미세먼지)
     *
     * @param string $complexCodePk
     *
     * @return array 금월 실시간 미세먼지 정보와 기준값 가짐
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

		// 미세먼지 기준값 조회
        $rStandardQ = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_finedust');
        $standData = $this->query($rStandardQ);
        $st = explode('/', $standData[0]['val']);
		
		$fs = $st[0];
		$fsu = $st[1];
		$fsu1 = $st[2];


		// 일통계 테이블 정의 
		$dailyFinedustTable = [
			'pm10'=> 'bems_stat_daily_finedust',
			'pm25'=> 'bems_stat_daily_finedust_ultra',
			'pm1_0'=> 'bems_stat_daily_finedust_ultra_1'
		];

		// 미세먼지(PM10) 조회
		$rPM10P = [
			'table'=> $dailyFinedustTable['pm10'],
			'start_date'=> $startDate,
			'end_date'=> $endDate,
			'complex_code_pk'=> $complexCodePk
		];

		$rPM10Q = $this->emsQuery->getInfoFineDustMonth($rPM10P);
		$rPM10Result = $this->query($rPM10Q);

		// PM10 저장할 데이터 추가 배열 선언 
		$infoPM10Data = [];
		// 인덱스 초기화
		$infoPM10Index = 0;
		// 일 카운트 
		$dateCount = 0;

		for ($i = 0, $date = $startDate; $date <= $endDate; $i++, $date++) {
			$pm10 = $rPM10Result[$infoPM10Index];
			$pm10ValDate = $pm10['val_date'];
			$standardVal = $fs;

			if ($date == $pm10ValDate) {
				$val = $pm10['val'];
				$infoPM10Index = ($infoPM10Index+1);
				// 그래프에 출력되는 만큼만 카운트
				$dateCount = $dateCount + 1;
			} else {
				$val = 0;
				$infoPM10Index = $infoPM10Index;
			}
			
			$displayDate = date('j', strtotime($date)) . '일';

			// 미세먼지(pm10) 데이터 추가 
			$infoPM10Data[] = [
				'date'=> $displayDate,
				'val'=> $val,
				'standard'=> $standardVal
			];
		}

		// 초미세먼지(PM25) 조회
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
			$standardVal = $fsu;

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

			// 미세먼지(pm25) 데이터 추가 
			$infoPM25Data[] = [
				'date'=> $displayDate,
				'val'=> $val,
				'standard'=> $standardVal
			];
		}

		// 극초미세먼지(PM1_0) 조회
		$rPM1P = [
			'table'=> $dailyFinedustTable['pm1_0'],
			'start_date'=> $startDate,
			'end_date'=> $endDate,
			'complex_code_pk'=> $complexCodePk
		];

		$rPM1Q = $this->emsQuery->getInfoFineDustMonth($rPM1P);
		$rPM1Result = $this->query($rPM1Q);

		// PM1_0 저장할 데이터 추가 배열 선언 
		$infoPM1Data = [];
		// 인덱스 초기화
		$infoPM1Index = 0;
		// 일 카운트 
		$dateCount = 0;

		for ($i = 0, $date = $startDate; $date <= $endDate; $i++, $date++) {
			$pm1 = $rPM1Result[$infoPM1Index];
			$pm1ValDate = $pm1['val_date'];
			$standardVal = $fsu1;

			if ($date == $pm1ValDate) {
				$val = $pm1['val'];
				$infoPM1Index = ($infoPM1Index+1);
				// 그래프에 출력되는 만큼만 카운트
				$dateCount = $dateCount + 1;
			} else {
				$val = 0;
				$infoPM1Index = $infoPM1Index;
			}
			
			$displayDate = date('j', strtotime($date)) . '일';

			// 미세먼지(pm1_0) 데이터 추가 
			$infoPM1Data[] = [
				'date'=> $displayDate,
				'val'=> $val,
				'standard'=> $standardVal
			];
		}

		return [
			'start_date'=> $startDate,
			'end_date'=> $endDate,
			'pm10'=> $infoPM10Data,
			'pm25'=> $infoPM25Data,
			'pm1_0'=> $infoPM1Data,
		];
	}

    /**
     * 금년 미세먼지 조회 (미세먼지, 초미세먼지, 극초미세먼지)
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

        // 미세먼지 기준값 조회
        $rStandardQ = $this->emsQuery->getQueryReference($complexCodePk,0,'limit_val_finedust');
        $standData = $this->query($rStandardQ);
        $st = explode('/', $standData[0]['val']);
		
		$fs = $st[0];
		$fsu = $st[1];
		$fsu1 = $st[2];

		// 월통계 테이블 정의 
		$monthFinedustTable = [
			'pm10'=> 'bems_stat_month_finedust',
			'pm25'=> 'bems_stat_month_finedust_ultra',
			'pm1_0'=> 'bems_stat_month_finedust_ultra_1'
		];

		// 미세먼지(PM10) 조회
		$rPM10P = [
			'table'=> $monthFinedustTable['pm10'],
			'start_year'=> $startYear,
			'end_year'=> $endYear,
			'complex_code_pk'=> $complexCodePk
		];

		$rPM10Q = $this->emsQuery->getInfoFineDustYear($rPM10P);
		$rPM10Result = $this->query($rPM10Q);

		// PM10 저장할 데이터 추가 배열 선언 
		$infoPM10Data = [];
		// 인덱스 초기화
		$infoPM10Index = 0;
		// 일 카운트 
		$dateCount = 0;

		for ($i = 0, $year = $startYear; $year <= $endYear; $i++, $year++) {
			$pm10 = $rPM10Result[$infoPM10Index];
			$pm10ValYear = $pm10['ym'];
			$standardVal = $fs;

			if ($year == $pm10ValYear) {
				$val = $pm10['val'];
				$infoPM10Index = ($infoPM10Index+1);
				// 그래프에 출력되는 만큼만 카운트
				$dateCount = $dateCount + 1;
			} else {
				$val = 0;
				$infoPM10Index = $infoPM10Index;
			}
			
			$temp = $year . '01';
			$displayDate = date('n', strtotime($temp)) . '월';

			// 미세먼지(pm10) 데이터 추가 
			$infoPM10Data[] = [
				'date'=> $displayDate,
				'val'=> $val,
				'standard'=> $standardVal
			];
		}

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
			$standardVal = $fsu;

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

		// 극초미세먼지(PM1_0) 조회
		$rPM1P = [
			'table'=> $monthFinedustTable['pm1_0'],
			'start_year'=> $startYear,
			'end_year'=> $endYear,
			'complex_code_pk'=> $complexCodePk
		];

		$rPM1Q = $this->emsQuery->getInfoFineDustYear($rPM1P);
		$rPM1Result = $this->query($rPM1Q);

		// PM1_0 저장할 데이터 추가 배열 선언 
		$infoPM1Data = [];
		// 인덱스 초기화
		$infoPM1Index = 0;
		// 일 카운트 
		$dateCount = 0;

		for ($i = 0, $year = $startYear; $year <= $endYear; $i++, $year++) {
			$pm1 = $rPM1Result[$infoPM1Index];
			$pm1ValYear = $pm1['ym'];
			$standardVal = $fsu1;

			if ($year == $pm1ValYear) {
				$val = $pm1['val'];
				$infoPM1Index = ($infoPM1Index+1);
				// 그래프에 출력되는 만큼만 카운트
				$dateCount = $dateCount + 1;
			} else {
				$val = 0;
				$infoPM1Index = $infoPM1Index;
			}
			
			$temp = $year . '01';
			$displayDate = date('n', strtotime($temp)) . '월';

			// 미세먼지(pm1_0) 데이터 추가 
			$infoPM1Data[] = [
				'date'=> $displayDate,
				'val'=> $val,
				'standard'=> $standardVal
			];
		}

		return [
			'start_year'=> $startYear,
			'end_year'=> $endYear,
			'pm10'=> $infoPM10Data,
			'pm25'=> $infoPM25Data,
			'pm1_0'=> $infoPM1Data,
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
		$pm10 = floor($param['pm10']);
		$pm25 = floor($param['pm25']);
		$pm1 = floor($param['pm1_0']);

		// 미세먼지, 초미세먼지 상태
		$f10Result = $f25Result = '';
		$bg10 = $bg25 = $bg = '';
		$imoticon10 = $imoticon25 = '';

		// 미세먼지 농도 
		$f10Data = $this->f10Data;
		if ($pm10 < $f10Data['good']['max']) {
			$f10Result = $f10Data['good']['status'];
			$bg10 = $f10Data['good']['img'];
			$imoticon10 = $f10Data['good']['imoticon'];
		}
		
		if ($pm10 >= $f10Data['normal']['min'] ) {
			if ($pm10 < $f10Data['normal']['max']) {
				$f10Result = $f10Data['normal']['status'];
				$bg10 = $f10Data['normal']['img'];
				$imoticon10 = $f10Data['normal']['imoticon'];
			}
		}

		if ($pm10 >= $f10Data['bad']['min'] ) {
			if ($pm10 < $f10Data['bad']['max']) {
				$f10Result = $f10Data['bad']['status'];
				$bg10 = $f10Data['bad']['img'];
				$imoticon10 = $f10Data['bad']['imoticon'];
			}
		}

		if ($pm10 >= $f10Data['vbad']['min']) {
			$f10Result = $f10Data['vbad']['status'];
			$bg10 = $f10Data['vbad']['img'];
			$imoticon10 = $f10Data['vbad']['imoticon'];
		}

		// 초미세먼지 농도 
		$f25Data = $this->f25Data;
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

		if ($pm25 >= $f25Data['vbad']['min'] ) {
			$f25Result = $f25Data['vbad']['status'];
			$bg25 = $f25Data['vbad']['img'];
			$imoticon25 = $f25Data['vbad']['imoticon'];
		}

		return [
			'pm10'=> number_format($pm10, 1),
			'pm10Result'=> $f10Result,
			'bg10'=> $bg10,
			'imoticon10'=> $imoticon10,
			'pm25'=> number_format($pm25, 1),
			'pm25Result'=> $f25Result,
			'bg25'=> $bg25,
			'imoticon25'=> $imoticon25,
			'pm1'=> number_format($pm1, 1)
		];
	}
}