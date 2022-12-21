<?php
namespace Http\Command;

/**
 * Class ArrangeFinedustDay
 */
class ArrangeFinedustDay extends Command
{
    /**
     * ArrangeFinedustDay constructor.
     */
	public function __construct()
    {
		parent::__construct();
	}

    /**
     * ArrangeFinedustDay destructor.
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
		$startHour = 0;
		$endHour = 23;

		$fdData = [];

		for ($hour = $startHour; $hour <= $endHour; $hour++) {
			$today = date('Y-m-d');

			// 전일
			$preDay = date('Y-m-d', strtotime('-1 day', strtotime($today)));

			if ($hour < 10) {
				$hour = '0' . $hour;
			}

			$preDay .= ' ' . $hour . ':';

			// 시간대별 합계 데이터 추출
			$rFinedustQ = $this->emsQuery->getInfoFineDustMeter($preDay);
			$rFdResult = $this->query($rFinedustQ);

			$fdCount = count($rFdResult);
			if ($fdCount > 0) {
				for ($i = 0; $i < $fdCount; $i++) {
					$fdData[$i]['device_eui'] = $rFdResult[$i]['device_eui'];
					
					$fdData[$i][$hour] = [
						'pm10'=> $rFdResult[$i]['pm10'],
						'pm25'=> $rFdResult[$i]['pm25'],
						'pm1_0'=> $rFdResult[$i]['pm1_0']
					];
				}
			}
		}

		// 전일로 계산 (Ymd 형식)
		$preDay = date('Ymd', strtotime('-1 day', strtotime($today)));

		// daily 데이터 추가하기
		$fdDataCount = count($fdData);
		if ($fdDataCount > 0) {
			$dailyFinedustTable = [
				'pm10'=> 'bems_stat_daily_finedust',
				'pm25'=> 'bems_stat_daily_finedust_ultra',
				'pm1_0'=> 'bems_stat_daily_finedust_ultra_1'
			];

			for ($i = 0; $i < $fdDataCount; $i++) {
				$fd = $fdData[$i];
				$deviceEui = $fdData[$i]['device_eui'];

				$pm10 = [
					'val_0'=> $this->setEmptyValueToZero($fd['00']['pm10']),
					'val_1'=> $this->setEmptyValueToZero($fd['01']['pm10']),
					'val_2'=> $this->setEmptyValueToZero($fd['02']['pm10']),
					'val_3'=> $this->setEmptyValueToZero($fd['03']['pm10']),
					'val_4'=> $this->setEmptyValueToZero($fd['04']['pm10']),
					'val_5'=> $this->setEmptyValueToZero($fd['05']['pm10']),
					'val_6'=> $this->setEmptyValueToZero($fd['06']['pm10']),
					'val_7'=> $this->setEmptyValueToZero($fd['07']['pm10']),
					'val_8'=> $this->setEmptyValueToZero($fd['08']['pm10']),
					'val_9'=> $this->setEmptyValueToZero($fd['09']['pm10']),
					'val_10'=> $this->setEmptyValueToZero($fd['10']['pm10']),
					'val_11'=> $this->setEmptyValueToZero($fd['11']['pm10']),
					'val_12'=> $this->setEmptyValueToZero($fd['12']['pm10']),
					'val_13'=> $this->setEmptyValueToZero($fd['13']['pm10']),
                    'val_14'=> $this->setEmptyValueToZero($fd['14']['pm10']),
					'val_15'=> $this->setEmptyValueToZero($fd['15']['pm10']),
					'val_16'=> $this->setEmptyValueToZero($fd['16']['pm10']),
					'val_17'=> $this->setEmptyValueToZero($fd['17']['pm10']),
					'val_18'=> $this->setEmptyValueToZero($fd['18']['pm10']),
					'val_19'=> $this->setEmptyValueToZero($fd['19']['pm10']),
					'val_20'=> $this->setEmptyValueToZero($fd['20']['pm10']),
					'val_21'=> $this->setEmptyValueToZero($fd['21']['pm10']),
					'val_22'=> $this->setEmptyValueToZero($fd['22']['pm10']),
					'val_23'=> $this->setEmptyValueToZero($fd['23']['pm10'])
				];

				// 미세먼지 일통계 데이터 추가
				$cFinedustPM10P = [
					'device_eui'=> $deviceEui,
					'pre_day'=> $preDay,
					'table'=> $dailyFinedustTable['pm10'],
					'data'=> $pm10
				];

				$cFinedustPM10Q = $this->emsQuery->insertInfoFineDustDaily($cFinedustPM10P);
				$this->squery($cFinedustPM10Q);

				$pm25 = [
					'val_0'=> $this->setEmptyValueToZero($fd['00']['pm25']),
					'val_1'=> $this->setEmptyValueToZero($fd['01']['pm25']),
					'val_2'=> $this->setEmptyValueToZero($fd['02']['pm25']),
					'val_3'=> $this->setEmptyValueToZero($fd['03']['pm25']),
					'val_4'=> $this->setEmptyValueToZero($fd['04']['pm25']),
					'val_5'=> $this->setEmptyValueToZero($fd['05']['pm25']),
					'val_6'=> $this->setEmptyValueToZero($fd['06']['pm25']),
					'val_7'=> $this->setEmptyValueToZero($fd['07']['pm25']),
					'val_8'=> $this->setEmptyValueToZero($fd['08']['pm25']),
					'val_9'=> $this->setEmptyValueToZero($fd['09']['pm25']),
					'val_10'=> $this->setEmptyValueToZero($fd['10']['pm25']),
					'val_11'=> $this->setEmptyValueToZero($fd['11']['pm25']),
					'val_12'=> $this->setEmptyValueToZero($fd['12']['pm25']),
					'val_13'=> $this->setEmptyValueToZero($fd['13']['pm25']),
					'val_14'=> $this->setEmptyValueToZero($fd['14']['pm25']),
					'val_15'=> $this->setEmptyValueToZero($fd['15']['pm25']),
					'val_16'=> $this->setEmptyValueToZero($fd['16']['pm25']),
					'val_17'=> $this->setEmptyValueToZero($fd['17']['pm25']),
					'val_18'=> $this->setEmptyValueToZero($fd['18']['pm25']),
					'val_19'=> $this->setEmptyValueToZero($fd['19']['pm25']),
					'val_20'=> $this->setEmptyValueToZero($fd['20']['pm25']),
					'val_21'=> $this->setEmptyValueToZero($fd['21']['pm25']),
					'val_22'=> $this->setEmptyValueToZero($fd['22']['pm25']),
					'val_23'=> $this->setEmptyValueToZero($fd['23']['pm25'])
				];

				// 초미세먼지 일통계 데이터 추가
				$cFinedustPM25P = [
					'device_eui'=> $deviceEui,
					'pre_day'=> $preDay,
					'table'=> $dailyFinedustTable['pm25'],
					'data'=> $pm25
				];

				$cFinedustPM25Q = $this->emsQuery->insertInfoFineDustDaily($cFinedustPM25P);
                $this->squery($cFinedustPM25Q);

				$pm1 = [
					'val_0'=> $this->setEmptyValueToZero($fd['00']['pm1_0']),
					'val_1'=> $this->setEmptyValueToZero($fd['01']['pm1_0']),
					'val_2'=> $this->setEmptyValueToZero($fd['02']['pm1_0']),
					'val_3'=> $this->setEmptyValueToZero($fd['03']['pm1_0']),
					'val_4'=> $this->setEmptyValueToZero($fd['04']['pm1_0']),
					'val_5'=> $this->setEmptyValueToZero($fd['05']['pm1_0']),
					'val_6'=> $this->setEmptyValueToZero($fd['06']['pm1_0']),
					'val_7'=> $this->setEmptyValueToZero($fd['07']['pm1_0']),
					'val_8'=> $this->setEmptyValueToZero($fd['08']['pm1_0']),
					'val_9'=> $this->setEmptyValueToZero($fd['09']['pm1_0']),
					'val_10'=> $this->setEmptyValueToZero($fd['10']['pm1_0']),
					'val_11'=> $this->setEmptyValueToZero($fd['11']['pm1_0']),
					'val_12'=> $this->setEmptyValueToZero($fd['12']['pm1_0']),
					'val_13'=> $this->setEmptyValueToZero($fd['13']['pm1_0']),
					'val_14'=> $this->setEmptyValueToZero($fd['14']['pm1_0']),
					'val_15'=> $this->setEmptyValueToZero($fd['15']['pm1_0']),
					'val_16'=> $this->setEmptyValueToZero($fd['16']['pm1_0']),
					'val_17'=> $this->setEmptyValueToZero($fd['17']['pm1_0']),
					'val_18'=> $this->setEmptyValueToZero($fd['18']['pm1_0']),
					'val_19'=> $this->setEmptyValueToZero($fd['19']['pm1_0']),
					'val_20'=> $this->setEmptyValueToZero($fd['20']['pm1_0']),
					'val_21'=> $this->setEmptyValueToZero($fd['21']['pm1_0']),
					'val_22'=> $this->setEmptyValueToZero($fd['22']['pm1_0']),
					'val_23'=> $this->setEmptyValueToZero($fd['23']['pm1_0'])
				];

				// 극초미세먼지 일통계 데이터 추가
				$cFinedustPM1P = [
					'device_eui'=> $deviceEui,
					'pre_day'=> $preDay,
					'table'=> $dailyFinedustTable['pm1_0'],
					'data'=> $pm1
				];

				$cFinedustPM1Q = $this->emsQuery->insertInfoFineDustDaily($cFinedustPM1P);
                $this->squery($cFinedustPM1Q);
			}
		}

		$data['result'] = true;
		$this->data = $data;

		return true;
	}

    /**
     * 빈 값이 있는 경우 0으로 처리
     *
     * @param string|null $value
     * @return int
     */
	private function setEmptyValueToZero(?string $value) : int
    {
        return empty($value) === true ? 0 : $value;
    }
}