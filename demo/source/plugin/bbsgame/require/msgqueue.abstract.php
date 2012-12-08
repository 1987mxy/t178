<?php
abstract class msgqueueAbstract {
	public abstract function send( $msgtype , $message);
// 	public abstract function receice( $queue , $desiredmsgtype , &$msgtype , $maxsize , &$message ,  $unserialize = true );
// 	public abstract function remove();
// 	public abstract function set();
// 	public abstract function stat();

	public function setMsgKey($msgKey){
		$svmq = msg_get_queue();
		return $svmq;
	}
	
	public function submitStatisticsData($post_data) {
		global $db_charset;
		
		foreach($post_data as $key=>$value) {
			$db_charset =='gbk' && $post_data[$key] = mb_convert_encoding($value, "UTF-8", "GB2312");
		}
		$data_string = http_build_query($post_data);
		
		$opts = array(
				  'http'=>array(
				    'method'=>"POST",
				    'header'=>"Content-type: application/x-www-form-urlencoded",
				    'content'=>"$data_string"
			)
		);
		$context = stream_context_create($opts);
		if(function_exists('fopen')) {
			$fp = fopen(API_SUBMIT_URL,"r",false,$context);
			$inheader = 1;
			$line = '';
			while (!feof($fp)) {
				$line .= fgets($fp,1024);
				if ($inheader && ($line == "\n" || $line == "\r\n")) {
					$inheader = 0;
				}
			}
			fclose($fp);
		}
		else{
			$line = file_get_contents(API_SUBMIT_URL.'?'.$data_string);
		}
		if($line)
			return $line;
		else
			return null;
	}
	
	
	/**
	* ??????
	*
	* @param string $fileName ??????·??
	* @param string $method ?????
	*/
	public function readover($fileName, $method = 'rb') {
		$fileName = S::escapePath($fileName);
		$data = '';
		if ($handle = @fopen($fileName, $method)) {
			flock($handle, LOCK_SH);
			$data = @fread($handle, filesize($fileName));
			fclose($handle);
		}
		return $data;
	}
	
	public 	function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
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