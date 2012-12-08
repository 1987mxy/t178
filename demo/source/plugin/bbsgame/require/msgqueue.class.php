<?php
!defined("H_P") && exit("forbidden");
include (H_P."/require/msgqueue.abstract.php");

class msgqueueFactory {
// 	public static $instance;
	const OS_WIN = 'win',OS_NIX = '*nix';
	
	function init($msgtype) {
		if( $msgtype == self::OS_WIN ) {
			$instance = new msgQueueFile();
			return $instance; 
		}
		else if( $msgtype == self::OS_NIX ) {
			$instance = new msgQueueMem();
			return $instance;
		}
	}

}



class msgQueueFile  extends msgqueueAbstract {
	
	const CACHESIZE = 10240,
		  CACHEPATH = "data/msg/";
	
	
	private $_message,
			$_cacheFile,
			$_cachePath,
			$_cacheArray;
	
	function msgQueueFile() {
		$this->_cacheFile = H_P.self::CACHEPATH."submit.php";
		$this->_cachePath = H_P.self::CACHEPATH;
		$this->setCacheArray();
	}

	
	function send($message, $seqKey) {
		empty($seqKey) && $seqKey = time();
		if(empty($message)) return false;

		$this->_message = $message;
		$data = json_decode($this->submitStatisticsData($message), true);
		if ($data[result] != 1) {
			//�ύʧ��
			$this->_add(time(), $message);
			return false;
		}
		else{ //�Ƴ�����
			$this->_remove($seqKey);
			$this->_save();
			return true;
		}
		
	}
	
	
	private function _add($key, $message) {
		$this->_cacheArray[$key] = $message;
		$this->_save();
	}
	
	
	function setCacheArray() {
		$cacheArray = array();
		@include_once($this->_cacheFile);
		if(!$this->_cacheArray = unserialize($cacheArray))
			$this->_cacheArray = array();
	}
	
	private function _save() {
		
		if( filesize($this->_cacheFile) > self::CACHESIZE) {
			$d = dir($this->_cachePath);
			while (false !== ($log = $d->read())) {
				$entry[] = $log;
			}
			$d->close();
			$len = count($entry) - 2; 
			$this->writeover($this->_cachePath."submit_".$len.".php", $this->getPreparedCacheData(), "rb+");
			$this->writeover($this->_cacheFile, "", "rb+");
		}
		else {
			$this->writeover($this->_cacheFile,$this->getPreparedCacheData(), "rb+");
		}
	}
	
	function _remove($seq) {
		if(empty($this->_cacheArray)) return;
		if(array_key_exists($seq, $this->_cacheArray)) unset($this->_cacheArray[$seq]);
	}
	
	
	function getPreparedCacheData() {

		$str = "<?php\n\r";
		$str .= "!defined('H_P') && exit('forbidden');\n\r";
		$str .= "\$cacheArray ='". serialize($this->_cacheArray)."';\n\r";
		$str .= "?>";
		return $str;
	}
	
	function getCacheArray() {
		return $this->_cacheArray;
	}
	
}


//Just testing...Ahhh 
class msgQueueMem  extends msgqueueAbstract {
	
	private $_svmq,
			$_queue;
	
	public function setMsgKey($msgKey){
		$this->_svmq = msg_get_queue($msgKey);
	}
	
	function send( $msgtype , $message) {
	
		if(empty($message) || !isset($message['seq']) ) return false;
	
		$this->_message = $message;
		// 		if( msg_send($this->_queue, $msgtype, $message, true) ){
		if( $result = json_decode($this->submitStatisticsData($post), true) ) {
			$ret = intval($result['ret']);
			$ret = -2;
			if( $ret === -2 ) {
				//�ύʧ��
				$this->_add($message['seq'], $message);
				return false;
			}
			elseif( $ret === 0 ) {
				//pop out queue
				$this->_remove($message['seq'],$message);
				return true;
			}
		}
		else {
			return false;
		}
		$seq +=1;
		$this->writeover(H_P."data/requestSeq.txt", $seq);
		// 		}
	}
	
	
	function _add($key, $message){
		$this->_queue[$key] = $message;
		msg_send($this->_svmq, $key, $message); 
	}
	
	function _remove($key, $message) {
		msg_set_queue($this->_svmq);
	}
	
	function stat(){
		
	}
	
	function set($key, $variable) {}
	
}