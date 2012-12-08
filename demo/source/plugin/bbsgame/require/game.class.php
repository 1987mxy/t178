<?php
!defined('H_P') && exit('forbidden');
/*
 * game.class.php Created on 2012-2-16
 * TODO
 * author:AgudaZaric
 * QQ:384318815
 * msn:coderzl@hotmail.com
 *
 */

class game {

	private $_gid,
			$_type,
			$_shell,
			$gameDb,
			$fields;

	public	$server;

	public static $table_game = 'ng_game',
		   $table_ip = 'ng_game_ip',
		   $table_credit = 'ng_game_credit',
		   $table_type = 'ng_game_type',
		   $table_shell = "ng_game_shell",
		   $table_user = 'ng_game_user';

	const GAME_DB = "game",
		  CREDIT_DB = "credit",
		  SCORE_DB = "score";


	/**
	 *
	 * @param int $id
	 * @param array $shell
	 * @param array $type
	 *
	 * */

    function game($gid, $shell = '', $type = '') {

    	ng_baseLoader::loadDB('gameDb','require/DB');
    	$this->gameDb = new gameDb();

		$this->_gid = intval($gid);
		!empty($this->_gid) && $game = $this->getAll();
		if(is_array($game)){
			foreach($game as $key=>$var){
				$sdtr = "\$this->_$key=\"$var\";";
				eval("\$this->_$key=\"$var\";");
			}
		}
		$this->server = new server();
    }


    function _setInstall($install) {
		$this->_installed = $install;
    }

    function update($array) {
    	if(!is_array($array)) return false;
    	foreach( $array as $key=>$value ){
    		if($this->validateField($key) === false) continue;
			$hash[] = "`$key` = ".S::sqlEscape($value);
    	}
    	$sql = implode(",", $hash);
		$sql = "UPDATE ".self::$table_game." SET ".$sql." WHERE `gid`=$this->_gid";
    	$this->_query($sql);
    }

    function _delete(){
    	$sql = "DELETE FROM ".self::$table_game." WHERE `gid` = $this->_gid ";
    	$this->_query($sql);
    }

    private function _query($sql = '') {
    	if(!empty($sql)) {
			$this->gameDb->query($sql);
		} else {
			return false;
		}
    }

    private function _addTypeWithTypename($typeName) {
    	$this->gameDb->addType($typeName);
    	$this->_type['typeId'] = mysql_insert_id()? mysql_insert_id():1;
    	$this->_type['typeName'] = $typeName;
    }


    private function _addShellWithShellurl($shellUrl) {
    	$this->gameDb->addShell($shellUrl);
    	$this->_shell['shellId'] = $this->gameDb->getshellIdWithShellurl($shellUrl);
    	$this->_shell['shellUrl'] = $shellUrl;
    }

    function getShellIdWithShellurl($shellUrl) {
    	return $this->gameDb->getshellIdWithShellurl($shellUrl);
    }


    function setType($type) {
    	if(empty($type['id'])) throw new gameException("empty type id");
		$this->_type = $type;
    }

    function setShell($shell) {
    	if(empty($shell['id'])) throw new gameException("empty shell id");
    	$this->_shell = $shell;
    }


    function register($uid, $pid) {
    	if(empty($this->_gid)) throw new gameException("obj game killed");
    	if($this->server->register($uid ,$pid) !==false) {
	    	$sql = "insert into ".self::$table_user."(`pid`,`uid`, `register`) values($pid,".S::sqlEscape($uid).",1)";
			$this->gameDb->query($sql);
    	}
    }

	/**
	 *
	 * @param array $creditType
	 * @return boolean
	 */
    function validateConfig(array $creditType){
    	if(empty($this->_installed) || empty($this->_ctype) || array_key_exists($this->_ctype, $creditType) == false)
    		return false;
    	else
    		return true;
    }

    function validateField($key) {
    	empty($this->fields) && $this->fields = $this->gameDb->getAllGameFiledsWithGameId($this->_gid);
    	return key_exists($key, $this->fields);
    }

    function getName(){
    	return $this->_name;
    }

    function isType($typeName) {
    	$this->_type['typeId'] = $this->gameDb->getTypeIdWithTypeName($typeName);
    	if( empty($this->_type['typeId']) ) {
    		return false;
    	} else {
    		$this->_type['typeName'] = $typeName;
    		return true;
    	}
    }

    function isShell($shellUrl) {
    	$this->_shell['shellId'] = $this->gameDb->getShellIdWithShellUrl($shellUrl);

     	if( empty($this->_shell['shellId']) ) {
    		return false;
    	} else {
    		$this->_shell['shellUrl'] = $shellUrl;
    		return true;
    	}
    }

    function isUserExist($uid) {

    	$sql = "SELECT uid FROM ".self::$table_user." WHERE register = 1 AND uid = ".S::sqlEscape($uid);
    	if($this->gameDb->get_one($sql))
    		return true;
    	else
    		return false;
    }

	function typeCheckAndSet($typeName) {
		if($this->isType($typeName) == false)
			$this->_addTypeWithTypename($typeName);
		if($this->_type['typeId'])
			return true;
		else
			return false;
	}

	function shellCheckAndSet($shellUrl) {
		if($this->isShell($shellUrl) == false)
			$this->_addShellWithShellurl($shellUrl);
		if($this->_shell['shellId'])
			return true;
		else
			return false;
	}

	function install($gameInfo) {
		$gameInfo = S::sqlEscape($gameInfo,false,true);
		$sql = "INSERT INTO ng_game(`gid`, `name`, `describe`,`objective`, `operation`, `swf`, `url`,`img`,`type`,`shellid`,`installed`) " .
				"VALUES($gameInfo[gid],".$gameInfo[name].",".$gameInfo[describe].",".$gameInfo[objective].",".$gameInfo[operation].",".
				$gameInfo[swf].",".$gameInfo[url].",".$gameInfo[img].",".$gameInfo[typeId].",$gameInfo[shellId],1)";
		$this->_query($sql);
		return true;
	}

	function uninstall() {
		$info = array('installed'=>0,"describe"=>'','objective'=>'','operation'=>'','shellId'=>0);
		$this->update($info);
	}

 	function fileDownload($sourceFile, $hostName, $downloadPath) {
		$path = substr($sourceFile,0,strrpos($sourceFile,'/'));
		!file_exists($downloadPath.$path) && mkdirWithPath($downloadPath, $path, 0777);
		$newlocalswf = $downloadPath.$sourceFile;

		if(file_exists($newlocalswf)) @unlink($newlocalswf);

		touch($newlocalswf);
		$fp = fopen($newlocalswf,'w+');
		if(fwrite($fp,file_get_contents($hostName.'/'.$sourceFile)) && fclose($fp)) {
			return true;
		}else{
			return false;
		}

 	}

 	function deleteFile($path, $fileName) {
 		if(empty($fileName)) return;
		@unlink($path.$fileName);
 	}

 	function updateScore(){}

 	function getCtype(){
 		return $this->_ctype;
 	}

    function getTypeid() {
    	if(is_numeric($this->_type)) return $this->_type;
    	else return $this->_type['typeId'];
    }

    function getShellid() {
    	return $this->_shell['shellId'];
    }

    function getType() {
    	return $this->_type;
    }

    function getShellurl() {
    	if($this->_shell['shellUrl']) {
    		return $this->_shell['shellUrl'];
    	} else {
    		$result = $this->gameDb->getAllTypeFiledsWithGameId($this->_gid);
		if($result['url'])
			return $result['url'];
		else
			return null;
	    }
    }

    function getSwf() {

		$result = $this->gameDb->getAllGameFiledsWithGameId($this->_gid);
		if($result['swf'])
			return $result['swf'];
		else
			return null;

    }

    function getAll() {
		$strSQL = "SELECT g.*,s.url as shellUrl FROM self::{$table_game} g LEFT JOIN self::{$table_shell} s ON g.shellid = s.id WHERE gid = $this->_gid";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		return $arr;
    }

	function getPKAll() {
    	$strSQL = "SELECT gid, name FROM self::{$table_game} WHERE installed = 1";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		return $arr;
    }

    function getAffect($score, $cash){

    	if($score >= $this->_rate0)
    		return $this->_rate1;
    	else
    		return 0;
    }

    function getUseup(){
    	return $this->_useup *= -1;
    }

	function getRecommendsWithcType($ctype, $count = 10) {

		$sql = "SELECT g.*,s.url as shell FROM ".self::$table_game." g
				LEFT JOIN ".self::$table_shell." s ON g.shellId = s.id
				WHERE g.type = ".S::sqlEscape($ctype)." AND g.gid != $this->_gid
				LIMIT $count";
		$query = $this->gameDb->query($sql);
		return $this->gameDb->getRows($query);
	}

    function creditLog($uid, $affect, $type ,$score){

    	//if(empty($affect) || empty($uid)) return false;

    	$this->gameDb->query("INSERT INTO ".self::$table_credit."(`gid`,`uid`,`affect`,`type`,`score`,`addTime`) VALUES(".S::sqlEscape($this->_gid).",".S::sqlEscape($uid).",".S::sqlEscape($affect).",".S::sqlEscape($type).",".S::sqlEscape($score).",".time().")");

    }

}

class gameException extends Exception{

	private $_gameMessgae;

	public function __construct( $message = null, $code = 0) {

		$this->_logFile = H_P."data/log/exception/game_exception.log";
		$this->_gameMessgae = $message;

		parent::__construct($message, $code);
	}


	public function getGameMessage(){

		$time = date("Y-m-d H:i:s", time());
		list($msec, $sec) = explode(' ', microtime());
		//  		$data = $time.".".ceil($msec*1000).":\t$this->file throws exception in line $this->line:\r\n".
		//  				"message:\t$this->_gameMessgae\r\n".
		//  				"code:\t$this->code";
		return $time.".".ceil($msec*1000)."\r\n".$this->__toString()."\r\n\r\n";
	}

	public function log(){
		$this->writeover($this->_logFile,$this->getGameMessage(), "a+");
		// 		$this->writeover($this->_logFile,parent::getMessage(), "a+");
	}

	function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
		$fileName = trim($fileName);
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

class server {
	const URL = "http://202.75.219.184:777/InterfaceForSitepage.aspx";
	private $url,
			$seq;

	function register($uid, $pid) {
		global $db_charset,$db;

		$user = $db->get_one("SELECT `uid`,`username`,`icon` FROM pw_members WHERE uid = $uid");
		if(empty($user)) return false;
		$prefix = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'], 'hack/'))."/images/face/";
		$icon = substr($user['icon'], 0,strpos($user['icon'], "|"));
		empty($icon) && $icon = '/none.gif';
		$avater = $prefix.$icon;
		$seq = $this->getSeq();
		ini_set('allow_url_include',1);
		$db_charset == 'gbk' && $user[username]= mb_convert_encoding($user[username], "UTF-8", "GB2312");
		$url = self::URL."?action=NotifyOnline&partner=$pid&seq=$seq&id=$uid&avater=$avater&nick=$user[username]";

		$fp = fopen($url,"r");
		$inheader = 1;
		while (!feof($fp)) {
			$line = fgets($fp,1024);
			if ($inheader && ($line == "\n" || $line == "\r\n")) {
				$inheader = 0;
			}
		}
		fclose($fp);
		if($line) {
			$result = json_decode($line,true);
			if( $result['ret'] == 0 ) {
				$this->setSeq(++$seq);
			}
			else if( $result['ret'] == -2 ) {
				$this->setSeq(++$seq);
				return false;
			}
			else {
				return false;
			}
		}
		else
			return false;
	}

	function setSeq($seq) {
		$ori = readover(H_P."data/requestSeq.txt");
		if($seq>$ori)
			writeover(H_P."data/requestSeq.txt", $seq);
	}

	function getSeq() {
		$seq = readover(H_P."data/requestSeq.txt");
		return $seq;
	}

}
?>
