<?php
//error_reporting(E_ALL & ~(E_NOTICE));
//ini_set("display_errors", 1);

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
define('SCR', 'source/plugin');

if(isset($_GET['action']) && $_GET['action'] == 'ajax'){
	define('AJAX','1');
}

$H_name = 'bbsgame';
define('R_P', dirname(__FILE__));
define('H_P', R_P);
if(!is_dir(H_P)){
	echo "Error!";
}

$hkimg		= H_P."/image";
/********************** pw_index.php 源码段修改开始 ************************/
include_once(H_P.'/data/basic_config.php');		// 新增函数库
include_once(H_P.'/data/bbsgame_config.php');
include_once(H_P.'/data/explorer_config.php');
//include_once(H_P.'/data/game_config.php');
include_once(H_P.'/data/jljf_config.php');
include_once(H_P.'/require/functions.php');
include_once(H_P.'/require/security.php');
include_once(H_P.'/data/api_config.php');
date_default_timezone_set("Asia/Shanghai");

if(!$ifopen) {
	//卸载或关闭确认
	showmessage('社区游戏尚未开放，请关注论坛公告！');
}

define('BASE_PATH', H_P);
define('API_HOST','http://www.97sng.com');
define('SWF_HOST','http://img.97sng.com/flash');
define('PIC_HOST','http://img.97sng.com/pic');
define('GAME_INFO',API_HOST.'/apps/clubapi.ashx');

/*page_css*/
define('C_PS', 12); //擂台每页显示条数包括分类下ajax的返回数。challenge
define('C_SCOPE',3);
define('P_PS', 10);//pk
define('P_SCOPE',3);

$GLOBALS['db_charset'] = "utf-8";
if($db_charset == 'gbk') {
	$ng_charset = 'GB2312';
}else if($db_charset == 'utf-8') {
	$ng_charset = 'UTF-8';
}
$time = time();
@include_once(H_P."/require/challenge.class.php");
$_G['uid'] && $newPknoticeNum = getNewPkNum($_G['uid'], challenge::PK_PK);

/********************* nggame start ***********/
$table_game = 'ng_game';
$table_ip = 'ng_game_ip';
$table_credit = 'ng_game_credit';
$table_pk   = 'ng_game_pk';
$table_pker = 'ng_game_pker';
$table_shell = 'ng_game_shell';

$ng_key = '9s3f5se';
$GLOBALS['URL'] = 'http://202.75.219.184/InterfaceForSitepage.aspx?';

ini_set('session.gc_maxlifetime',10800);
session_start();

$action = $_GET['action'];
$timestamp = time();

if($action=='set') {
	empty($_G[uid]) && msg("need login",array("ret"=>0,"uid"=>''),DEBUG);
	$uid = intval($_G[uid]);
	
	$key = $_POST['key'];
	$score = $_POST['score'];
	$gid = $_POST['gid'];
	$mode = $_POST['mode'];
	$pk_id = $_POST['pk_id'];

	!$key && msg("key_null",array("affect"=>0,"ret"=>0,"gid"=>$gid,"score"=>$score,"uid"=>$uid),DEBUG);
	!$gid && msg("gid_null",array("affect"=>0,"ret"=>0,"gid"=>$gid,"score"=>$score,"uid"=>$uid),DEBUG);
	!$_SESSION['ng_sig'] && msg("第一次提交必要参数缺失或是你已经提交过",array("affect"=>0,"ret"=>0,"gid"=>$gid,"score"=>$score,"uid"=>$uid),DEBUG);
	
	$entry = md5($_SESSION['ng_sig'].$ng_key.$score);
	$entry !== $key && msg("key_illegal_or_expired",array("affect"=>0,"ret"=>0,"gid"=>$gid,"score"=>$score,"uid"=>$uid),DEBUG);
	
	// 新版积分处理逻辑功能 
	try
	{
		$gameInfo = getGameInfo($gid);
		!validateConfig($gameInfo['ctype'], $_G['setting']['extcredits']) && msg("game_conf_error",array("affect"=>0,"ret"=>0),DEBUG);

		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if((string)trim($gameInfo['ctype']) === (string)trim($ext)) {
				$gameInfo['money'] = $value['title'];
				$cash = $_G['member'][$ext];
				break;
			}
		}
		$affect = getAffect($score, $gameInfo);
		
		if (!$mode) {
			$_SESSION['noScoreSet'] == 1 && msg("抱歉，您的".$gameInfo['money']."不足,无法获取积分。",array("ret"=>0), DEBUG);


			if (!empty($affect)) { 
				// DZ 扣除积分功能
				$dataarr = array($ext => $affect);
				updatemembercount($_G['uid'], $dataarr, 1, 'TRC', $gid);
			
				$affect_describe = "{$_G['username']}在游戏 <".$gameInfo['name']."> 中获得".$affect.$gameInfo['money'];
				//$sql = "INSERT INTO pw_creditlog (uid,username,ctype,affect,adddate,logtype,ip,descrip)" .
				//		" VALUES ($winduid,".S::sqlEscape($winddb[username]).",".S::sqlEscape($game->getCtype()).", $score,$timestamp,'game_affect','$winddb[onlineip]',".S::sqlEscape($affect_describe).")";
				//$db->query($sql);
			}
		}
		else {
			$challenge = new challenge();

			$strSQL = "SELECT pk.score,pk.uid,pk.endtime,pk.type,pk.id,pk.gid FROM {$table_pk} pk WHERE id = {$pk_id}";
			$query = DB::query($strSQL);
			$pkInfo = DB::fetch($query);

			if( $pkInfo['gid'] ) {
				//msg($pkInfo['endtime'],array("ret"=>0));
				if( $pkInfo['endtime'] < time() ) {
					msg("pk_expeired",array("ret"=>0)); // 可能是数据库读取错误
				}
				//挑战所有者
				if( $pkInfo['uid'] == $uid ) {
					$userType = 'DEF';

					$strSQL = "SELECT act_score FROM {$table_pker} WHERE def_uid = {$uid} AND pkid = {$pk_id} LIMIT 1";
					$query = DB::query($strSQL);
					$pkScore = DB::fetch($query);

					if ( $score > $pkInfo['score']) {
						$strSQL = "UPDATE {$table_pk} SET score='{$score}' WHERE id = {$pkInfo['id']}";
						$query = DB::query($strSQL);
					}
				}//加入挑战者
				else{
					$userType = 'ACT';
					$pkScore = $pkInfo['score'];
				}

				
				if (pkRecord( $pk_id, $score, $userType, $uid)) {
					$CHALLENG_RESULT = $score - $pkScore;
				}
			}
			unset($challenge);
		}
		
		!empty($score) && creditLog($gid, $_G['uid'], $affect, $gameInfo['ctype'],$score);
	}
	catch (gameException $ge) {
		$ge->log();
	}
	catch (challengeException $ce) {
		$ce->log();
	}
	unset($_SESSION['ng_sig']);
	
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = 'extcredits'.$key;
		getuserprofile($ext);
		if((string)trim($gameInfo['ctype']) === (string)trim($ext)) {
			$ctype = $value['title'];
			break;
		}
	}
	$username = $_G['username'];
	if (!$mode) {
		if ($affect >= 0) {
			$msgAffect = "并获得$affect $ctype";
		}
		else{
			$msgAffect = "并消耗$affect $ctype";
		}
	}

	$GLOBALS['AFFECT_RESULT'] = array("affect"=>$msgAffect,"username"=>$username, 'score'=>$score);
	if (isset($CHALLENG_RESULT)) 
	{
		if ($CHALLENG_RESULT > 0) 
		{
			msg('submit_mode_success',array("ret"=>1), DEBUG, FALSE);
		}
		elseif ($CHALLENG_RESULT == 0) {
			msg('submit_mode_draw',array("ret"=>1), DEBUG, FALSE);
		}
		else {
			msg('submit_mode_failed',array("ret"=>1), DEBUG, FALSE);
		}
	}
	else{
		msg('submit_success',array("ret"=>1), 1, FALSE);
	} 


	ob_flush();
	flush(); //输出缓冲
	
	
	$strSQL = "SELECT * FROM {$table_pk} WHERE gid = $gid AND type= ".challenge::PK_NATIONAL." AND endtime > ".time();
	$arr = DB::result_first($strSQL);
	if ($arr['id']) {
		$hdflag = 9;
	}
	else{
		$hdflag = 0;
	}
	unset($arr);

// 	if(!empty($score) && ( $score > getUserMaxScore($uid, $gid) || $hdflag==9)) {
		$gameName = $gameInfo['name'];
		$post = array(
			"pid"=>$pid,
			'uid'=>$uid,
			'nick'=>$username,
			'score'=>$score,
			'gameid' => $gid,
			'game'=>$gameName,
			'hdflag' => $hdflag,
			'encodestr' => md5("$score$pid$uid"),
		);
		include_once H_P.'/require/msgqueue.class.php';
		$msgFactory = new msgqueueFactory();

		$msgqueue = $msgFactory->init(msgqueueFactory::OS_WIN);

		$msgqueue->send($post);

		$seqs = $msgqueue->getCacheArray();
		
		if(is_array($seqs)) {
			foreach($seqs as $key=>$post) {
				$msgqueue->send($post, $key);
				if(rand(0, 10) > 5)break;
			}
		}
// 	}
	exit;
}
else if($action == 'login') {

	define('CURMODULE', "logging");
	
	require libfile('function/member');
	$ctl_obj = new logging_ctl();
	$ctl_obj->setting = $_G['setting'];
	$ctl_obj->on_login();
	if($_G['uid']) {
		msg("login_success", array("ret"=>1,"uid"=>$_G['uid']));
	} else {
		msg("登入失败，用户名或密码错误", array("ret"=>0,"uid"=>0));
	}
	exit;
}
/********************* nggame end ***********/

if($_GET['gid']) {
	$gid = intval($_GET['gid']);		//	游戏序号
} else {
	$strSQL = "SELECT gid FROM {$table_pk} WHERE type = ".challenge::PK_NATIONAL." ORDER BY starttime DESC";
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
	$gid = $result['gid'];
}

if(isset($_GET['gid']) && $_GET['action'] == 'game') {	
	S::gp("mode");
	S::gp(array('pk_id','gid'),'G',2);
		
	$uid = $_G['uid'];
	empty($uid) && $uid = '0';
		
	@include_once(H_P.'/require/game.class.php');
	@include_once(H_P.'/require/challenge.class.php');
		
	if ( $_G[uid] === 1 ) $admintoken = 1;
		
	$gameInfo = getGameInfo($gid);
	if(!$gameInfo['gid']) {
		showmessage('游戏未安装或已删除。');
	}
	$gameInfo['shellUrl'] = preg_replace('/.swf/', '_v2.swf',$gameInfo['shellUrl']);
	if ($pkgame_isdownload == 'on' && file_exists(H_P."data/".$gameInfo['shellUrl']) && file_exists(H_P."/data/".$gameInfo['swf'])) {
		$host = 'source/plugin/bbsgame/data/';
	}
	else {
		$host = SWF_HOST;
	}
		
	$gameInfo['shellUrl'] = $host.'/'.$gameInfo['shellUrl']."?t=".time();
	$gameInfo['swf'] = $host.'/'.$gameInfo['swf'];
	$gameInfo['img'] = PIC_HOST.'/'.$gameInfo['img'];
	//$gameInfo[ctype] = $credit->cType[$gameInfo[ctype]];
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = 'extcredits'.$key;
		//getuserprofile($ext);
		//echo $value['title']."=>".$_G['member'][$ext];
		if((string)trim($ext) === (string)trim($gameInfo['useup_ctype'])) {
			$gameInfo['money'] = $value['title'];
			break;
		}
	}
	
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = 'extcredits'.$key;

		if((string)trim($ext) === (string)trim($gameInfo['ctype'])) {
			$gameInfo['useup_ctype'] = $value['title'];
			break;
		}
	}

	$strSQL = "SELECT g.*,s.url as shell  
			FROM {$table_game} g, {$table_shell} s
			WHERE g.shellId = s.id AND g.type = {$gameInfo['type']} AND g.gid != {$gid} LIMIT 7";
	$query = DB::query($strSQL);
	while( $result = DB::fetch($query) ) {
		$arr[] = $result;
	}
	$recommendGames = $arr;
	unset($arr);
	foreach($recommendGames as $k=>$v) {
		$recommendGames[$k]['img'] = PIC_HOST.'/'.preg_replace('/_120.jpg/', '.jpg', $v['img']);
	}
	$pkRankingUser = getPkRankingUser($gid, "LIMIT 10");
	
	$strSQL = "SELECT * FROM {$table_pk} WHERE gid = {$gid} AND type= ".challenge::PK_NATIONAL." AND endtime > ".time();
	$query = DB::query($strSQL);
	while( $result = DB::fetch($query) ) {
		$arr[] = $result;
	}
	 
	if ($arr[0]['gid'])
		$nationalActive = 9;//活动标记
	else
		$nationalActive = '0';
	unset($arr);
		
	$page = isset($_GET['p']) && !empty($_GET['p']) ?intval($_GET['p']):1;
		
	$pageSize = 10;
	$scope = 3;
	$url = $_SERVER["SCRIPT_NAME"]."?gid=$gid&p=";
	$count = getReplyCount($gid);
	$replylist = getReplyList($gid, "LIMIT ".($page-1)*$pageSize.", $pageSize");
		
	@include_once(H_P."/require/pageCss.class.php");
	$pageCss = new PageCss($pageSize, $scope, $url, "#list");
	$pageCss->setCounts($count);
	$pageCss->setCurrentpage($page);
	$pageCss = $pageCss->getHTML(false);
		
	if (!empty($_G['uid'])) 
	{
		//mode:pk normal challenge
		$modetypes = array("pk"=>"挑战","challenge"=>"擂台赛");
		if (!empty($mode))
			Cookie('bbsgame_mode',$mode);
		else
			Cookie('bbsgame_mode','');
		if ($mode == 'pk') 
		{
			try {
				if (!empty($pk_id) ) 
				{
					$info = getPkInfo($pk_id);
					$strSQL = "SELECT * FROM {$table_pker} WHERE pkid={$pk_id} LIMIT 1";
					$query = DB::query($strSQL);
					$result = DB::fetch($query);
					$pkerWithUid = $result;
					
					//def_user
					if ($info['uid'] == $_G['uid'] )
					{
						if ($info['status'] == challenge::PK_STATUS_MAKESURE) 
						{

							$strSQL = "UPDATE {$table_pk} SET status=".challenge::PK_STATUS_ING." WHERE id={$pk_id} LIMIT 1";
							$query = DB::query($strSQL);
							$dataarr = array($info['case_ctype'] => '-'.$info['case']);
							updatemembercount($info['uid'], $dataarr, 1, 'TRC', $gid);
						}

						$strSQL = "SELECT act_username AS username,act_score AS score,act_uid AS uid FROM {$table_pker} WHERE pkid = {$pk_id}";
						$query = DB::query($strSQL);
						$result = DB::fetch($query);
						$recommendGames = $result;
						
						$pkerWithUid['score'] = $pkerWithUid['def_score'];
					}//act_user
					else 
					{
						if($_G['u_id'] == $pkerWithUid['act_uid']) {
							$userName = $pkerWithUid['def_username']; 
							$uid = $pkerWithUid['def_uid'];
							$score = $pkerWithUid['def_score']; 
							$myscore = $pkerWithUid['act_score']; 
						} else {
							$userName = $pkerWithUid['act_username'];
							$uid = $pkerWithUid['act_uid'];
							$score = $pkerWithUid['act_score'];
							$myscore = $pkerWithUid['def_score'];
						}

						$fightUser = array("username"=>$userName,"uid"=>$uid,"score"=>$score);
						$pkerWithUid['score'] = $pkerWithUid['act_score'];
					}
				}
				unset($challenge);
			}catch (challengeException $e) {
				$e->log();
			}
			$mode_type = $modetypes[$mode];
			$playedStatistics = 1;
		}
		elseif ($mode == 'challenge') 
		{

			try{
				$challenge = new challenge();
				if (!empty($pk_id) ) 
				{
					$strSQL = "SELECT pk.* FROM {$table_pk} pk LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member m ON pk.uid = m.uid WHERE pk.id = {$pk_id} LIMIT 1";
					$query = DB::query($strSQL);
					$result = DB::fetch($query);
					$info = $result;

					$strSQL = "SELECT act_score as score FROM {$table_pker} WHERE pkid={$pk_id} AND act_uid = {$_G['uid']} LIMIT 1";
					$query = DB::query($strSQL);
					$result = DB::fetch($query);
					$pkerWithUid = $result;
					//用户尚未加入有效擂台
					if (empty($pkerWithUid) && $info['uid'] != $_G['uid'] && $info['status'] != challenge::PK_STATUS_OK && $info['endtime'] > time()) 
					{
						$act_user = array("uid"=>$_G['uid'], "username"=>S::sqlEscape($_G['username']),"usericon"=>S::sqlEscape($_G['icon']));
						$def_user = array("uid"=>$info['uid'], "username"=>S::sqlEscape($info['username']),"usericon"=>S::sqlEscape($info['icon']));
						if ($challenge->join($info['id'], $act_user, $def_user, $info['gid'])) {
							$dataarr = array($info['case_ctype'] => '-'.$info['case']);
							updatemembercount($act_user['uid'], $dataarr, 1, 'TRC', $gid);
						}
					}
					empty($pkerWithUid['score']) && $pkerWithUid['score'] = 0;
					$fightUser = array("username"=>$info['username'],"uid"=>$info['uid'],"score"=>$info['score']);
				}
				unset($challenge);
			}
			catch (challengeException $e) {
				$e->log();
			}
			$mode_type = $modetypes[$mode];
		}
		$strSQL = "SELECT IFNULL(MAX(score),0) AS max, IFNULL(MIN(score),0) AS min FROM ng_game_credit WHERE uid={$_G['uid']} AND gid={$gid}";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		$pkerWithUid = $result;
	}
	$game_select = "ctrl_selected";
	$theurl = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$anchor = $gameInfo['name'];
	if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
	include template('bbsgame:content');
	exit;
} elseif($_GET['action'] == 'pk') {
	S::gp(array("AJAX","r_p_t",'page','act'));
	//require_once (R_P . 'require/showimg.php');
	@include_once(H_P."/require/pageCss.class.php");
	@include_once(H_P."/require/challenge.class.php");
			
	$request_pk_type = $r_p_t;
	empty($page) && $page = 1;
			
	$pageSize = P_PS;
	$scope = P_SCOPE;
	$start = ($page-1)*$pageSize;
	$url = $_SERVER[SCRIPT_NAME]."?id=bbsgame&action=pk&page=";
		
	try {
		if (empty($_G[uid]))
			showmessage("抱歉，您还没登录，无法进入挑战",'member.php?mod=logging&action=login');
		$challenge = new challenge();
		
		if ($AJAX) 
		{
			ob_clean();
			
			if ($request_pk_type == 'act') 
			{
				$strSQL = "SELECT pk.pkname as pk_name,pk.endtime, g.img, g.name, pker.*, pk.status AS status, pker.def_score, pker.act_score  
						FROM {$table_pker} pker LEFT JOIN {$table_pk} pk ON pk.id = pker.pkid
						LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pker.act_uid = $_G[uid] AND pk.type = ".challenge::PK_PK." ORDER BY pk.ctime DESC LIMIT {$start},{$pageSize}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					if($result['def_score'] > $result['act_score']) $result['lose'] = 1; else $result['lose'] = 0;
					if($result['def_score'] == $result['act_score']) $result['draw'] = 1; else $result['draw'] = 0;
					$result['act_usericon'] = avatar($result['act_uid'], "small", 1);
					$result['def_usericon'] = avatar($result['def_uid'], "small", 1);
					$arr[] = $result;
				}
				$pklistsPkHistoryAsAct = $arr;
				unset($arr);
				pkListAjaxOutput($pklistsPkHistoryAsAct);
			}
			elseif ($request_pk_type == 'def') 
			{
				$strSQL = "SELECT pk.pkname as pk_name,pk.endtime, g.img, g.name, pker.*, pk.status AS status, pker.def_score, pker.act_score 
						FROM {$table_pker} pker LEFT JOIN {$table_pk} pk ON pk.id = pker.pkid
						LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pker.def_uid = $_G[uid] AND pk.status <> ".challenge::PK_STATUS_MAKESURE." AND pk.type = ".challenge::PK_PK." LIMIT {$start},{$pageSize}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					if($result['def_score'] > $result['act_score']) $result['lose'] = 1; else $result['lose'] = 0;
					if($result['def_score'] == $result['act_score']) $result['draw'] = 1; else $result['draw'] = 0;
					$result['act_usericon'] = avatar($result['act_uid'], "small", 1);
					$result['def_usericon'] = avatar($result['def_uid'], "small", 1);
					$arr[] = $result;
				}
				$pklistsPkHistoryAsDef = $arr;
				unset($arr);
				pkListAjaxOutput($pklistsPkHistoryAsDef);
			}
			elseif ($request_pk_type == 'pkme')
			{
				$strSQL = "SELECT pk.pkname as pk_name,pk.endtime,pk.case,pk.endtime,pk.case_ctype,pk.case as casenum, g.img, g.name, pk.status, pker.*
						FROM {$table_pker} pker LEFT JOIN {$table_pk} pk ON pk.id = pker.pkid
						LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pker.def_uid = $_G[uid] AND pk.type = ".challenge::PK_PK." AND pk.status =".challenge::PK_STATUS_MAKESURE." ORDER BY pk.ctime DESC LIMIT {$start},{$pageSize}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					$result['act_usericon'] = avatar($result['act_uid'], "small", 1);
					$result['def_usericon'] = avatar($result['def_uid'], "small", 1);
					$arr[] = $result;
				}
				$pklistsPkMe = $arr;
				unset($arr);
				$message_count = count($pklistsPkMe);
			
				pkListAjaxOutput($pklistsPkMe);
			}
		}

		$strSQL = "SELECT COUNT(*) as count 
				FROM {$table_pk} pk LEFT JOIN {$table_pker} pker ON pk.id = pker.pkid
				LEFT JOIN {$table_game} g ON pk.gid = g.gid
				WHERE (pker.act_uid = $_G[uid] OR pker.def_uid = $_G[uid]) AND pk.type = ".challenge::PK_PK;
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		$count = $result['count'];
		//var_dump($count);
		$strSQL = "SELECT pk.pkname as pk_name,pk.id as pkid,pk.case,pk.endtime,pk.case_ctype, g.img, g.name, pker.*,pk.status status, pker.def_score, pker.act_score 
				FROM {$table_pker} pker LEFT JOIN {$table_pk} pk ON pk.id = pker.pkid
				LEFT JOIN {$table_game} g ON pk.gid = g.gid
				WHERE (pker.act_uid = $_G[uid] OR pker.def_uid = $_G[uid]) AND pk.type = ".challenge::PK_PK." ORDER BY pk.ctime DESC LIMIT {$start},{$pageSize}";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			if($result['def_score'] > $result['act_score']) $result['lose'] = 1; else $result['lose'] = 0;
			if($result['def_score'] == $result['act_score']) $result['draw'] = 1; else $result['draw'] = 0;
			$result['act_usericon'] = avatar($result['act_uid'], "small", 1);
			$result['def_usericon'] = avatar($result['def_uid'], "small", 1);
			
			$arr[] = $result;
		}
		$pklistsPkall = $arr;
		
		$pklistsPkall = pkListOutput($pklistsPkall);
		
		unset($arr);
		$pageCss = new PageCss($pageSize, $scope, $url);
		$pageCss->setCounts($count);
		$pageCss->setCurrentpage($page);
		$pageCss = $pageCss->getHTML(false);			
		
		$strSQL = "SELECT gid,name FROM {$table_game} WHERE `installed` = 1";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		$gameList = $arr;

		unset($arr);

		$strSQL = "SELECT fuid, fusername FROM {$_G['config']['db'][1]['tablepre']}home_friend WHERE uid='{$_G['uid']}'";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		$friendsList = $arr;
		unset($arr);
		unset($challenge);
		
	}
	catch (challengeException $e){
		$e->log();
	}
		
	$zhanshu_select = "ctrl_selected";
	$anchor = "战书";
	if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
	include template('bbsgame:content');
	exit;
} elseif($_GET['action'] == 'challenge') {
	S::gp(array("AJAX","r_c_t",'page'),'G');
	@include_once(H_P."/require/challenge.class.php");
	$challenge = new challenge();
			
	empty($page) && $page = 1;
	$request_challenge_type = $r_c_t;
	$scope = C_SCOPE;
	$pageSize = C_PS;
	$start = ($page-1)*$pageSize;
	$url = $_SERVER[SCRIPT_NAME]."?id=bbsgame&action=challenge&page=";
			
	/* 擂台 */
	try{
		$strSQL = "SELECT pk.* FROM {$table_pk} pk WHERE pk.type<>".challenge::PK_PK;
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		$count = count($arr);
		unset($arr);
	
		if ($AJAX)
		{
			ob_clean();
			if ($request_challenge_type == 'personal')
			{
				$strSQL = "SELECT pk.id,pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate, g.img,g.gid,g.name
						FROM {$table_pk} pk LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pk.type = ".challenge::PK_PERSONAL."
						ORDER BY pk.ctime DESC LIMIT {$start}, {$pageSize}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					if($db_charset == 'gbk') {
						$result['name'] = mb_convert_encoding($result['name'], "UTF-8", "GB2312");
						$result['username'] = mb_convert_encoding($result['username'], "UTF-8", "GB2312");
					}
					$arr[] = $result;
				}
				$personalPkLists = $arr;
				unset($arr);
				challengeAjaxOutput($personalPkLists);
			
			}
			elseif ($request_challenge_type == 'sitenal') {
			
				$strSQL = "SELECT pk.id,pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate, g.img,g.gid,g.name
						FROM {$table_pk} pk LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pk.type = ".challenge::PK_SITENAL."
						ORDER BY pk.ctime DESC LIMIT {$start}, {$pageSize}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					if($db_charset == 'gbk') {
						$result['name'] = mb_convert_encoding($result['name'], "UTF-8", "GB2312");
						$result['username'] = mb_convert_encoding($result['username'], "UTF-8", "GB2312");
					}
					$arr[] = $result;
				}
				$sitenalPkLists = $arr;
				unset($arr);
				challengeAjaxOutput($sitenalPkLists);
			}
			elseif ($request_challenge_type == 'national') {

				$strSQL = "SELECT pk.id,pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate,g.img,g.name,g.gid
						FROM {$table_pk} pk LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pk.type = ".challenge::PK_NATIONAL."
						ORDER BY pk.ctime DESC LIMIT {$start}, {$pageSize}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					if($db_charset == 'gbk') {
						$result['name'] = mb_convert_encoding($result['name'], "UTF-8", "GB2312");
						$result['username'] = mb_convert_encoding($result['username'], "UTF-8", "GB2312");
					}
					$arr[] = $result;
				}
				$nationalPkLists = $arr;
				unset($arr);
				challengeAjaxOutput($nationalPkLists);
			}
			elseif ($request_challenge_type == 'mine') {
				if (!empty($_G[uid])) {
					$strSQL = "SELECT pk.id, pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate,g.img,g.name,g.gid
						FROM {$table_pk} pk LEFT JOIN {$table_game} g ON pk.gid = g.gid
						WHERE pk.uid = $_G[uid]  AND pk.type =".challenge::PK_PERSONAL."
						ORDER BY pk.ctime DESC LIMIT {$start}, {$pageSize}";
					$query = DB::query($strSQL);
					while( $result = DB::fetch($query) ) {
						if($db_charset == 'gbk') {
							$result['name'] = mb_convert_encoding($result['name'], "UTF-8", "GB2312");
							$result['username'] = mb_convert_encoding($result['username'], "UTF-8", "GB2312");
						}
						$arr[] = $result;
					}
					$minePkLists = $arr;
					unset($arr);
				}
				else{
					$minePkLists = null;
				}
				challengeAjaxOutput($minePkLists);
			}
		}
			
		@include_once(H_P."/require/pageCss.class.php");
		$pageCss = new PageCss($pageSize, $scope, $url);

		$gameInfo = getGameInfo(intval($_GET['gid']));
		$pkGame = getPKAll();
		!empty($gameInfo['img']) && $gameInfo['img'] = PIC_HOST.'/'.$gameInfo['img'];

		$strSQL = "SELECT pk.*,g.name, g.gid, g.img
				FROM {$table_pk} pk LEFT JOIN {$table_game} g ON pk.gid = g.gid
				WHERE pk.type <> ".challenge::PK_PK."
				ORDER BY pk.ctime DESC LIMIT {$start}, {$pageSize}";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			if($db_charset == 'gbk') {
				$result['gamename'] = $result['name'];
			}
			$arr[] = $result;
		}
		$allPkLists = $arr;
		unset($arr);

		$pageCss->setCounts($count);
		$pageCss->setCurrentpage($page);
		$pageCss = $pageCss->getHTML(false);
	
		if( !empty($allPkLists) ) {
			foreach( $allPkLists as $key=>$pk ) {
				if (empty($pk['gid'])) {
					unset($allPkLists[$key]);
					continue;
				}
				$allPkLists[$key]['endtime'] = $pk['endtime'] < time() ? "已结束" : date("Y-m-d", $pk['endtime']);
				$allPkLists[$key]['ctime'] = date("Y-m-d ", $pk['ctime']);
				$allPkLists[$key]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $pk['img']);
			}
		}

		//partake
		if ( !empty($_G[uid])){
			$status = array('1'=>"火爆进行中...","2"=>"未知","3"=>"已结束");
			$strSQL = "SELECT pker.*,pk.*
				FROM {$table_pk} pk LEFT JOIN {$table_pker} pker ON pker.pkid = pk.id
				WHERE pker.act_uid = $_G[uid] OR pk.uid = $_G[uid] AND pk.type <>".challenge::PK_PK."
				GROUP BY pk.id
				ORDER BY pk.ctime DESC";
			$query = DB::query($strSQL);
			while( $result = DB::fetch($query) ) {
				$arr[] = $result;
			}
			$partakePkLists = $arr;
			unset($arr);
			
			if( !empty($partakePkLists) ) {
				foreach( $partakePkLists as $key=>$pk ) {
					$partakePkLists[$key]['endtime'] = date("Y-m-d ", $pk['endtime']);
					$partakePkLists[$key]['status'] = $status[$pk[status]];
					$partakePkLists[$key]['totalreward'] = $pk['number']*$pk['case'];
				}
			}
		}
	
		unset($challenge);
	}
	catch (challengeException $e){
		$e->log();
	}
	/* configure of challenge ,by admin */
	//$challenge_ctype = $credit->cType[$pkgame_challenge_case_ctype];
	$challenge_pkerCreditPercent = $pkgame_challenge_algorithm_personal_pkerPercent;
	$challenge_pkerCreditNum = count(explode(":", $challenge_pkerCreditPercent));
	$leitai_select = "ctrl_selected";
	$take_select = isset($_GET['act']) && trim($_GET['act']) == 'take' ? "leitai_selected":'';
	empty($take_select) && $all_select = 'leitai_selected';
	$anchor = "擂台赛";
	if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
	include template('bbsgame:content');
	exit;
} elseif($_GET['action'] == 'postmsg') {
	$url = S_HOST.'/games/coop/contactme.html';
	$outDivConfig = array("width"=>"960px","height"=>"940px");
	$iFrameConfig = array("width"=>'960px',"height"=>'930px',"top"=>'0px',"src"=>$url);
	if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
	include template('bbsgame:cooperation');
	exit;
} elseif($_GET['action'] == 'iplayed') {
	@include_once(H_P."/require/challenge.class.php");
	$whoIs = array('me' => '我', 'other' => "TA");
	if($_GET['uid']) 
	{
		$uid = $_GET['uid'];
		$who = 'other';
		//$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		//$username = $userService->getUserNameByUserId($uid);
	}
	else 
	{
		$who = 'me';
		$uid = $_G['uid'];
		$username = $_G['username'];
	}
	if($uid) 
	{
		try{
			$challenge = new challenge();
			$timeLimit = $time - 2592000; //1 month
			$iplayedpk = 'IPLAYED_PK';
			$iplayednormal = 'IPLAYED_NORMAL';
			//个人玩
			$strSQL = "SELECT g.name AS gamename, g.gid ,c.addTime,COUNT(c.gid) AS count, MAX(score) AS max_score
					FROM ng_game_credit AS c LEFT JOIN ng_game g ON c.gid = g.gid
					WHERE c.uid = $uid AND c.status = 1 AND c.addTime > {$timeLimit}
					GROUP BY c.gid 
					ORDER BY c.addTime desc LIMIT 10";
			$query = DB::query($strSQL);
			while($result = DB::fetch($query) ){
				$result['addTime'] = date("Y-m-d H:i", $result['addTime']);
				$result['iplayedtype'] = $iplayednormal;
				$iPlayedLists[$result['addTime']] = $result;
			}
			//pk赛或擂台赛
			$strSQL = "SELECT pker.*, g.name AS gamename
					FROM ng_game_pker AS pker LEFT JOIN ng_game g ON pker.gid = g.gid
					WHERE pker.act_uid = $uid OR pker.def_uid = $uid  AND pker.ctime > {$timeLimit}
					ORDER BY pker.ctime desc LIMIT 10";
			$query = DB::query($strSQL);
			while($result = DB::fetch($query) ){
				$result['ctime'] = date("Y-m-d H:i", $result['ctime']);
				$result['iplayedtype'] = $iplayedpk;
				$iPlayedLists[$result['ctime']] = $result;
			}
			unset($challenge);
		}
		catch (challengeException $e) {
			$e->log();
		}
		$strSQL = "SELECT * FROM ng_game_user WHERE uid = $uid";
		$query = DB::query($strSQL);
		$registerInfo = DB::fetch($query);
	}
	else {
		$none = true;
		
	}
	$iplayed_select = "ctrl_selected";
	$anchor = "我玩过";		
	if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
	include template('bbsgame:content');
	exit;
} elseif($_GET['action'] == 'cooperation') {
	if(!$_G['uid']) {
		showmessage("您尚未登入，请登入后再玩互动游戏！");
		exit;
	}
	date_default_timezone_set("Asia/Hong_Kong");
			
	$gameFile = $_GET['coopid']."_module.php";
	$gamePath = H_P."/data/coop/".$gameFile;

	if(FILE_EXISTS($gamePath)) {
		include_once($gamePath);
	}

	if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
	include template('bbsgame:cooperation');
	exit;
} elseif($_GET['action'] == 'active') {
	@include_once(H_P."/require/challenge.class.php");
	try
	{
		$challenge = new challenge();

		$strSQL = "SELECT pk.pkname,pk.status,pk.gamename,pk.gid, pk.username, pk.starttime,pk.endtime,pk.id
				FROM ng_game_pk AS pk
				WHERE pk.gid = ".intval($pkgame_active_gameid)." AND pk.type = ".challenge::PK_SITENAL."
				ORDER BY pk.id";
		$query = DB::query($strSQL);
		$siteActive = DB::fetch($query);

		if($siteActive['id'])
		{
			$siteAdminReward = $pkgame_active_reward_price.$pkgame_active_reward_ctype;
			$siteActive['activetime'] = date("Y年m月d日", $siteActive['starttime'])."-".date("m月d日", $siteActive['endtime']);
		}
		else
			showmessage('抱歉，暂无站内活动');
	}
	catch (challengeException $e){
		$e->log();
	}
	$anchor = '站内活动详情';
	$active_pkerCreditNum = count(explode(":", $pkgame_challenge_algorithm_sitenal_pkerPercent));
	include template('bbsgame:active');
	exit;
} elseif($_GET['action'] == 'check') {
	$pid = S::getGP('pid','G',2);
	$c_r = S::getGP('c_r','G',2);
	$p   = S::getGP('p','G',2);
	$s   = S::getGP('s','G',2);
	$AJAX= S::getGP('AJAX','G',2);
		
	if ($AJAX)
	{
		ob_clean();
		try {
			//challenge_request
			if ($c_r) {
				@include_once(H_P."/require/challenge.class.php");
				$challenge = new challenge();
				
				$strSQL = "SELECT pker.act_username,pker.act_uid,pker.act_score
					FROM ng_game_pker AS pker 
					WHERE pker.pkid = {$pid}
					ORDER BY act_score DESC LIMIT {$s},{$p}";
				$query = DB::query($strSQL);
				while($result = DB::fetch($query) ){
					$arr[] = $result;
				}
				$ranking = $arr;
				unset($arr);

				if ($db_charset == 'gbk' && !empty($ranking)) {
					foreach ($ranking as $k=>$v) {
						$ranking[$k]['act_username'] = mb_convert_encoding($v['act_username'], 'UTF-8', 'GB2312');
					}
				}
				msg("",array("ret"=>1,"list"=>$ranking),0,true);
		
			}//pk_request
			else{
		
			}
		}
		catch (challengeException $e){
			$e->log();
		}
	}
	else {
		exit('forbidden');
	}
	exit;
} else {
	if(isset($_GET['list'])) {
		$list = !empty($_GET['list'])?trim($_GET['list']):'pk';
		$page = intval($_GET['p']);
		$pageSize = intval($_GET['s']);
		
		!$page && $page = 1;
		!$pageSize && $pageSize = 27;
		$scope = 3;
		$url = "plugin.php?id=bbsgame&action=list&list=$list&p=";
		$start = ($page-1)*$pageSize;
		
		$temp = $mode['JLJF'];
		$key = 'J_subm1';
		if($list =='jl')
		{
			$titleImg = 'source/plugin/bbsgame/images/1/title_03.jpg';
			!empty($temp[$key][gids]) && $sta = "`gid` IN (".$temp[$key][gids].") ";
			!empty($temp[$key][tids]) && $sta .= "OR `type` IN (".$temp[$key][tids].")";

		}
		else
		{
			$titleImg = 'source/plugin/bbsgame/images/1/title_05.jpg';
			!empty($temp[$key][gids]) && $sta = "`gid` NOT IN (".$temp[$key][gids].") ";
		}
		$query = DB::query("SELECT * FROM ng_game WHERE $sta");
		while($result = DB::fetch($query)){
			$arr[] = $result;
		}
		$count = count($arr);
		@include_once(H_P."/require/pageCss.class.php");
		$pageCss = new PageCss($pageSize, $scope, $url);
		$pageCss->setCounts($count);
		$pageCss->setCurrentpage($page);
		$pageCss = $pageCss->getHTML();
		$games = getGameByWhere("WHERE $sta LIMIT $start, $pageSize");
		foreach($games as $i=>$game) {
			$games[$i][img] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $game['img']);
		}
		foreach($temp as $k=>$v) {
			$catergories[$k] = $v['name'];
		}
		if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
		include template('bbsgame:listpage');
		exit;
	}
	else {//index
		$hotPlayedCount = 6;
		$jfpkGageCount = 16;
		$query = DB::query('SELECT G.name,G.gid,G.img,COUNT(*) AS count FROM ng_game_ip AS I LEFT JOIN ng_game AS G ON I.gid = G.gid GROUP BY I.gid ORDER BY count DESC LIMIT '.$hotPlayedCount);
		
		while($result = DB::fetch($query)){
			if (empty($result['img']) || empty($result['name'])) continue;
			$result['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $result['img']);
			$hotPlayedLists[] = $result;
		}

		include(H_P.'/data/jljf_config.php');

		$J_cnf = implode(",", $J_cnf);
		!$J_cnf && $J_cnf = 0;
		$games = getGameByWhere("WHERE `gid` NOT IN($J_cnf) ORDER BY `gid` DESC LIMIT $jfpkGageCount");
		foreach($games as $i=>$game) {
			$games[$i][img] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $game['img']);
		}
		$mode['JFPK'][0]['games'] = $games;
		
		foreach( $mode['JLJF'] as $k=>$v ) {
			empty($v[gids]) && $v[gids] = 0;
			empty($v[tids]) && $v[tids] = 0;
			$sta = "`gid` IN ($v[gids]) OR `type` IN ($v[tids])";
			$games = getGameByWhere("WHERE $sta order by `gid` desc LIMIT 7");
			foreach($games as $i=>$game) {
				$games[$i][img] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $game['img']);
			}
			$mode['JLJF'][$k]['games'] = $games;
		}
		$rankingGame = getRankingGame("LIMIT 10");
		
		$userActive = getUserActive("LIMIT 8");
		
		try{
			$challenge = new challenge();
			
			$strSQL = "SELECT pk.pkname,pk.status,pk.gamename,pk.starttime,pk.endtime,pk.id FROM {$table_pk} AS pk 
					WHERE pk.gid = ".intval($pkgame_active_gameid)." AND pk.type = ".challenge::PK_SITENAL." ORDER BY pk.id DESC";
			$query = DB::query($strSQL);
			$siteActive = DB::fetch($query);
			if($siteActive['id']){
				$siteActive['activetime'] = date("Y年m月d日", $siteActive['starttime'])."-".date("m月d日", $siteActive['endtime']);
				if ($siteActive['status'] == challenge::PK_STATUS_ING) {
					$siteActive['status'] = '进行中。。';
				}
				if ($siteActive['endtime'] < time()) {
					$siteActive['status'] = '已过期';
				}
				$strSQL = "SELECT * FROM {$table_pker} WHERE act_score > 0 AND pkid = $siteActive[id] ORDER BY act_score desc LIMIT 4";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					$arr[] = $result;
				}
				$partakeUsers = $arr;
			}
			unset($challenge);
		}
		catch (challengeException $e){
			$e->log();
		}
		$ng_host = 'http://www.97sng.com/public/bbs/';
		if($ng_charset == 'GB2312') {
			$ggjs_file = 'bbsgamegg_gbk.js';
			$ggjs = $ng_host.$ggjs_file;
		} else {
			$ggjs_file = 'bbsgamegg_utf.js';
			$ggjs = $ng_host.$ggjs_file;
		}

		setIndexJs($ggjs, $ggjs_file);
		if(!empty($_G['uid'])) {
			$strSQL = "SELECT `lose_num`,`win_num` FROM ng_game_user WHERE uid = $_G[uid]";
			$registerInfo = DB::fetch($strSQL);
		} else 
			$_G['uid'] = 0;

		// 接口获取互动游戏最新数目
		$gameStr = ng_get_contents(GAME_OG_API);
		$List = json_decode($gameStr,true);

		if($List['result'] !== '-1') {
			$gameList = explode("|", $List['result']);
			$newNum = count($gameList);
		} else {
			$newNum = 0;
		}

		preg_match("/\/.*\//", $_SERVER['REQUEST_URI'], $matches);
		$coopUrl = urlencode('http://'.$_SERVER[HTTP_HOST].$matches[0]."/plugin.php?id=bbsgame&amp;");
		$time = time();
		$coopNum = file_get_contents(H_P."/data/coop/coopnum.txt");
		$coopIframeHeight = ((ceil(intval($coopNum)/2))*110)."px";

		// 是否需要更新互动游戏控制
		if($newNum > $coopNum) { //当最新游戏数大于缓存游戏数时进行更新操作
			$dst = H_P.'/data/coop/coopnum.txt';
			writeover($dst, $newNum, 'wb+');
			for($i = 0; $i < $newNum; $i++) {
				$gameFN = explode(".", $gameList[$i]);
				$gameFile = $gameFN[0]."_module.php";
				$gameUrl = S_HOST."/games/hd/dz/".$db_charset."/".$gameList[$i];
				$gameData = file_get_contents($gameUrl);
				$gamePath = H_P."/data/coop/".$gameFile;
				if(!FILE_EXISTS($gamePath)) {
					writeover(H_P.'/data/coop/'.$gameFile, $gameData, 'wb+');
				}
			}
		}
		if (!empty($navtitle)) $navtitle = $pkgame_name; elseif(empty($nobbname)) $_G['setting']['bbname'] = $pkgame_name;
		include template('bbsgame:index');
		exit;
	}

}
/********************** pw_index.php 源码段修改结束 *************************/
?>