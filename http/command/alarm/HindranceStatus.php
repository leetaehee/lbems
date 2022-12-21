<?php
namespace Http\Command;

/**
 * Class HindranceStatus
 */
class HindranceStatus extends Command
{
    /**
     * HindranceStatus constructor.
     */
	 public function __construct()
     {
		parent::__construct();
     }

    /**
     * HindranceStatus destructor.
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

		$rHindranceAlarmExistQ = $this->emsQuery->getHindranceAlarmExistCount($complexCodePk);
		$rHindranceAlarmResult = $this->query($rHindranceAlarmExistQ);

		$alarmCount = $rHindranceAlarmResult[0]['alarm_on_off'];

		$data['hindrance_alram_count'] = $alarmCount;

		$this->data = $data;
		
		return true;
     }
}
