<?php
namespace Http\Command;

use  Http\SensorManager;

use Database\DbModule;

use EMS_Module\EMSQuery;
use EMS_Module\Config;
use EMS_Module\SensorInterface;
use EMS_Module\MobileQuery;
use EMS_Module\ElectricPriceQuery;
use EMS_Module\Utility;

/**
 * Class Command
 */
abstract class Command 
{
    /** @var DbModule|null $db */
	protected ?DbModule $db = null;

	/** @var  string|null $message */
	protected ?string $message = null;

	/** @var array $data */
	protected array $data = [];

	/** @var EmsQuery|null $emsQuery */
	protected ?EMSQuery $emsQuery = null;

    /** @var MobileQuery|null $mobileQuery */
    protected ?MobileQuery $mobileQuery = null;

    /** @var ElectricPriceQuery|null $electricPriceQuery */
    protected ?ElectricPriceQuery $electricPriceQuery = null;

	/** @var SensorManager|null $sensorManager */
	protected ?SensorManager $sensorManager = null;

    /** @var Object|null $sensorObj */
    protected ?Object $sensorObj = null;

    /** @var array $devOptions */
    protected array $devOptions = [];

    /** @var string|null $siteType 사이트 타입 */
    protected ?string $siteType = null;

    /** @var array $baseDate 기준일자 */
    protected array $baseDateInfo = [];

    /**
     * Command constructor.
     */
	public function __construct() 
	{
        $this->sensorManager = new SensorManager();
		$this->emsQuery = new EMSQuery();
        $this->mobileQuery = new MobileQuery();
        $this->electricPriceQuery = new ElectricPriceQuery();
		$this->db = new DbModule();

        $this->devOptions = parse_ini_file(dirname(__FILE__) . '/../../.env');
        $this->baseDateInfo = Utility::getInstance()->getBaseDate();

        $this->setSiteType();
	}

    /**
     * Command destructor.
     */
	public function __destruct() 
	{
		$this->db = null;
	}

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool|null
     */
	abstract public function execute(array $params) :? bool;

    /**
     * select
     *
     * @param string $query
     *
     * @return array
     *
     * @throws \Exception
     */
	public function query(string $query) : array
	{
		if ($this->db->querys($query) == false) {
			throw new \Exception($this->db->getMessage());
		}

		return $this->db->getData();
	}

    /**
     * insert/update/delete
     *
     * @param string $query
     *
     * @throws \Exception
     */
	public function squery(string $query) : void
    {
		if ($this->db->squery($query) == false) {
			throw new \Exception($this->db->getMessage());
		}
	}

    /**
     * db close 실행
     *
     * @return bool
     */
	public function close() : bool
	{
		return $this->db->close();
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
     * 결과 데이터 반환
     *
     * @return array
     */
	public function getData() : array
	{
		return $this->data;
	}

    /**
     * 건물 센서 객체 반환
     *
     * @param string $complexCodePk
     *
     * @return SensorInterface
     */
	public function getSensorManager(string $complexCodePk) :SensorInterface
    {
        return $this->sensorManager->getSensorObject($complexCodePk);
    }

    /**
     * 건물 테스트를 위한 건물코드 변경
     *
     * @param string $complexCodePk
     *
     * @return string
     */
    public function getSettingComplexCodePk(string $complexCodePk) : string
    {
        $fcComplexCodePk = $complexCodePk;

        switch ($fcComplexCodePk) {
            case '2002':
                //$fcComplexCodePk = '2001';
                break;
            case '2006':
                //$fcComplexCodePk = '2003';
                break;
            case '9999':
                $fcComplexCodePk = '2002';
                break;
        }

        return $fcComplexCodePk;
    }

    /**
     * 사이트 타입 설정
     */
    public function setSiteType() : void
    {
        $devOptions = $this->devOptions;
        $siteType = $devOptions['SITE_TYPE'];

        $this->siteType = empty($siteType) === true ? Config::SYSTEM_TYPE : $siteType;
    }

    /**
     * 개발모드인지 확인 후 true/false 반환
     *
     * @return bool
     */
    public function isDevMode() : bool
    {
        $isDevMode = true;
        $devOptions = $this->devOptions;

        if ($devOptions['IS_DEV'] === '0') {
            $isDevMode = false;
        }

        return $isDevMode;
    }
}