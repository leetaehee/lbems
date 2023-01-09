<?php
namespace Database;
/**
 * Class Database 데이터베이스 추상화 클래스
 */
abstract class Database
{
    /** @var Database|null $db 데이터베이스 커넥션 정보  */
	protected $db = null;

    /** @var string $message */
	protected string $message = '';

    /** @var array $data */
	protected array $data = [];

    /** @var string $dbHost */
	protected string $dbHost = '';

    /** @var string $dbPort */
	protected string $dbPort = '';

    /** @var string $dbId */
	protected string $dbId = '';

    /** @var string $dbPasswd */
	protected string $dbPasswd = '';

    /** @var string $dbSid */
	protected string $dbSid = '';

    /**
     * Database constructor.
     *
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbId
     * @param string $dbPasswd
     * @param string $dbSid
     */
	public function __construct(string $dbHost, string $dbPort, string $dbId, string $dbPasswd, string $dbSid)
	{
		$this->dbHost = $dbHost;
		$this->dbPort = $dbPort;
		$this->dbId = $dbId;
		$this->dbPasswd = $dbPasswd;
		$this->dbSid = $dbSid;
		
		$this->connect();
	}

    /**
     * Database destructor.
     */
	public function __destruct()
	{
		if ($this->isConnected() == true) {
			$this->close();
		}

		$this->db = null;
	}

    /**
     * database Connection 실행
     */
	abstract public function connect() : void;

    /**
     * db close 실행
     *
     * @return bool
     */
	abstract public function close() : bool;

    /**
     * 커넥션 체크
     *
     * @return bool
     */
	abstract public function isConnected() : bool;

    /**
     * select 실행
     *
     * @param string $query
     *
     * @return bool
     */
    abstract public function query(string $query) : bool;

    /**
     * select 실행 (복수)
     *
     * @param string $query
     *
     * @return bool
     */
	abstract public function querys(string $query) : bool;

    /**
     * insert/update/delete 실행
     *
     * @param string $query
     *
     * @return bool
     */
	abstract public function squery(string $query) : bool;

    /**
     * 메세지 출력
     *
     * @return string
     */
	public function getMessage() : string
	{
		return $this->message;
	}

    /**
     * 결과 데이터 반환
     *
     * @return array
     */
	public function getData() : array
	{
		return $this->data;
	}
}