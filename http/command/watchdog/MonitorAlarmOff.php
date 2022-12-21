<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class MonitorAlarmOff 장애 발생 해제 크론탭
 */
class MonitorAlarmOff extends Command
{
    /**
     * MonitorAlarmOff constructor.
     */
	public function __construct()
    {
        parent::__construct();
	}

    /**
     * MonitorAlarmOff destructor.
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

            $query = '';
            $query = $this->emsQuery->getQueryCurrentAlarmFromAlarmLogTable($option,'on');
            $alarmList = $this->query($query);

            if (count($alarmList) > 0) {
                // 이미 발생한 알람
                $alarmCnt = count($alarmList);

                for ($loop = 0; $loop < $alarmCnt; $loop++) {
                    $alarmCode = $alarmList[$loop]['alarm_code'];
                    $sensorSn = $alarmList[$loop]['sensor_sn'];

                    $query = '';
                    $query = $this->emsQuery->getQueryIsNotExistAlarm($option, $sensorSn, $alarmCode);

                    if (empty($query)) {
                        continue;
                    }

                    $alarmExist = $this->query($query);

                    if (count($alarmExist) > 0) {
                        // 알람 해제
                        $query = '';

                        $complexPk = $alarmList[$loop]['complex_code_pk'];
                        $noAlarmLogPk = $alarmList[$loop]['no_alarm_log_pk'];

                        $alarmReleaseTime = $alarmExist[0]['val_date'];

                        if ($alarmCode === '0001') {
                            $this->sendTocData($option, [
                                'home_dong_pk' => $alarmList[$loop]['home_dong_pk'],
                                'home_ho_pk' => $alarmList[$loop]['home_ho_pk'],
                                'home_grp_pk' => $alarmExist[0]['home_grp_pk'],
                                'alarm_code' => $alarmCode,
                                'complex_code_pk' => $complexPk,
                                'arch_type' => $alarmExist[0]['arch_type'],
                                'sensor_sn' => $alarmExist[0]['sensor_sn'],
                                'toc_sensor_sn' => $alarmExist[0]['toc_sensor_sn'],
                                'occur_time' => $alarmList[$loop]['alarm_on_time'],
                                'solve_time' => $alarmReleaseTime,
                                'home_dong_cnt' => $alarmExist[0]['home_dong_cnt'],
                            ]);
                            $query = $this->emsQuery->getQueryAlarmRelease($complexPk, $option, $sensorSn, $alarmCode, 'off', $alarmReleaseTime, $noAlarmLogPk);
                            $this->squery($query);
                        } else if ($alarmCode === '0002') {
                            $cTime = strtotime('now');
                            $valDate = strtotime($alarmReleaseTime);
                            $differTime = $cTime - $valDate;

                            if ($differTime < 1800) {
                                $this->sendTocData($option, [
                                    'home_dong_pk' => $alarmList[$loop]['home_dong_pk'],
                                    'home_ho_pk' => $alarmList[$loop]['home_ho_pk'],
                                    'home_grp_pk' => $alarmExist[0]['home_grp_pk'],
                                    'alarm_code' => $alarmCode,
                                    'complex_code_pk' => $complexPk,
                                    'arch_type' => $alarmExist[0]['arch_type'],
                                    'sensor_sn' => $alarmExist[0]['sensor_sn'],
                                    'toc_sensor_sn' => $alarmExist[0]['toc_sensor_sn'],
                                    'occur_time' => $alarmList[$loop]['alarm_on_time'],
                                    'solve_time' => $alarmReleaseTime,
                                    'home_dong_cnt' => $alarmExist[0]['home_dong_cnt'],
                                ]);
                                $query = $this->emsQuery->getQueryAlarmRelease($complexPk, $option, $sensorSn, $alarmCode, 'off', $alarmReleaseTime, $noAlarmLogPk);
                                $this->squery($query);
                            }
                        }
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
            'occur_time' => $data['occur_time'],
            'solve_time' => $data['solve_time'],
            'location' => $locationName,
            'sensor_id' => $data['sensor_sn'],
            'sensor_type' => $sensorTypeName,
            'system' => $this->siteType,
        ];

        Utility::getInstance()->curlProcess($tocURL, $method, $httpHeaders, $fcData);
    }
}