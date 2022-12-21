<?php
namespace Http\Command;

use EMS_Module\Config;

class FileDownload extends Command
{
    /** @var string[] $files type에 따라 해당되는 파일명 조회 */
    private array $files = [
        'manual' => [
            'download_name' => '건물에너지관리 시스템 L-BEMS 1.0.0 매뉴얼',
            'file_name' => 'manual.pdf',
            'path' => '../res/download',
            'use_timestamp' => false
        ],
    ];

    /** @var string[] $buildingManuals 건물별 매뉴얼 이름  */
    private array $buildingManuals = [
        '2002' => 'manual.pdf',
        '2003' => 'manual.pdf',
    ];

    /**
     * CacheDailyTimeMeter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * CacheDailyTimeMeter destructor.
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
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $complexCodePk = $_SESSION['ss_complex_pk'];
        $fileType = isset($params[0]['value']) === true ? $params[0]['value'] : 'manual';

        $fileData = $this->getFileData($complexCodePk, $fileType);
        if (count($fileData) === 0) {
            return false;
        }

        // 다운로드
        $this->onDownload($fileData);

        $this->data = $data;
        return true;
    }

    /**
     * 타입에 따른 파일 다운로드 하고자 하는 파일명 조회
     *
     * @param string $complexCodePk
     * @param string $type
     *
     * @return array
     */
    private function getFileData(string $complexCodePk, string $type) : array
    {
        $fcData = [];

        $files = $this->files;
        $files = $files[$type];

        $buildingManuals = $this->buildingManuals;

        if (isset($files[$type]) === true && empty($files[$type]) === false) {
            return $fcData; // 유효성 검증..
        }

        $fileName = $buildingManuals[$complexCodePk];
        if (empty($fileName) === true) {
            return $fcData;
        }

        $filePath = $files['path'];
        $downloadName = $files['download_name'];
        $useTimestamp = $files['use_timestamp'];

        $allowExtends = Config::DOWNLOAD_ALLOW_EXTENSIONS;
        $fileNameExtensions = explode('.', $fileName);
        $ext = $fileNameExtensions[1];

        if (in_array($ext, $allowExtends) === false) {
            return $fcData;
        }

        $fcData = [
            'file_name' => $fileName,
            'download_name' => $downloadName,
            'ext' => $ext,
            'use_timestamp' => $useTimestamp,
            'file_path' => "{$filePath}/{$fileName}"
        ];

        return $fcData;
    }

    /**
     * 파일 다운로드 실행
     *
     * @param array $fileData
     */
    private function onDownload(array $fileData) : void
    {
        $ext = $fileData['ext'];
        $downloadName = $fileData['download_name'];
        $filePath = $fileData['file_path'];

        $fileSize = fileSize($filePath);
        $currentTimestamp = $fileData['use_timestamp'] === true ? "_".date('YmdHis') : '';

        $fileName = "{$downloadName}{$currentTimestamp}.{$ext}";

        header("Pragma: public");
        header("Expires: 0");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition:attachment;filename={$fileName}");
        header("Content-Transfer-Encoding:binary");
        header("Content-Length: {$fileSize}");

        ob_clean();
        flush();
        readfile($filePath);
    }
}