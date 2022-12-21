<?php
namespace Http\Command;

/**
 * Class ArrangeFinedustMonth
 */
class ArrangeFinedustMonth extends Command 
{
	/**
	 * ArrangeFinedustMonth constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * ArrangeFinedustMonth destructor.
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
		/**
		 * 쿼리
		 * daily에서 전날 데이터에 대해서 구하기  (3개 테이블 모두 조회)
		 * 미세먼지 종류별(3개) month에 넣기
		 */
		$currentMonth = date('Ymd');

		$preMonth = date('Ymd', strtotime('-1 month', strtotime($currentMonth)));
		$preFinalDay = date('t', strtotime($preMonth));
		if ($preFinalDay < 10) {
			$preFinalDay = '0' . $preFinalDay;
		}

		// 날짜 포맷 Ym 형식으로 변경 
		$preMonth = date('Ym', strtotime('-1 month', strtotime($currentMonth)));
		
		$startDate = $preMonth . '01';
		$endDate = $preMonth . $preFinalDay;

		// 일통계 테이블 정의 
		$dailyFinedustTable = [
			'pm10'=> 'bems_stat_daily_finedust',
			'pm25'=> 'bems_stat_daily_finedust_ultra',
			'pm1_0'=> 'bems_stat_daily_finedust_ultra_1'
		];

		// 미세먼지(PM10) 일별 합계 데이터 추출
		$statMonthPM10P = [
			'table'=> $dailyFinedustTable['pm10'],
			'start_date'=> $startDate,
			'end_date'=> $endDate
		];

		$rStatMonthPM10Q = $this->emsQuery->getInfoFineDustDaily($statMonthPM10P);
		$rFM10Result = $this->query($rStatMonthPM10Q);

		// 초미세먼지(PM25) 일별 합계 데이터 추출
		$statMonthPM25P = [
			'table'=> $dailyFinedustTable['pm25'],
			'start_date'=> $startDate,
			'end_date'=> $endDate
		];

		$rStatMonthPM25Q = $this->emsQuery->getInfoFineDustDaily($statMonthPM25P);
		$rFM25Result = $this->query($rStatMonthPM25Q);

		// 극초미세먼지(PM1_0) 일별 합계 데이터 추출
		$statMonthPM1P = [
			'table'=> $dailyFinedustTable['pm1_0'],
			'start_date'=> $startDate,
			'end_date'=> $endDate
		];

		$rStatMonthPM1Q = $this->emsQuery->getInfoFineDustDaily($statMonthPM1P);
		$rFM1Result = $this->query($rStatMonthPM1Q);

		// 일별 데이터 저장
		$fm10Data = $fm25Data = $fm1Data = [];
		// 값
		$fm10Val = $fm25Val = $fm1Val = 0;

		// 미세먼지(PM10)
		for ($i = 0; $i < count($rFM10Result); $i++) {
			$fm10Rslt = $rFM10Result[$i];

			$fm10Data[] = [
				'sensor_sn'=> $fm10Rslt['sensor_sn'],
				'val_date'=> $fm10Rslt['val_date'],
				'val'=> $fm10Rslt['val'],
				'max_val'=> $fm10Rslt['max_val'],
				'min_val'=> $fm10Rslt['min_val']
			];
		}

		// 초미세먼지(PM25)
		for ($i = 0; $i < count($rFM25Result); $i++) {
			$fm25Rslt = $rFM25Result[$i];

			$fm25Data[] = [
				'sensor_sn'=> $fm25Rslt['sensor_sn'],
				'val_date'=> $fm25Rslt['val_date'],
				'val'=> $fm25Rslt['val'],
				'max_val'=> $fm25Rslt['max_val'],
				'min_val'=> $fm25Rslt['min_val']
			];
		}

		// 극초미세먼지(PM1_0)
		for ($i = 0; $i < count($rFM1Result); $i++) {
			$fm1Rslt = $rFM1Result[$i];
		
			$fm1Data[] = [
				'sensor_sn'=> $fm1Rslt['sensor_sn'],
				'val_date'=> $fm1Rslt['val_date'],
				'val'=> $fm1Rslt['val'],
				'max_val'=> $fm1Rslt['max_val'],
				'min_val'=> $fm1Rslt['min_val']
			];
		}

		// 월통계 테이블 정의 
		$monthFinedustTable = [
			'pm10'=> 'bems_stat_month_finedust',
			'pm25'=> 'bems_stat_month_finedust_ultra',
			'pm1_0'=> 'bems_stat_month_finedust_ultra_1'
		];

		// 월통계추가(PM10)
		for ($i = 0; $i < count($fm10Data); $i++) {
			$fd = $fm10Data[$i];

			$sensorSn = $fd['sensor_sn'];

			// 이미 평균 값으로 들어가기 때문에 '월'통계는 안함 - 문제가 발생할 경우 /$preFinalDay 나눌 것
			$val = round($fd['val']);
			$maxVal = round($fd['max_val']);
			$minVal = round($fd['min_val']);

			$rMonthPM10P = [
				'table'=> $monthFinedustTable['pm10'],
				'ym'=> $preMonth,
				'start_date'=> $startDate,
				'end_date'=> $endDate,
				'closing_day'=> $preFinalDay,
				'sensor_sn'=> $sensorSn,
				'val'=> $val,
				'max_val'=> $maxVal,
				'min_val'=> $minVal
			];
			$rMonthPM10Q = $this->emsQuery->insertInfoFineDustMonth($rMonthPM10P);
			$this->squery($rMonthPM10Q);
		}

		// 월통계추가(PM25)
		for ($i = 0; $i < count($fm25Data); $i++) {
			$fd = $fm25Data[$i];

			$sensorSn = $fd['sensor_sn'];
			
			// 이미 평균 값으로 들어가기 때문에 '월'통계는 안함 - 문제가 발생할 경우 /$preFinalDay 나눌 것
			$val = round($fd['val']);
			$maxVal = round($fd['max_val']);
			$minVal = round($fd['min_val']);

			$rMonthPM25P = [
				'table'=> $monthFinedustTable['pm25'],
				'ym'=> $preMonth,
				'start_date'=> $startDate,
				'end_date'=> $endDate,
				'closing_day'=> $preFinalDay,
				'sensor_sn'=> $sensorSn,
				'val'=> $val,
				'max_val'=> $maxVal,
				'min_val'=> $minVal
			];

			$rMonthPM25Q = $this->emsQuery->insertInfoFineDustMonth($rMonthPM25P);
			$this->squery($rMonthPM25Q);
		}

		// 월통계추가(PM1_0)
		for ($i = 0; $i < count($fm1Data); $i++) {
			$fd = $fm1Data[$i];

			$sensorSn = $fd['sensor_sn'];

			// 이미 평균 값으로 들어가기 때문에 '월'통계는 안함 - 문제가 발생할 경우 /$preFinalDay 나눌 것
			$val = round($fd['val']);
			$maxVal = round($fd['max_val']);
			$minVal = round($fd['min_val']);

			$rMonthPM25P = [
				'table'=> $monthFinedustTable['pm1_0'],
				'ym'=> $preMonth,
				'start_date'=> $startDate,
				'end_date'=> $endDate,
				'closing_day'=> $preFinalDay,
				'sensor_sn'=> $sensorSn,
				'val'=> $val,
				'max_val'=> $maxVal,
				'min_val'=> $minVal
			];

			$rMonthPM1Q = $this->emsQuery->insertInfoFineDustMonth($rMonthPM25P);
			$this->squery($rMonthPM1Q);
		}

		$data['result'] = true;
		$this->data = $data;

		return true;
	}
}