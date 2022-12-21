<?php
namespace Module;

/**
 * Class Excel 엑셀
 */
 class Excel
 {
	/** @var string|null $fileName 파일명 */
	private $fileName = null;
	
	/** @var string|null $extension 확장자 */
	private $extension = null;

	/** @var string|null $realFileName 파일 이름과 확장자를 합친 이름 */
	private $realFileName = null;

	/** @var PHPExcel|null $excelObj 엑셀 객체 */
	private $excelObj = null;

	/**
	 * Excel constructor.
	 *
	 * @param PHPExcel $excelObj
	 * @param string $fileName
	 * @param string $extension
	 */
	public function __construct(\PHPExcel $excelObj, string $fileName, string $extension = 'xls')
	{
		$this->excelObj = $excelObj;
		$this->fileName = $fileName;
		$this->extension = $extension;
		$this->realFileName .= $this->fileName . '.' . $this->extension; 
	}

	/** 
	 * Excel destructor.
	 */
	public function __destruct(){ }

     /**
      * Excel Download
      *
      * @throws \PHPExcel_Reader_Exception
      * @throws \PHPExcel_Writer_Exception
      */
	public function onDownload()
	{
		$excelObj = $this->excelObj;
		$realFileName = $this->realFileName;

		$objWriter = \PHPExcel_IOFactory::createWriter($excelObj, 'Excel5');
		ob_end_clean();

        header("Content-Type:application/vnd.ms-excel");
        header("content-Disposition: attachment; filename={$realFileName}");
        header("Cache-Control:max-age=0");

		$objWriter->save('php://output');
	}
 }
