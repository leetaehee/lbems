<?php
namespace Http\Command;

use EMS_Module\Config;
use EMS_Module\Utility;

/**
 * Class HindranceExcel
 */
class HindranceExcel extends Command
{
    /**
     * HindranceExcel constructor.
     */
	public function __construct()
    {
		parent::__construct();
	}

    /**
     * HindranceExcel destructor.
     */
	public function __destruct()
    {
		parent::__destruct();
	}

    /**
     * 메인실행함수
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
            $hidranceAlarmData = [];
            $statusNames = [];
            $sensorTypeText = [];

            $this->sensorObj = $this->getSensorManager($complexCodePk);

			// 폼 데이터 받기
			parse_str($params[0]['formData'], $formArray);

			$formArray = Utility::getInstance()->removeXSSFromFormData($formArray);

            // 장애알람 조회
            if (is_null($this->sensorObj) === false) {
                $sensorTypeText = $this->sensorObj->getHindranceAlarmSensor();
            }

            if (in_array($complexCodePk, Config::FACTORY_USE_GROUP) === true) {
                $isUseFactory = true;
                $selectStatusType = $formArray['frame_select_status_type'];

                $statusNames = $this->sensorObj->getFactorySensorAboutStatusName();

                $formArray['sensor_sn'] = $sensorTypeText[$selectStatusType]['sensor_sn'];
            }

			// 알람 로그 가져오기
			$rHindranceAlarmLogQ = $this->emsQuery->getHindranceAlarmExcel($complexCodePk, $formArray);
			$rHindranceAlarmLogData = $this->query($rHindranceAlarmLogQ);

			for ($i = 0, $seq = 1; $i < count($rHindranceAlarmLogData); $i++, $seq++) {
				$logData = $rHindranceAlarmLogData[$i];

				$sensorType = $logData['sensor_type'];
				$alarmOffTime = $logData['alarm_off_time'];

                $sensorNo = $logData['sensor_sn'];

                if (in_array($complexCodePk, Config::FACTORY_USE_GROUP) === true) {
                    $sensorType = $statusNames[$sensorNo];
                }

				$hidranceAlarmData[] = [
                    'seq' => $seq,
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
		}

		$data['is_exist_data'] = $isExistSession;

		$this->data = $data;
		
		return true;
	}
}