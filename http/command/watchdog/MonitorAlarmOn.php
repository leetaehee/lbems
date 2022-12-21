<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class MonitorAlarmOn 장애 발생 크론탭
 */
class MonitorAlarmOn extends Command
{
    /**
     * MonitorAlarmOn constructor.
     */
	public function __construct()
    {
		parent::__construct();
	}

    /**
     * MonitorAlarmOn destructor.
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
        $tableCount = count(Config::SENSOR_TABLES);

        for ($i = 0; $i < $tableCount; $i++) {
			$option = $i;

			$alarmList = [];
            $alarmListCount = 0;
			for ($j = 0; $j < 2; $j++) {
                $query = '';
                $alarmCode = '';

                if ($j == 0) {
                    $query = $this->emsQuery->getQueryCurrentAlarmFromSensorTable($option, 'error_code');
                    $alarmCode = '0001'; //communication failure
                    $alarmAllList = $this->query($query);

                    if (empty($alarmAllList[0]) === false) {

                        $loopCnt = count($alarmAllList);

                        for ($z = 0; $z < $loopCnt; $z++) {

                            $alarmData = $alarmAllList[$z];

                            $alarmList[$alarmListCount]['complex_code_pk'] = $alarmData['complex_code_pk'];
                            $alarmList[$alarmListCount]['sensor_sn'] = $alarmData['sensor_sn'];
                            $alarmList[$alarmListCount]['val_date'] = $alarmData['val_date'];
                            $alarmList[$alarmListCount]['home_dong_pk'] = $alarmData['home_dong_pk'];
                            $alarmList[$alarmListCount]['home_ho_pk'] = $alarmData['home_ho_pk'];
                            $alarmList[$alarmListCount]['alarm_code'] = $alarmCode;
                            $alarmList[$alarmListCount]['home_grp_pk'] = $alarmData['home_grp_pk'];
                            $alarmList[$alarmListCount]['arch_type'] = $alarmData['arch_type'];
                            $alarmList[$alarmListCount]['toc_sensor_sn'] = $alarmData['toc_sensor_sn'];
                            $alarmList[$alarmListCount]['home_dong_cnt'] = $alarmData['home_dong_cnt'];

                            $this->sendTocData($option, $alarmList[$alarmListCount]);

                            $alarmListCount = $alarmListCount + 1;
                        }
                    }
                } else {
                    $query = $this->emsQuery->getQueryCurrentAlarmFromSensorTable($option, 'time_out');
                    $alarmCode = '0002'; // time out
                    $alarmAllList = $this->query($query);
                    if (!empty($alarmAllList[0])) {
                        $loopCnt = count($alarmAllList);

                        for ($z = 0; $z < $loopCnt; $z++) {
                            $alarmData = $alarmAllList[$z];

                            $cTime = strtotime('now');
                            $valDate = strtotime($alarmData['val_date']);
                            $differTime = $cTime - $valDate;

                            if ($differTime > 1800) {
                                $alarmList[$alarmListCount]['complex_code_pk'] = $alarmData['complex_code_pk'];
                                $alarmList[$alarmListCount]['sensor_sn'] = $alarmData['sensor_sn'];
                                $alarmList[$alarmListCount]['val_date'] = $alarmData['val_date'];
                                $alarmList[$alarmListCount]['home_dong_pk'] = $alarmData['home_dong_pk'];
                                $alarmList[$alarmListCount]['home_ho_pk'] = $alarmData['home_ho_pk'];
                                $alarmList[$alarmListCount]['alarm_code'] = $alarmCode;
                                $alarmList[$alarmListCount]['home_grp_pk'] = $alarmData['home_grp_pk'];
                                $alarmList[$alarmListCount]['arch_type'] = $alarmData['arch_type'];
                                $alarmList[$alarmListCount]['toc_sensor_sn'] = $alarmData['toc_sensor_sn'];
                                $alarmList[$alarmListCount]['home_dong_cnt'] = $alarmData['home_dong_cnt'];

                                $this->sendTocData($option, $alarmList[$alarmListCount]);

                                $alarmListCount = $alarmListCount + 1;
                            }
                        }
                    }
                }
            }

			$alarmCnt = count($alarmList);

			// 알람 발생했는지 check
			if (count($alarmList) > 0) {
				for ($loop=0; $loop < $alarmCnt; $loop++) {
					$complexPk = $alarmList[$loop]['complex_code_pk'];
					$sensorSn = $alarmList[$loop]['sensor_sn'];
					$homeDongPk = $alarmList[$loop]['home_dong_pk'];
					$homeHoPk = $alarmList[$loop]['home_ho_pk'];
					$alarmTime = $alarmList[$loop]['val_date'];
					$alarmCode = $alarmList[$loop]['alarm_code'];

					$query = '';
					$query = $this->emsQuery->getQueryIsExistAlarm($option, $sensorSn, $alarmCode, 'on', 'alarm_log');
					$alarmLogList = $this->query($query);

					// 장애코드 가져오기
					$query = '';
					$query = $this->emsQuery->getQueryAlarmCodeMsg($alarmCode);
					$alarmCodeMsgResult = $this->query($query);
					$alarmCodeMsg = $alarmCodeMsgResult[0]['alarm_msg'];

					if (count($alarmLogList) === 0) {
					    // 이전 발생한 Alarm이 존재하지 않음으로 insert
                        $query = '';
                        $query = $this->emsQuery->getQueryInsertAlarm($complexPk, $option, $sensorSn, $alarmCode, 'on', $alarmTime, $homeDongPk, $homeHoPk, $alarmCodeMsg);
                        $this->query($query);
					}     

				}
			}
		}

		$this->close();
		return true;
	}

	/**
     * TOC 데이터 전송
     *
     * @param int $option
     * @param array $data
     */
	private function sendTocData(int $option, array $data) : void
    {
        if ($this->isDevMode() === true) {
            return;
        }

        if (count($data) === 0) {
            // 없으면 curl 전송안함..
            return;
        }

        $tocURL = $this->devOptions['TOC_URL'] . '/set_error_log';
        $method = 'POST';

        $httpHeaders = [
            "toc-key:" . $this->devOptions['TOC_KEY']
        ];

        $nowDateTime = date('YmdHis');

        $sensorTypes = Config::SENSOR_TYPES;
        $sensorTypeNames = Config::SENSOR_TYPE_NAMES;

        $sensorType = $sensorTypes[$option];
        $sensorTypeName = $sensorTypeNames[$sensorType];

        if (in_array($option, Config::TOC_ENERGY_TYPE_ERROR_ITEMS) === false) {
            // 전기, 용도별 중 전기 관련된 항목, 태양광 추출..
            return;
        }

        if (empty($data['toc_sensor_sn']) === true) {
            // toc 대상만 보낼 것..
             return;
        }

        $dong = $data['home_dong_pk'];
        $floor = $data['home_grp_pk'];
        $floorName = Config::FLOOR_INFO[$floor];
        $alarmCode = $data['alarm_code'];
        $homeDongCnt = (int)$data['home_dong_cnt'];

        $locationName = ($homeDongCnt === 1)  ? $floorName : "{$dong}동 $floorName";
        $errorCode = $alarmCode === '0002' ? '0' : '1';

        $fcData = [
            'val_date' => $nowDateTime,
            'complex_code' => $data['complex_code_pk'],
            'arch_type' => $data['arch_type'],
            'error_code' => $errorCode,
            'occur_time' => $data['val_date'],
            'solve_time' => null,
            'location' => $locationName,
            'sensor_id' => $data['sensor_sn'],
            'sensor_type' => $sensorTypeName,
            'system' => $this->siteType,
        ];

        Utility::getInstance()->curlProcess($tocURL, $method, $httpHeaders, $fcData);
    }
}