<?php
define('ConfigFile', dirname(__FILE__)."/../../{$EnvFilePath}");

/**
 * Class ElectricMariaDB 전기요금 DB 커넥션
 */
class ElectricMariaDB
{
    private $db;

    private $message;

    private $data;

    private $dbHost = '';

    private $dbPort = '';

    private $dbId = '';

    private $dbPasswd = '';

    private $dbSid = '';

    /**
     * ElectricMariaDB constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $devOptions = parse_ini_file(ConfigFile);

        $this->db = null;
        $this->dbHost = $devOptions['FEE_DB_HOST'];
        $this->dbPort = $devOptions['FEE_DB_PORT'];
        $this->dbId = $devOptions['FEE_DB_ID'];
        $this->dbPasswd = $devOptions['FEE_DB_PASSWORD'];
        $this->dbSid = $devOptions['FEE_DB_SID'];

        $this->connect();
    }

    /**
     * ElectricMariaDB destructor.
     */
    public function __destruct()
    {
        if ($this->isConnected() == true) {
            $this->close();
        }

        $this->db = null;
    }

    public function connect()
    {
        $dbHost = $this->dbHost;
        $dbPort = $this->dbPort;
        $dbId = $this->dbId;
        $dbPasswd = $this->dbPasswd;
        $dbSid = $this->dbSid;

        $this->db = mysqli_connect($dbHost, $dbId, $dbPasswd, $dbSid, $dbPort);

        if ($this->isConnected() === false) {
            throw new Exception('connection error.');
        }

        $link = mysqli_select_db($this->db, $dbSid);

        if (!$link) {
            $this->close();
        }
    }

    public function close()
    {
        if ($this->isConnected() == true) {
            mysqli_close($this->db);
            return;
        }

        $this->db = null;
    }

    public function isConnected()
    {
        return !($this->db === null || $this->db === false);
    }

    public function querys($query)
    {
        if ($this->isConnected() == false) {
            $this->message = 'No Connection';
            return false;
        }

        $result = mysqli_query($this->db, $query);

        if (!$result) {
            $this->message = mysqli_error($this->db);
            return false;
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        $this->data = $rows;

        if($result != true)
            mysqli_free_result($result);

        return $rows;
    }

    public function squery($query)
    {
        if ($this->isConnected() == false) {
            $this->message = 'No Connection';
            return false;
        }

        $result = mysqli_query($this->db, $query);

        if (!$result) {
            $t = mysqli_error($this->db);
            $this->message = mysqli_error($this->db);
            return false;
        }

        if ($result != true) {
            mysqli_free_result($result);
        }

        return $result;
    }
}