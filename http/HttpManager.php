<?php
namespace Http;

use Http\Parser\IParser;
use Http\Parser\LoginParser;
use Http\Parser\BuildingParser;
use Http\Parser\ReportParser;
use Http\Parser\AnalysisParser;
use Http\Parser\WatchdogParser;
use Http\Parser\DashboardParser;
use Http\Parser\WeatherParser;
use Http\Parser\ControlParser;
use Http\Parser\PredictionParser;
use Http\Parser\FacilityParser;
use Http\Parser\AlarmParser;
use Http\Parser\InfoParser;
use Http\Parser\DiagramParser;
use Http\Parser\SetParser;
use Http\Parser\ManagerParser;
use Http\Parser\MenuParser;
use Http\Parser\SolarParser;
use Http\Parser\PaperParser;
use Http\Parser\AccountParser;
use Http\Parser\AuthParser;
use Http\Parser\TestParser;
use Http\Parser\MigrationParser;
use Http\Parser\CacheParser;
use Http\Parser\HomeParser;
use Http\Parser\FrameParser;
use Http\Parser\CommonParser;
use Http\Parser\CalendarParser;
use Http\Parser\IntegrationParser;

/**
 * Class HttpManager
 */
class HttpManager 
{
    /** @var string $message */
	private string $message = '';

	/** @var array $data */
	private array $data = [];

    /**
     * HttpManager constructor.
     */
	public function __construct()
	{
		$this->message = '';
		$this->data = [];
	}

    /**
     * Parser 객체 반환
     *
     * @param string $requester
     *
     * @return IParser
     */
	private function getResponder(string $requester) : IParser
	{
		$responder = null;

		switch ($requester) {
            case 'login':
                $responder = new LoginParser();
                break;
            case 'building':
                $responder = new BuildingParser();
                break;
            case 'report':
                $responder = new ReportParser();
                break;
            case 'analysis':
                $responder = new AnalysisParser();
                break;
            case 'watchdog':
                $responder = new WatchdogParser();
                break;
            case 'dashboard':
                $responder = new DashboardParser();
                break;
            case 'weather':
                $responder = new WeatherParser();
                break;
            case 'control':
                $responder = new ControlParser();
                break;
            case 'prediction':
                $responder = new PredictionParser();
                break;
            case 'facility':
                $responder = new FacilityParser();
                break;
            case 'alarm':
                $responder = new AlarmParser();
                break;
            case 'info':
                $responder = new InfoParser();
                break;
            case 'diagram':
                $responder = new DiagramParser();
                break;
            case 'set':
                $responder = new SetParser();
                break;
            case 'manager':
                $responder = new ManagerParser();
                break;
            case 'menu' :
                $responder = new MenuParser();
                break;
            case 'solar':
                $responder = new SolarParser();
                break;
            case 'paper':
                $responder = new PaperParser();
                break;
            case 'account':
                $responder = new AccountParser();
                break;
            case 'auth':
                $responder = new AuthParser();
                break;
            case 'test':
                $responder = new TestParser();
                break;
            case 'migration':
                $responder = new MigrationParser();
                break;
            case 'cache':
                $responder = new CacheParser();
                break;
            case 'home':
                $responder = new HomeParser();
                break;
            case 'frame':
                $responder = new FrameParser();
                break;
            case 'common':
                $responder = new CommonParser();
                break;
            case 'calendar':
                $responder = new CalendarParser();
                break;
            case 'integration':
                $responder = new IntegrationParser();
                break;
        }

        return $responder;
	}

    /**
     * Command 객체 반환
     *
     * @param string $requester
     * @param string $request
     * @param array $params
     *
     * @return bool
     */
	private function parseRequest(string $requester, string $request, array $params) : bool
	{
        $this->message = '';
		$this->data = [];

		$responder = $this->getResponder($requester);

		if ($responder == null) {
			$this->message = ErrNoResponder;
			return false;
		}

		$command = $responder->getCommand($request);

		if ($command == null) {
			$this->message = ErrWrongRequest;
			return false;
		}

		$result = $command->execute($params);

		if ($result == false) {
			$this->message = $command->getMessage();
			return false;
		}

		$this->data = $command->getData();

		return true;
	}

    /**
     * 컨트롤러 반환
     *
     * @param string $requester
     * @param string $request
     * @param array $params
     *
     * @return bool
     */
	public function parse(string $requester, string $request, array $params) : bool
	{
		$ret = false;

		try {
			$ret = $this->parseRequest($requester, $request, $params);
		} catch(\Exception $e) {
			$this->message = $e->getMessage();
			$ret = false;
		}

		return $ret;
	}

    /**
     * 메세지 출력
     *
     * @return string|null
     */
	public function getMessage() :? string
	{
		return $this->message;
	}

    /**
     * 반환 데이터 조회
     *
     * @return array
     */
	public function getData() : array
	{
		return $this->data;
	}
}
