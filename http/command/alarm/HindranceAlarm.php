<?php
namespace Http\Command;

use EMS_Module\Utility;
use EMS_Module\Config;

/**
 * Class HindranceAlarm
 */
class HindranceAlarm extends Command
{
    /**
     * HindranceAlarm constructor.
     */
	public function __construct()
    {
		parent::__construct();
	}

    /**
     * HindranceAlarm destructor.
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
		$complexCodePk = $_SESSION['ss_complex_pk'];
		$isExistSession = true;

		if (empty($complexCodePk)){
			// 세션이 끊긴 경우 조회 하지 않는다.
			$isExistSession = false;
		} else {
			$formArray = [];
            $statusNames = [];
            $sensorTypeText = [];
            $hidranceAlarmData = [];

            $this->sensorObj = $this->getSensorManager($complexCodePk);

            $isUseFactory = false;

			// 폼 데이터 받기
			parse_str($params[0]['formData'], $formArray);
            $formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

			// 페이징정보
			$startPage = $params[0]['start_page']-1;
			$endPage = $params[0]['view_page_count'];
			$viewPageCount = $params[0]['view_page_count'];
			if ($startPage < 1) {
				$startPage = 0;
			} else {
				$startPage = $startPage * $endPage;
			}

            // 장애알람 조회
            if (is_null($this->sensorObj) === false) {
                $sensorTypeText = $this->sensorObj->getHindranceAlarmSensor();
            }

			// 알람 카운트
			$rHindranceAlarmExistQ = $this->emsQuery->getHindranceAlarmExistCount($complexCodePk);
			$rHindranceAlarmResult = $this->query($rHindranceAlarmExistQ);

			$alarmCount = $rHindranceAlarmResult[0]['alarm_on_off'];

            if (in_array($complexCodePk, Config::FACTORY_USE_GROUP) === true) {
                $isUseFactory = true;
                $selectStatusType = $formArray['frame_select_status_type'];

                $statusNames = $this->sensorObj->getFactorySensorAboutStatusName();

                $formArray['sensor_sn'] = $sensorTypeText[$selectStatusType]['sensor_sn'];
            }

			// 알람 로그 가져오기
			$rHindranceAlarmLogQ = $this->emsQuery->getHindranceAlarmLog($complexCodePk, $formArray, $startPage, $endPage);
			$rHindranceAlarmLogData = $this->query($rHindranceAlarmLogQ);

			// 현재 리스트 되는 알람 카운트 (발생, 해제 포함)
			$rHindranceAlarmCount = $this->emsQuery->getHindranceAlarmCount($complexCodePk, $formArray);
			$hindranceAlarmCount = $this->query($rHindranceAlarmCount);

			if ($params[0]['start_page'] == 1) {
				$seq = 1;
			} else {
				$seq = (($viewPageCount * $params[0]['start_page']) - $viewPageCount) +  1;
			}

			for ($i = 0; $i < count($rHindranceAlarmLogData); $i++) {
				$logData = $rHindranceAlarmLogData[$i];

				$sensorType = $logData['sensor_type'];
				$alarmOffTime = $logData['alarm_off_time'];

				$sensorNo = $logData['sensor_sn'];

                if ($isUseFactory === true) {
                    $sensorType = $statusNames[$sensorNo];
                }

				$hidranceAlarmData[] = [
					'seq' => $seq++,
					'sensor_sn' => $logData['sensor_sn'],
					'sensor_type' => $sensorTypeText[$sensorType]['name'],
					'alarm_msg' => $logData['alarm_msg'],
					'home_dong_pk' => $logData['home_dong_pk'],
					'home_ho_pk' => $logData['home_ho_pk'],
                    'home_grp_pk' => $logData['home_grp_pk'],
					'alarm_on_time' => $logData['alarm_on_time'],
					'alarm_off_time' => empty($alarmOffTime) ? '-' : $alarmOffTime,
					'reg_date' => $logData['reg_date'],
					'complex_name' => Utility::getInstance()->updateDecryption($logData['name']),
					'alarm_on_off' => $logData['alarm_on_off']
				];
			}

			$data['hindrance_alram_log'] = $hidranceAlarmData;
			$data['hindrance_alram_count'] = $alarmCount;
			$data['count'] = $hindranceAlarmCount[0]['cnt'];
		}

		$data['is_exist_data'] = $isExistSession;

		$this->data = $data;
		
		return true;
	}
}