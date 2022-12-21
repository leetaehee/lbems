<?php
namespace Module;

/**
 * Class FileCache 파일 캐시 모듈
 */
class FileCache implements CacheInterface
{
    /** @var string|null $fileName 파일명 */
    private ?string $fileName = null;

    /** @var resource|null $filePointer */
    private $filePointer = null;

    /** @var string $upDirectory 상위 디렉토리 */
    private string $upDirectory = '/kevin/lbems/cache/';

    /** @var string $filePath 파일 전체 경로 */
    private string $filePath;

    /** @var string $extension 확장자명 */
    private string $extension;

    /** @var string $arrayKeyName 캐시를 담는 배열의 키 값 */
    private string$arrayKeyName = 'saveData';

    /** @var int|600 $cacheTime 캐시 시간 체크 */
    private int $cacheTime = 600;

    /**
     * FileCache 생성자.
     *
     * @param string $fileName
     * @param string $fileDirectory
     * @param string $extension
     */
    public function __construct(string $fileName, string $fileDirectory, string $extension = 'php')
    {
        $envOptions = parse_ini_file(ConfigFile);
        $tmpCachePath = $envOptions['CACHE_FILE_PATH'];

        if (isset($tmpCachePath) === true && empty($tmpCachePath) === false) {
            $this->upDirectory = $tmpCachePath;
        }

        $this->fileName = $fileName;
        $this->upDirectory .= $fileDirectory;
        $this->extension = $extension;
        $this->filePath = $this->upDirectory .'/' . $this->fileName . '.' .$this->extension;
    }

    /**
     * 캐시 저장하고자 하는 디렉토리가 존재하는지 확인
     *
     * @param string $directoryPath
     *
     * @return bool
     */
    private function isCheckDirectory(string $directoryPath) : bool
    {
        if (is_dir($directoryPath) === true) {
            return true;
        }
        
        return false;
    }

    /**
     * 파일 열기
     *
     * @param string $filePath
     *
     * @return resource
     */
    private function fileOpen(string $filePath)
    {
        return fopen($filePath, 'w+');
    }

    /**
     * 파일쓰기
     *
     * @param string $data
     */
    private function fileWrite(string $data) : void
    {
        // 배열의 키 값 받기
        $saveArrays = $this->arrayKeyName;
        $directoryPath = $this->upDirectory;

        $filePointer = $this->filePointer;

        if ($this->isCheckDirectory($directoryPath) == false) {
            // 폴더 없으면 생성 할 것
            mkdir($directoryPath, 0777, true);
        }

        fwrite($filePointer, '<?php ');
        fwrite($filePointer, '$' . $saveArrays . '= unserialize("' . $data . '");');
        fwrite($filePointer, ' ?>');
    }

    /**
     * 캐시 파일이 만들어진지 10분이 지났는지 체크
     * 
     * @return bool
     */
    public function isCheckTimeByMakeCacheFile() : bool
    {
        $filePath = $this->filePath;
        $cacheTime = $this->cacheTime;

        if (file_exists($filePath) == true) {
            $nowTime = strtotime(date('YmdHis'));
            $lastModifyTime = strtotime(date('YmdHis', filemtime($filePath)));

            $differTime = ($nowTime - $lastModifyTime);
            if ($differTime < $cacheTime) {
                // 캐쉬한지 정해진 시간 미만 일 때는 진행하지 않는다.
                return false;
            }
        }
        
        return true;
    }

    /**
     * 파일 닫기
     */
    private function fileClose() : void
    {
        fclose($this->filePointer);
    }

    /**
     * 배열로 된 데이터를 직렬화
     *
     * @param array $data
     *
     * @return string
     */
    private function onSerialize(array $data) : string
    {
        return str_replace('"', '\"', serialize($data));
    }

    /**
     * 역직렬화를 통해 배열로 변환
     *
     * @param $data
     *
     * @return string
     */
    private function onUnSerialize($data)
    {
        return unserialize($data);
    }

    /**
     * 파일에 캐시를 하기
     *
     * @param array $data
     *
     */
    public function cacheFileWrite(array $data) : void
    {
        // 직렬화
        $serializeData = $this->onSerialize($data);

        // 캐시경로 설정하기
        $filePath = $this->filePath;
        $this->filePointer = $this->fileOpen($filePath, 'w+');

        // 파일쓰기
        $this->fileWrite($serializeData);

        // 파일 종료
        $this->fileClose();
    }

	/**
	 * 캐시 저장 키 이름 변경 
	 *
	 * @param string $keyName
	 */
	public function setArrayKeyName(string $keyName) : void
	{
		$this->arrayKeyName = $keyName;
	}

    /**
     * 파일에 캐시데이터 추가
     *
     * @param resource $filePointer
     */
    public function cacheWrite(resource $filePointer) : void
    {
        /**
         * watchdog 디렉토리에서 file_cache_module 를 상속받은 클래스 생성
         * 해당 함수 override 하여 구현토록 할 것
         * fileCacheModule.cacheWrite()에 구현 하지 말것 (예외 처리 할 것)
         * 크론탭에서 사용 가능하도록 파일열기-닫기 함수는 부르기 execute()안에서...
         */
    }

    /**
     * 캐시 읽어오기
     *
     * @param string $filePath
     *
     * @return array $saveData
     */
    private function cacheRead(string $filePath) : array
    {
        // 배열의 키 값 받기
        $saveKeyName = $this->arrayKeyName;

        // 배열 초기화
        $saveData = [];

        if (file_exists($filePath) === true) {
            include_once $filePath;

			$saveData = $$saveKeyName;
        }

        return $saveData;
    }

    /**
     * 캐시 로드
     *
     * @return array
     */
    public function cacheLoad() : array
    {
        // 파일명 포함한 경로
        $filePath = $this->filePath;

        // 캐시 데이터 가져오기
        return $this->cacheRead($filePath);
    }
}