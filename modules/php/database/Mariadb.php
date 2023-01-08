<?php
namespace Database;

/**
 * Class Mariadb
 */
class Mariadb extends Database
{
    /**
     * Mariadb constructor.
     *
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbId
     * @param string $dbPasswd
     * @param string $dbSid
     */
	public function __construct(string $dbHost, string $dbPort, string $dbId, string $dbPasswd, string $dbSid)
    {
		$this->db = null;
		parent::__construct($dbHost, $dbPort, $dbId, $dbPasswd, $dbSid);
	}

    /**
     * Mariadb destructor.
     */
	public function __destruct()
    {
		parent::__destruct();
	}

    /**
     * database Connection 실행
     */
	public function connect() : void
    {
		$dbHost = $this->dbHost;
		$dbPort = $this->dbPort;
		$dbId = $this->dbId;
		$dbPasswd = $this->dbPasswd;
		$dbSid = $this->dbSid;

		$dns = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = ${dbHost})(PORT = ${dbPort}))(CONNECT_DATA = (SID = ${dbSid})) )";

		$this->db = mysqli_connect($dbHost, $dbId, $dbPasswd, $dbSid, $dbPort);
		mysqli_set_charset($this->db, 'utf8');
		$link = mysqli_select_db($this->db, $dbSid);

		if (!$link) {
			$this->close();
		}
	}

    /**
     * db close 실행
     *
     * @return bool
     */
	public function close() : bool
    {
		if ($this->isConnected() == true) {
        	mysqli_close($this->db);
			return false;
		}

		$this->db = null;

		return true;
	}

    /**
     * 커넥션 체크
     *
     * @return bool
     */
	public function isConnected() : bool
    {
		return !($this->db === null || $this->db === false);
	}

    /**
     * select 실행 (복수)
     *
     * @param string $query
     *
     * @return bool
     */
	public function querys(string $query) : bool
    {
		if ($this->isConnected() == false) {
			$this->message = ErrConnection;
			return false;
		}

		$result = mysqli_query($this->db, $query);

		// Check result
		// This shows the actual query sent to MySQL, and the error. Useful for debugging.
		if (!$result) {
			$this->message = mysqli_error($this->db);
			$this->message .= $this->dbHost;
			return false;
		}

		$rows = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}

		$this->data = $rows;

		if ($result != true) {
            mysqli_free_result($result);
        }

		return true;
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
		if ($this->isConnected() == false) {
			$this->message = ErrConnection;
			return false;
		}

		$result = mysqli_query($this->db, $query);

		// Check result
		// This shows the actual query sent to MySQL, and the error. Useful for debugging.
		if (!$result) {
			$t = mysqli_error($this->db);
			$this->message = mysqli_error($this->db);
			return false;
		}

		if ($result != true) {
            mysqli_free_result($result);
        }

		return true;
	}
}