<?php
/**
* 
* Jun 11, 2012
* GBK
* 3:16:11 PM
* AgudaZaric
* baseException.class.php
**/

class BaseException extends Exception{

	const LOGSIZE = 20480;
	public static $log_file_size = self::LOGSIZE;

	private $_Messgae,
			$_logPath,
			$_logFile;

	public function __construct( $message = null, $code = 0) {
		if( !defined("H_P") ) exit("Forbidden");

		parent::__construct($message, $code);
		$this->_logPath = H_P."/data/exception/";
		$this->_logFile = $this->_logPath.get_class($this).".php";
		$this->_Messgae = $message;

	}


	private function _getMessage(){
		$time = date("Y-m-d H:i:s", time());
		
		list($msec, $sec) = explode(' ', microtime());
		return $time.".".ceil($msec*1000)."\r\n".$this->__toString()."\r\n\r\n";
	}

	public function log() {
		if( false === file_exists($this->_logFile) )
			$this->writeover($this->_logFile, "<?php exit(0); ?>\r\n", "rb+");
		$fileSize = filesize($this->_logFile);
		
		if( $fileSize > self::LOGSIZE) {
			$d = dir($this->_logPath);
			while (false !== ($log = $d->read())) 
			{
				$entry[] = $log;
			}
			$d->close();
			$len = count($entry) - 2; //except ". .."
			$bakFile = $this->_logPath.get_class($this).'_'.$len.".php";
			$this->writeover($bakFile, file_get_contents($this->_logFile), "ab+");
			$this->writeover($this->_logFile, "<?php exit(0); ?>\r\n", "rb+");
		} 
		else {
			$this->writeover($this->_logFile,$this->_getMessage(), "ab+");
		}
	}

	function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true){
		$fileName = S::escapePath($fileName, $ifCheckPath);
		touch($fileName);
		$handle = fopen($fileName, $method);
		$ifLock && flock($handle, LOCK_EX);
		$writeCheck = fwrite($handle, $data);
		$method == 'rb+' && ftruncate($handle, strlen($data));
		fclose($handle);
		$ifChmod && @chmod($fileName, 0777);
		return $writeCheck;
	}

}