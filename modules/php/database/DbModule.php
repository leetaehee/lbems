<?php
namespace Database;

define('dbConfigFile', dirname(__FILE__) . "/../../{$EnvFilePath}");

/**
 * Class DbModule 데이터베이스 선택 클래스
 */
class DbModule
{
    /** @var Database|null $database */
	private ?Database $database = null;

    /** @var string $dbType */
	private string $dbType = '';

    /** @var string $dbHost */
	private string $dbHost = '';

    /** @var string $dbPort  */
	private string $dbPort = '';

    /** @var string $dbId */
	private string $dbId = '';

    /** @var string $dbPasswd  */
	private string $dbPasswd = '';

    /** @var string $dbSid */
	private string $dbSid = '';

    /** @var array|null $tmpDbInfo */
	private ?array $tmpDbInfo;

    /**
     * DbModule constructor.
     *
     * @param array|null $dbInfo
     *
     * @throws \Exception
     */
	public function __construct(array $dbInfo = null)
    {
		$this->tmpDbInfo = $dbInfo;
		$this->initDatabase();
	}

    /**
     * DbModule destructor.
     */
	public function __destruct()
    {
		$this->database = null;
	}

    /**
     * 데이터 베이스 객체 생성
     */
	private function createDatabase() : void
    {
		if ($this->dbType == 'mariadb') {
			$this->database = new Mariadb($this->dbHost, $this->dbPort, $this->dbId, $this->dbPasswd, $this->dbSid);
		} else {
			throw new \Exception(ErrDbType);
		}
	}

    /**
     * 데이터 베이스 커넥션  설정 초기화
     *
     * @throws \Exception
     */
	private function initDatabase() : void
    {
		$this->readConfig();
		$this->createDatabase();

		if ($this->database->isConnected() == false) {
			throw new \Exception(ErrConnection);
		}
	}

    /**
     * 커넥션 정보 조회
     */
	private function readConfig() : void
    {
		/**
         * 데이터베이스 생성정보를 배열인 경우와 파일로 받는 경우 분리
         */
		$dbInfo = $this->tmpDbInfo;
		if (is_array($dbInfo)) {
			$ini_array = $dbInfo;
		} else {
			$ini_array = parse_ini_file(dbConfigFile);
		}

		if ($ini_array === null) {
			throw new \Exception(ErrReadConfig);
		}

		$this->dbType = $ini_array['DB_TYPE'];
		$this->dbHost = $ini_array['DB_HOST'];
		$this->dbPort = $ini_array['DB_PORT'];
		$this->dbId = $ini_array['DB_ID'];
		$this->dbPasswd = $ini_array['DB_PASSWORD'];
		$this->dbSid = $ini_array['DB_SID'];

		if ($this->dbType == '' || $this->dbHost == '' || $this->dbPort == '' || $this->dbId == ''
            || $this->dbPasswd == '' || $this->dbSid == '') {
		    throw new \Exception(ErrReadConfig);
		}
	}

    /**
     * select 실행
     *
     * @param string $query
     *
     * @return bool
     */
	public function querys(string $query) : bool
    {
		return $this->database->querys($query);
	}

    /**
     * insert/update/delete 실행
     *
     * @param string $query
     *
     * @return bool
     */
	public function squery(string $query) : bool
    {
		return $this->database->squery($query);
	}

    /**
     * 메세지 출력
     *
     * @return string
     */
	public function getMessage() : string
    {
		return $this->database->getMessage();
	}

    /**
     * 결과 데이터 반환
     *
     * @return array
     */
	public function getData() : array
    {
		return $this->database->getData();
	}

    /**
     * db close 실행
     *
     * @return bool
     */
	public function close() : bool
    {
		return $this->database->close();
	}
}