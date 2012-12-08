<?php
/*
* 
* Jun 13, 2012
* GBK
* 2:23:03 PM
* challenge.act.php
*/
	
S::gp(array("act"));
@include_once H_P."/require/challenge.class.php";
@include_once(H_P.'/data/jljf_config.php');

if($act == 'add') {
	S::gp(array("desc","case","endtime","pkname"));
	if ($case < $challenge_min_score || $case > $challenge_max_score)
		//msg("抱歉，消耗数超出范围，请设置在$challenge_min_score - $challenge_max_score 范围内。",array("ret"=>0, "error"=>1), DEBUG,1,$ajax);
		showmessage("抱歉，消耗数超出范围，请设置在$challenge_min_score - $challenge_max_score 范围内。");

	$cash = getUsercredit($_G['uid'],$pkgame_challenge_case_ctype);
	if ($cash) {
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = trim('extcredits'.$key);
			getuserprofile($ext);
			if((string)trim($pkgame_challenge_case_ctype) === (string)trim($ext)) {
				$exttitle = $value['title'];
				break;
			}
		}
		if ($cash < $pkgame_challenge_case_num) {
			//msg("抱歉，{$exttitle}不足，发起擂台需要{$pkgame_challenge_case_num}".$exttitle."",array("ret"=>0, "error"=>2), DEBUG,1,$ajax);
			showmessage("抱歉，{$exttitle}不足，发起擂台需要{$pkgame_challenge_case_num}".$exttitle."");
		}
	}
	
	if( preg_match('/[0-9]{4}-([0-9]{1,2})-([0-9]{1,2})/',$endtime, $matches) == false || $matches[1] > 12 || $matches[2] > 31  ) 
		//msg("结束日期不合法,请重新填写",array("ret"=>0), DEBUG,1,$ajax);
		showmessage("结束日期不合法,请重新填写");
	
	$endtime = strtotime($endtime);
	empty($_G[uid]) && showmessage("抱歉，您还没登录"); //msg("抱歉，您还没登录",array("ret"=>0), DEBUG,1,$ajax);
	empty($endtime) && showmessage("抱歉，结束时间不能为空"); //msg("抱歉，结束时间不能为空",array("ret"=>0), DEBUG,1,$ajax);
	empty($pkname) && showmessage("抱歉，擂台名不能为空"); //msg("抱歉，擂台名不能为空",array("ret"=>0), DEBUG,1,$ajax);
	empty($desc) && showmessage("请简单写下不能为空"); //msg("请简单写下不能为空",array("ret"=>0), DEBUG,1,$ajax);
	empty($gid) && showmessage("抱歉，获取游戏id失败。请重试。"); //msg("抱歉，获取游戏id失败。请重试。",array("ret"=>0), DEBUG,1,$ajax);
	try {
		$challenge = new challenge();
	
		$strSQL = "SELECT pk.* FROM {$table_pk} pk	WHERE pk.uid = $_G[uid] AND pk.gid = $gid AND pk.status = 1 LIMIT 1";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		$pkInfo = $result;

		if( !empty($pkInfo) )
			//msg("抱歉，您于这款游戏有个未完的擂台不能再创建一个新的。",array("ret"=>0, "error"=>3), DEBUG,1,$ajax);
			showmessage("抱歉，您于这款游戏有个未完的擂台不能再创建一个新的。");
		$challenge->endtime = S::sqlEscape($endtime);
		$challenge->case['num'] =  S::sqlEscape($case);
		$challenge->case['ctype'] = S::sqlEscape($pkgame_challenge_case_ctype);
		$challenge->desc = S::sqlEscape($desc);
		$challenge->pkname = S::sqlEscape($pkname);
		$challenge->status = challenge::PK_STATUS_ING;
		$challenge->ctime = S::sqlEscape(time());
		
		
		$where = "WHERE gid={$gid}";
		$result = getGameByWhere($where);
		$challenge->gamename = $result[0]['name'];
		
		$vars = get_object_vars($challenge);
		foreach($vars as $name=>$value) {
			if (empty($value)) {
				eval("\$challenge->$name = \"''\";");
			}
		}
		
		$user = array("uid"=>$_G['uid'],"username"=>S::sqlEscape($_G['username']));
		
		if ($challenge->add($user,$gid,challenge::PK_PERSONAL)) {
			//扣除防御者积分

			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				getuserprofile($ext);
				if((string)trim($pkgame_challenge_case_ctype) === (string)trim($ext)) {
					break;
				}
			}
			$dataarr = array($ext => -1*$pkgame_challenge_case_num);
			updatemembercount($_G['uid'], $dataarr, 1, 'TRC', $_G['uid']);

			showmessage("发起擂台成功");
			$url = "http://".$_SERVER['HTTP_HOST'];
			$dir = $_SERVER['SCRIPT_NAME'];
			$rid = explode("/", $dir);
			$ng_path = $url."/".$rid[1]."/";
			header("Location:{$ng_path}plugin.php?id=bbsgame&action=challenge");
		}
		else{
			//msg("提交失败，请重试。",array("ret"=>0), DEBUG,1,$ajax);
			showmessage("提交失败，请重试。");
		}
	}
	catch (challengeException $e) {
		$e->log();
		//msg("提交失败，请重试。",array("ret"=>0), DEBUG,1,$ajax);
		showmessage("提交失败，请重试。");
	}
}
elseif ($act == 'join') {
	
	empty($_G[uid]) && msg("not_login",array("ret"=>0));
	
	S::gp(array("pid","share",'cnt'));
	$db_charset == 'gbk' && $cnt = mb_convert_encoding($cnt, 'GB2312', 'UTF-8');
	
	$challenge = new challenge();

	$strSQL = "SELECT pk.*, m.uid as userid FROM {$table_pk} pk
			LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member m ON pk.uid = m.uid
			WHERE pk.id = {$pid} LIMIT 1";
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
	$pkInfo = $result;
	$size = "small";
	$pkInfo['icon'] = avatar($pkInfo['userid'], $size);
	if (empty($pkInfo)) {
		msg("challenge_not_exsit", array("ret"=>0), DEBUG);
	}

	$cash = getUsercredit($uid,$pkInfo['case_ctype']);
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = trim('extcredits'.$key);
		getuserprofile($ext);
		if((string)trim($pkgame_challenge_case_ctype) === (string)trim($ext)) {
			$exttitle = $value;
			break;
		}
	}
	!in_array($gid, $J_cnf) && $pkInfo['case'] > $cash && msg($exttitle.'不足',array("ret"=>0), DEBUG);
	if ($pkInfo['endtime'] < $time ) {
		msg("challenge_expired", array("ret"=>0,"t"=>$time), DEBUG);
	}
	if ($pkInfo['uid'] == $uid) {
		msg("challenge_same_act_def", array("ret"=>0,"t"=>$time), DEBUG);
	}
	
	$GLOBALS[challenge_gid] = $pkInfo['gid'];
	$GLOBALS[challenge_pkid] = $pkInfo['id'];
	
	$strSQL = "SELECT pker.act_uid FROM {$table_pker} pker WHERE pker.pkid = {$pid}";
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
	$pker = $result;
	if (!empty($pker)) {
		foreach ($pker as $v) {
			if ($v['act_uid'] == $_G['uid']){
				msg("challenge_already_joined",array("ret"=>1));
			}
		}
	}
	
	empty($_G['icon']) && $_G['icon'] = '';
	empty($pkInfo['icon']) && $pkInfo['icon'] = '';
	
	$act_user = array("uid"=>$_G['uid'], "username"=>S::sqlEscape($_G['username']),"usericon"=>S::sqlEscape($_G['icon']));
	$def_user = array("uid"=>$pkInfo['uid'], "username"=>S::sqlEscape($pkInfo['username']),"usericon"=>S::sqlEscape($pkInfo['icon']));
	
	if (false === $challenge->join($pkInfo['id'], $act_user, $def_user, $pkInfo['gid']) ) {
		msg("challenge_join_faild",array("ret"=>0),1);
	}
	else {
		//扣除积分
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if((string)trim($pkInfo['case_ctype']) === (string)trim($ext)) {
				break;
			}
		}
		$dataarr = array($ext => -1*$pkInfo['case']);
		updatemembercount($_G['uid'], $dataarr, 1, 'TRC', $_G['uid']);
		if ($share) {
			/*$weiboService = L::loadClass('weibo','sns');
			if (($return = $weiboService->sendCheck($cnt, $_G['groupid']))) {
				$weiboService->send($winduid, $cnt);
			}*/
		}
		msg("challenge_join_success",array("ret"=>1));
	}
}