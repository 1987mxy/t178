<?php
//error_reporting(E_ALL^E_NOTICE^E_WARNING);
error_reporting(E_ALL & ~(E_NOTICE));
//ini_set("display_errors", 1);
define('APPTYPEID', 127);
define('CURSCRIPT', 'plugin');
define('R_P', dirname(__FILE__));
define('H_P', R_P);

$ng_path = substr(H_P, 0, (strlen(H_P)-22));
include_once($ng_path."/source/class/class_core.php");

$discuz = & discuz_core::instance();
$discuz->init();

include_once(H_P.'/require/functions.php');
include_once(H_P.'/require/security.php');
@include_once(H_P.'/data/api_config.php');
include_once H_P.'/data/basic_config.php';
define('DEBUG', $pkgame_debug);    //调试模式

$action = $_GET['action'];
$id = $_GET['id'];

ini_set("session.gc_maxlifetime","7200");
ini_set("session.cookie_lifetime","7200");

session_start();
date_default_timezone_set("Asia/Shanghai");
$ng_key = '9s3f5se';

$GLOBALS['URL'] = S_HOST.'/games/svs/submitgamescore.action?';
$GLOBALS['pkgame_challenge_algorithm'] = $pkgame_challenge_algorithm;
$MSGKey = "52011" ;

$table_game = 'ng_game';
$table_ip = 'ng_game_ip';
$table_credit = 'ng_game_credit';
$ng_key = '9s3f5se';
$action = $_GET['action'];
$timestamp = time();

if($action == 'get'){ // 获得结果
	@include_once H_P.'/data/jljf_config.php';
	$id = $_GET['id'];

	empty($id) && msg("gid_null", array("ret"=>0,"key"=>0),DEBUG);

	$strSQL = "select gid from `ng_game` where gid=$id limit 1";
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
	$res = $result;

	empty($res['gid']) && msg("game_not_exist, with gid = $id", array("ret"=>0,"key"=>0),DEBUG);
	$onlineIp = pkgameGetIp();
	
	if($ipView = $GLOBALS['_COOKIE']['bbsgame_ipview']) {
		
		list($lastTime,$ip,$ids) = explode("\t",$ipView);
		if(is_array($ids))
			$ids = explode(',', $ids);
		else
			$gameids[] = $ids;

		if(!in_array($id, $gameids)) {
			$gameids[] = $id;
			$ids = implode(",", $gameids);
			$cookieValue = $timestamp."\t".$onlineIp."\t".$ids;
			Cookie('bbsgame_ipview',$cookieValue);
		}
		
	} 
	else {
		$cookieValue = $timestamp."\t".$onlineIp."\t".$id;
		Cookie('bbsgame_ipview',$cookieValue,$timestamp+86400);  //ip按天统计
	}
	$sql = "($id,'$onlineIp',$timestamp)";
	$file = H_P.'/data/queryIp.txt';
	
	/*if( filesize($file)<1024 ) {
		logQueryCache($file, $sql.",","ab+");
	}
	else {

		$sql = "INSERT INTO ng_game_ip(gid,ip,addTime) values".readover($file).$sql;
		if(DB::query($sql))
		logQueryCache($file,'',"rb+");
	}*/
	$sql = "INSERT INTO ng_game_ip(gid,ip,addTime) values".$sql;
	DB::query($sql);

	if ($uid = $_G['uid']) {
		@include_once H_P.'/data/bbsgame_config.php';
		empty($_G[uid]) && msg("need login",array("ret"=>0,"uid"=>''),DEBUG);
		!isUserExist($uid) && register($uid,$pid);
		
		$gameInfo = getGameInfo($id);
		// 获得预处理的积分
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if((string)trim($gameInfo['ctype']) === (string)trim($ext)) {
				$cash = $_G['member'][$ext];
				break;
			}
		}
		$useup = $gameInfo['useup']; // 获得开始游戏扣除的积分
		$_SESSION['ng_sig'] = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,5);
			
		$mode = GetCookie('bbsgame_mode');
		if ($useup != 0 && empty($mode)) {
			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				getuserprofile($ext);
				if((string)trim($gameInfo['useup_ctype']) === (string)trim($ext)) {
					$extcredtitle = $value['title'];
					break;
				}
			}
			if ($useup > $cash){
				$_SESSION['noScoreSet'] = 1; //无法获取积分
				msg('您当前'.$extcredtitle.'不足，本次游戏不能赚取积分',array("ret"=>0,"key"=>"$_SESSION[ng_sig]","gid"=>$id,"uid"=>$uid), DEBUG);
			}
			else {
				unset($_SESSION['noScoreSet']);
			}

			$dataarr = array($ext => -1*$useup);
			updatemembercount($_G['uid'], $dataarr, 1, 'TRC', $_G['uid']);

			$affect_describe = "$_G[username]在游戏 <".$gameInfo['name']."> 中消耗".$useup." ".$extcredtitle;
			/*$sql = "INSERT INTO pw_creditlog (uid,username,ctype,affect,adddate,logtype,ip,descrip)" .
								" VALUES ($winduid,".S::sqlEscape($winddb[username]).",".S::sqlEscape($game->getCtype()).", $useup,$timestamp,'game_affect','$winddb[onlineip]',".S::sqlEscape($affect_describe).")";
			$db->query($sql);*/

			creditLog($id, $uid, $useup, $gameInfo['useup_ctype'] ,$score);
		}//奖励积分游戏
		else {
			unset($_SESSION['noScoreSet']);
		}
	}
	else{
		msg("not_login",array("ret"=>0,"uid"=>$uid,"gid"=>$id,"pid"=>$pid), DEBUG);
	}
	
	msg("got_key", array("ret"=>1,"key"=>"$_SESSION[ng_sig]","gid"=>$id,"uid"=>intval($_G[uid]),"pid"=>$pid),DEBUG);
	exit;
}
?>