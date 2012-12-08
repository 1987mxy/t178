<?php
/*
* 
* Jun 13, 2012
* GBK
* 2:23:26 PM
* AgudaZaric
* pk.act.php
*/

include_once H_P."/require/challenge.class.php";
include_once(H_P.'/data/jljf_config.php');

S::gp(array('content',"act"));

//发出挑战
if( $act == 'add' ) {
	S::gp(array("def_uid","def_user","def_uid_ass","case","casetype","share","endtime","act_content",'ajax'));
	
	if(!$def_uid) $def_uid = getUserInfoWithUsername($def_user);
	if(!$def_uid_ass) $def_uid_ass = getUserInfoWithUsername($def_uid_ass);

	if(!$def_uid) $def_uid = 0;
	if(!$def_uid_ass) $def_uid_ass = 0;
	empty($_G['uid']) && msg("uid = $uid",array("ret"=>0, "err"=>1),0,1,$ajax);
	if ($def_uid == $_G['uid'])
		msg("不能挑战自己",array("ret"=>0, "err"=>1),0,1,$ajax);
		//showmessage("不能挑战自己");
	if($_G['charset'] == 'gbk') {
		$act_content = mb_convert_encoding($act_content, "GB2312", "UTF-8");
	}

	$credit_ctype = array();
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = trim('extcredits'.$key);
		getuserprofile($ext);
		$credit_ctype[] = $ext;
	}

	if( !array_key_exists($casetype, $credit_ctype) ) $casetype = "extcredits2";
	$endtime = strtotime($endtime);
	$endtime < $time && msg("pk_time_illegal", array("ret"=>0, "err"=>2),0,1,$ajax);//showmessage("时间设置错误"); 
	$case < 0 && msg('抱歉，消耗值要大于或等于0。',null,0,1,$ajax);//showmessage("抱歉，消耗值要大于或等于0。"); 
	
	$cash = getUsercredit($uid,$casetype);
	if (empty($def_uid)) 
		$def_uid = $def_uid_ass;
	if ($case < $pk_min_score || $case > $pk_max_score) 
		msg("抱歉，消耗数超出范围，请设置在$pk_min_score - $pk_max_score 范围内。",array("ret"=>0, "err"=>3), DEBUG,1,$ajax);
		//showmessage("抱歉，消耗数超出范围，请设置在$pk_min_score - $pk_max_score 范围内。");
	!in_array($gid, $J_cnf) && $case > $cash && msg("抱歉，".$casetype.'不足。快去奖励积分游戏赚取吧。',array("ret"=>0, "err"=>4), DEBUG,1,$ajax); //showmessage("抱歉，".$casetype."不足。快去奖励积分游戏赚取吧。")
	$cash = getUsercredit($def_uid,$casetype);
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = trim('extcredits'.$key);
		getuserprofile($ext);
		if((string)trim($casetype) === (string)trim($ext)) {
			$casetypeTitle = $value['title'];
			break;
		}
	}
	!in_array($gid, $J_cnf) && $case > $cash && showmessage("抱歉，对方".$casetypeTitle."不足。"); //msg("抱歉，对方".$casetype.'不足。',array("ret"=>0, "err"=>5), DEBUG,1,$ajax);
	
	$userInfo = getUserInfoWithUid($def_uid);
	$usernames = $userInfo['username'];
	$atc_title = trim($_G['username']."向您发起游戏挑战 .");

	{//validate message
		if ("" == $usernames) {
			msg("抱歉，挑战对象不能为空。请稍后再试。", array("ret"=>0, "err"=>6),0,1,$ajax);
			//showmessage("抱歉，挑战对象不能为空。请稍后再试。");
		}

 		$usernames = is_array($usernames) ? $usernames : explode(",", $usernames);

		if ("" == $act_content) {
			msg("内容不能为空。", array("ret"=>0, "err"=>7),0,1,$ajax);
			//showmessage("内容不能为空。");
		}
		if( isset($_G['messagecontentsize']) && $_G['messagecontentsize'] > 0 && strlen($act_content) > $_G['messagecontentsize']){
			ajaxExport(array('bool' => false, 'message' => ''));
			msg('内容超过限定长度'.$_G['messagecontentsize'].'字节', array("ret"=>0, "err"=>8),0,1,$ajax);
			//showmessage("内容超过限定长度".$_G['messagecontentsize']."字节");
		}
	}

	
	//create relationship
	try{
		$challenge = new challenge();
		$challenge->endtime = S::sqlEscape($endtime);
		$challenge->starttime = $challenge->ctime = S::sqlEscape(time());
		$challenge->case['num'] =  S::sqlEscape($case);
		$challenge->case['ctype'] = S::sqlEscape($casetype);
		$challenge->desc = empty($desc) ? "''" : S::sqlEscape(cnt);
		$challenge->pkname = empty($name) ? "''" : S::sqlEscape($name);
		$challenge->status = challenge::PK_STATUS_MAKESURE;
		$challenge->gamename = S::sqlEscape($name);
		
		$vars = get_object_vars($challenge);
		foreach($vars as $name=>$value) {
			if (empty($value)) {
				eval("\$challenge->$name = \"''\";");
			}
		}

			
		$strSQL = "SELECT pk.id  
						FROM {$table_pker} pker LEFT JOIN {$table_pk} pk ON pk.id = pker.pkid
						WHERE pker.def_uid = $def_uid AND pker.act_uid = ".$_G['uid']." AND pker.gid = $gid AND pk.type = ".challenge::PK_PK." AND (pk.status = ".challenge::PK_STATUS_MAKESURE." OR pk.endtime > {$time})";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		
		$result['pkid'] && msg("pk_duplicate",array("ret"=>0),0,1,$ajax);
		
		$pk_user = array("uid"=>$userInfo['uid'],"username"=>S::sqlEscape($userInfo['username']),"usericon"=>S::sqlEscape($userInfo['icon']));
		
		
		$challenge->add($pk_user, $gid, challenge::PK_PK);
		
		$pk_id = $challenge->getInsertId($pk_user['uid'], $gid);
		$act_user = array("uid"=>$_G['uid'], "username"=>S::sqlEscape($_G['username']),"usericon"=>S::sqlEscape($_G['icon']));
		
		$challenge->join($pk_id, $act_user, $pk_user, $gid);
		
		// DZ 扣除发起者积分
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if((string)trim($casetype) === (string)trim($ext)) {
				$casetypeTitle = $value['title'];
				break;
			}
		}
		$dataarr = array($ext => -1*$case);
		updatemembercount($_G['uid'], $dataarr, 1, 'TRC', $_G['uid']);
	
	}
	catch (challengeException $e) {
		$e->log();
		msg("pk_add_faild",array("ret"=>0),DEBUG,1,$ajax);
		//showmessage("抱歉，挑战TA失败了，请刷新重试。不服就来单挑，加油再挑战更高的成绩吧！");
	}
	/*if( $share =='on' ) {
		
		//分享到
		$weiboService = L::loadClass('weibo','sns');
		if (($return = $weiboService->sendCheck($act_content, $_G['groupid']))) {
			$weiboService->send($winduid, $act_content);
		}
	}*/

	
	//send message
	{
		//$filterUtil = L::loadClass('filterutil', 'filter');
		$url = "http://".$_SERVER['HTTP_HOST'];
		$dir = $_SERVER['SCRIPT_NAME'];
		$ng_path = substr($dir, 0, (strlen($dir)-36));
		$act_content = "输者将被扣除 :$case ".$casetypeTitle." [url={$url}{$ng_path}plugin.php?id=bbsgame&action=game&gid=$gid&mode=pk&pk_id={$pk_id}]接受挑战[/url]";

		sendpm($def_uid, $atc_title, $act_content, $_G['uid']);
	}
	$GLOBALS['def_name']  = $userInfo['username'];
	$GLOBALS[LOCATION_HREF] = "plugin.php?id=bbsgame&action=game&gid=$gid&mode=pk&pk_id=$pk_id";
	msg("pk_add_success",array("ret"=>1,'hook' => "location.href='".$GLOBALS[LOCATION_HREF]."';"),0,1,$ajax);
	//showmessage("恭喜，已成功向 @".$GLOBALS['def_name']." 发起挑战通知，待对方确认，加油哦！");
	exit;
}
else if( $act == 'join' ) {
	
	S::gp(array("pid"));
	try{
		$challenge = new challenge();
		$strSQL = "SELECT pk.* FROM {$table_pk} pk
				WHERE pk.id = $pid AND pk.status = ".challenge::PK_STATUS_MAKESURE." AND pk.type = ".challenge::PK_PK."	LIMIT 1";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		$pkInfo = $result;
		$cash = getUsercredit($uid,$pkInfo['case_ctype']);
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if((string)trim($pkInfo['case_ctype']) === (string)trim($ext)) {
				$casetypeTitle = $value['title'];
				break;
			}
		}
		!in_array($gid, $J_cnf) && $pkInfo['case'] > $cash && msg("抱歉，".$casetypeTitle.'不足，快去奖励积分游戏赚取吧。',array("ret"=>0), DEBUG);
		if( empty($pkInfo) ) {
			msg("challenge_not_exsit", array("ret"=>0), DEBUG);
		}
		if( $pkInfo['endtime'] < $time ) {
			msg("challenge_expired", array("ret"=>0,"t"=>$time), DEBUG);
		}
		if( $pkInfo['uid'] == $uid ) {
			msg("challenge_same_act_def", array("ret"=>0,"t"=>$time), DEBUG);
		}
			
		$act_user = array("uid"=>$_G['uid'], "username"=>S::sqlEscape($_G['username']));

		if(false === $challenge->acceptPk($pid, $uid) ) {
			msg("pk_accept_faild",array("ret"=>0),DEBUG);
		}
		else {
			// DZ 扣除防御者积分
			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				getuserprofile($ext);
				if((string)trim($pkInfo['case_ctype']) === (string)trim($ext)) {
					break;
				}
			}
			$dataarr = array($ext => -1*$pkInfo['case']);
			updatemembercount($uid, $dataarr, 1, 'TRC', $uid);
			msg("pk_accept_success",array("ret"=>1));
		}
	}
	catch (challengeException $e) {
		$e->log();
		msg("pk_accept_faild",array("ret"=>0),DEBUG);
	}
}
elseif ($act=='refuse') {
	S::gp(array("pid"));
	
	try{
		$challenge = new challenge();
		
		$strSQL = "SELECT pk.* FROM {$table_pk} pk
				WHERE pk.id = {$pid} AND pk.status = ".challenge::PK_STATUS_MAKESURE." AND pk.type = ".challenge::PK_PK." LIMIT 1";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		$pkInfo = $result;
		if( empty($pkInfo) ) {
			msg("operate_failed", array("ret"=>0), DEBUG);
		}
		if( $pkInfo['endtime'] < $time ) {
			msg("challenge_expired", array("ret"=>0,"t"=>$time), DEBUG);
		}
		if( $pkInfo['uid'] != $uid ) {
			msg("challenge_no_privilege", array("ret"=>0,"t"=>$time), DEBUG);
		}
		$strSQL = "SELECT `act_uid` FROM {$table_pker} WHERE pkid = {$pid}";
		$act = DB::fetch_first($strSQL);
		$act_uid = $act['act_uid'];
		
		if(false === $challenge->refusePk($pid) ) {
			msg("operate_faild",array("ret"=>0),DEBUG);
		}
		else {
			//返还押金
			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				getuserprofile($ext);
				if((string)trim($pkInfo['case_ctype']) === (string)trim($ext)) {
					break;
				}
			}
			$dataarr = array($ext => $pkInfo['case']);
			updatemembercount($uid, $dataarr, 1, 'TRC', $uid);

			sendpm($act_uid, "挑战被拒绝", "挑战书$pkInfo[id]:{名称:$pkInfo[name],游戏:$pkInfo[gamename]} 被对方拒绝", $_G['uid']);
			msg("operate_success",array("ret"=>1));
		}
	}
	catch (challengeException $e) {
		$e->log();
		msg("pk_refuse_faild",array("ret"=>0),DEBUG);
	}
}