<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
 
$identifier = 'bbsgame';
$operation = $_G['gp_operation'];
/********************************************/
define('R_P', dirname(__FILE__));
define('H_P', R_P);
@include_once(H_P.'/data/bbsgame_config.php');
@include_once H_P.'/data/jljf_config.php';
@include_once(H_P.'/require/functions.php');
$downloadRootPath = H_P."/data/";
@include_once(H_P."/data/game_config.php");

// $db_hackdb;
$table_game = 'ng_game';
$table_ip = 'ng_game_ip';
$table_credit = 'ng_game_credit';
$table_type = 'ng_game_type';
$table_shell = "ng_game_shell";
$table_pk = "ng_game_pk";

$db_charset = 'utf-8';
if($db_charset == 'gbk') {
	$ng_charset = 'GB2312';
	$gj = 'gonggaodz_gbk.js';
	$cj = 'contact_gbk.js';
}else if($db_charset == 'utf-8') {
	$ng_charset = 'UTF-8';
	$gj = 'gonggaodz_utf8.js';
	$cj = 'contact_utf8.js';
}

if(!$pid) {
	showtableheader('提示信息');
	showtablerow('', array('width="100%"'), array("资料尚未完善，请先<a href=\"admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=admincp\">完善资料</a><br /><br />请先确认 bbsgame/data 缓存目录的可写权限已经开启"));
	exit;
}

$gg_js = "<script>document.write(\"<script src='http://www.97sng.com/public/bbs/{$gj}?t=\"+new Date().getTime()+\"'\><\/script>\");</script>";
$lx_js = "<script>document.write(\"<script src='http://www.97sng.com/public/bbs/{$cj}?t=\"+new Date().getTime()+\"'\><\/script>\");</script>";

showtableheader('提示信息');
$str = "1、本插件为合作运营产品，合作上千家社区站点，活跃度、粘度较高，为站长有效服务；<br />
2、本插件对于用户使用本产品，无外部跳转，保证活跃站内用户的同时，不流失任何用户；<br />
3、游戏内容由我司无偿提供，由站点自身选择安排；<br />
4、游戏充值等产生的收益与站内分成；<br />
5、站长一些想法可通过我联合平台实现站外共同盈收；";
showtablerow('', array('width="5%"', 'width="95%"'), array('', $str));
showtablerow('', array('width="5%"', 'width="95%"'), array('公告：', $gg_js));
showtablerow('', array('width="5%"', 'width="95%"'), array('联系', $lx_js));

showtableheader('<a href="admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk">设置站内擂台</a> | <a href="admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&list=1">所有擂台列表</a>');

/*************************************************************/
if(!$_GET['type'] && !$_GET['list'] && !$_GET['signflag']) {

	//$query = DB::query("SELECT uid, gid, max(score) AS score FROM {$table_credit} GROUP BY uid,gid");
	$query = DB::query("SELECT max(score) AS score FROM {$table_credit} GROUP BY gid");
	while($result = DB::fetch($query)) {
		$res[] = $result;
	}
	
	$num = count($res);
	$strinscore = "";
	for($i = 0; $i < $num; $i++) {
		$strinscore .= $res[$i]['score'].",";
	}
	$strinscore = substr($strinscore, 0, (strlen($strinscore) - 1));
	!$strinscore && $strinscore = "0";
	
	!$page && $page = 1;
	!$limit && $limit = 24;
	if($_GET['p']) $p = $_GET['p']; else $p = 1;
	!($start = ($p - 1) * $limit) && ($start = 0);
	$scope = 3;
	$url = "admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&p=";
	@include_once(H_P."/require/pageCss.class.php");

	$query = DB::query("SELECT COUNT(*) AS total  
						FROM {$table_credit} AS c 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member AS m ON c.uid=m.uid
						LEFT JOIN {$table_game} AS g ON c.gid=g.gid
						WHERE c.score in($strinscore)");
	$result = DB::fetch($query);
	$total = $result['total'];

	$query = DB::query("SELECT c.uid, m.username, c.gid, g.name, g.img, c.score 
						FROM {$table_credit} AS c 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member AS m ON c.uid=m.uid
						LEFT JOIN {$table_game} AS g ON c.gid=g.gid
						WHERE c.score in($strinscore)
						LIMIT {$start},{$limit}");
	while($result = DB::fetch($query)) {
		$games[] = $result;
	}

	$pageCss = new PageCss($limit, $scope, $url);
	$pageCss->setCounts($total);
	$pageCss->setCurrentpage($page);
	$pageCss = $pageCss->getHTML();

	showtablerow('', array('colspan="4" width="100%"'), 
				array('站内各游戏最高得分用户'));
	$i = 1;
	$str = "<table><tr>";

	foreach($games as $k=>$v) {
		$img = preg_replace('/_120.jpg/', '.jpg', $v['img']);
		$str .= "<td>{$v['username']}&nbsp;<a href='admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&uid={$v['uid']}&gid={$v['gid']}&type=2'>设置擂台</a><br /> 游戏：{$v['name']}<br /><img src=\"http://img.97sng.com/pic/{$img}\"><br />得分：{$v['score']}</td>";
		
		if($i % 8 == 0) {
			$str .= "</tr><tr>";
		}
		$i++;
	}
	$str .= "</tr></table>";
	showtablerow('', array('width="100%"'), array($str));
	showtablerow('', array('width="100%"'), array($pageCss));
	showformfooter();
	showtablefooter();
	exit;
}
else if($_GET['type']){
	!($uid = $_GET['uid']) && ($uid = 0);
	!($gid = $_GET['gid']) && ($gid = 0);
	
	$query = DB::query("SELECT max(score) AS score FROM {$table_credit} WHERE affect>=0 AND gid='{$gid}' AND uid='{$uid}' GROUP BY gid,uid");
	$re = DB::fetch($query);
	
	$score = $re['score'];
	
	$query = DB::query("SELECT m.username, g.name, g.ctype FROM {$table_credit} AS c 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member AS m ON c.uid=m.uid
						LEFT JOIN {$table_game} AS g ON c.gid=g.gid
						WHERE c.uid={$uid} AND c.gid={$gid}");
	$result = DB::fetch($query);
	
	if($_POST['submit']) {
		//print_r($_POST); 	
		$username = $result['username'];
		!($price = $_POST['price']) && ($price = 0);
		!($price_ctype = $_POST['price_ctype']) && ($price_ctype = 0);
		!($rate = $_POST['rate']) && ($rate = 0);
		!($rate_ctype = $_POST['rate_ctype']) && ($rate_ctype = 0);
		!($case = $_POST['case']) && ($case = 0);
		$type = 2;
		$starttime = mktime(0, 0, 0, $_POST['smonth'], $_POST['sday'], $_POST['syear']);
		$endtime = mktime(0, 0, 0, $_POST['emonth'], $_POST['eday'], $_POST['eyear']);
		$time = time();
		
		$sql = "SELECT * FROM {$table_pk} WHERE `uid`='{$uid}' AND `gid`='{$gid}' AND `type`='{$type}'";
		$query = DB::query($sql);
		$res = DB::fetch($query);

		if($res['id']) {
			$sql = "UPDATE {$table_pk} SET `price`='{$price}',`price_ctype`='{$price_ctype}',`starttime`='{$starttime}',`endtime`='{$endtime}',`ctime`='{$time}' WHERE `id`='{$res['id']}'";
		} else {
			$sql = "INSERT INTO {$table_pk} (`gid`, `uid`, `username`, `price`, `price_ctype`, `rate`, `case`, `type`, `starttime`, `endtime`, `ctime`) 
						values('{$gid}', '{$uid}', '{$username}', '{$price}', '{$price_ctype}', '{$rate}', '{$case}', '{$type}', '{$starttime}', '{$endtime}', '{$time}')";
		}
		
		//echo $sql;
		$query = DB::query($sql);
		HEADER("LOCATION: admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&uid={$uid}&gid={$gid}&score={$score}");
		exit;
	}

	showformheader("plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&uid={$uid}&gid={$gid}&score={$score}&type=2");

	//print_r($result);
	showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('游戏名：', $result['name'].'<input class="input" type="hidden" name="ng_name" value="'.$result['name'].'" /><input class="input" type="hidden" name="ng_id" value="'.$gid.'" />'));
	showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('擂主：', $result['username'].'【最高游戏分数：'.$score.'】<input class="input" type="hidden" name="username" value="'.$result['username'].'" /><input class="input" type="hidden" name="userid" value="'.$uid.'" />'));
	
	$str = "";
	foreach($_G['setting']['extcredits'] as $key => $value){
		$ext = 'extcredits'.$key;

		if((string)trim($ext) === (string)trim($result['ctype'])) $value_sel = "selected"; else $value_sel = "";
		$str .= "<option value=\"{$ext} \" {$value_sel}>{$value['title']}</option>";
	}
	$selstr = "<select name=\"price_ctype\">{$str}</select>";	// 奖金选择
	$selcase = "<select name=\"rate_ctype\">{$str}</select>";	// pk扣费选择

	showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('奖励：', '<input class="input" type="text" name="price" value="'.$result['price'].'" size="5"/>'.$selstr));
	//showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('税率：', '<input class="input" type="text" name="rate" value="'.$result['rate'].'" size="5"/>%'));
	//showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('PK扣费：', '<input class="input" type="text" name="case" value="'.$result['case'].'" size="5"/>'.$selcase));

	$now_year = date('Y');
	$now_month = date('m');
	$now_day = date('d');
	$selstart = "<select name=\"syear\">";
	for($i = $now_year; $i <= ($now_year + 10); $i++) {
		$selstart .= "<option value=\"{$i}\">{$i}</option>";
	}
    $selstart .= "</select>";
	$selstart .= "<select name=\"smonth\">";
	for($i = 1; $i <= 12; $i++) {
		if($i == $now_month) $value = "selected"; else $value = "";
		$selstart .= "<option value=\"{$i}\" {$value}>{$i}</option>";
	}
	$selstart .= "</select>";
	$selstart .= "<select name=\"sday\">";
	for($i = 1; $i <= 31; $i++) {
		if($i == $now_day) $val = "selected"; else $val = "";
		$selstart .= "<option value=\"{$i}\" {$val}>{$i}</option>";
	}
	$selstart .= "</select>";

	$selend = "<select name=\"eyear\">";
	for($i = $now_year; $i <= ($now_year + 11); $i++) {
		$selend .= "<option value=\"{$i}\">{$i}</option>";
	}
    $selend .= "</select>";
	$selend .= "<select name=\"emonth\">";
	for($i = 1; $i <= 12; $i++) {
		if($i == $now_month) $value = "selected"; else $value = "";
		$selend .= "<option value=\"{$i}\" {$value}>{$i}</option>";
	}
	$selend .= "</select>";
	$selend .= "<select name=\"eday\">";
	for($i = 1; $i <= 31; $i++) {
		if($i == $now_day) $val = "selected"; else $val = "";
		$selend .= "<option value=\"{$i}\" {$val}>{$i}</option>";
	}
	$selend .= "</select>";

	showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('起始时间：', $selstart));
	showtablerow('', array('width="10%" align="right"', 'width="90%" align="left"'), array('结束时间：', $selend));
	
	showsubmit('submit','submit');

	showformfooter();
	showtablefooter();
	exit;
}
else if($_GET['list']) {
	!$page && $page = 1;
	!$limit && $limit = 24;
	if($_GET['p']) $p = $_GET['p']; else $p = 1;
	!($start = ($p - 1) * $limit) && ($start = 0);
	$scope = 3;
	$url = "admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&p=";
	@include_once(H_P."/require/pageCss.class.php");

	$query = DB::query("SELECT COUNT(*) AS total FROM {$table_pk} AS p 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member AS m ON p.uid=m.uid
						LEFT JOIN {$table_game} AS g ON p.gid=g.gid");
	$result = DB::fetch($query);
	$total = $result['total'];

	$query = DB::query("SELECT m.username, g.name, p.* FROM {$table_pk} AS p 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member AS m ON p.uid=m.uid
						LEFT JOIN {$table_game} AS g ON p.gid=g.gid
						LIMIT {$start},{$limit}");
	while($result = DB::fetch($query)) {
		$gamepks[] = $result;
	}
	
	$pageCss = new PageCss($limit, $scope, $url);
	$pageCss->setCounts($total);
	$pageCss->setCurrentpage($page);
	$pageCss = $pageCss->getHTML();

	showtablerow('', array('width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"'), array('<b>属性</b>','<b>游戏</b>', '<b>擂主</b>', '<b>奖励</b>', '<b>税率</b>', '<b>费用</b>',  '<b>开始时间</b>', '<b>结束时间</b>', '<b>编辑</b>', ''));

	foreach($gamepks as $k=>$v) {
		$stime = date('Y-m-d', $v['starttime']);
		$etime = date('Y-m-d', $v['endtime']);
		if($v['type'] == 1) $type = "全国"; elseif($v['type'] == 2) $type = "站内"; elseif($v['type'] == 3) $type = "个人"; else $type = "";

		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;

			if((string)trim($ext) === (string)trim($v['price_ctype'])) {
				$val = $value['title'];
				break;
			}
		}

		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;

			if((string)trim($ext) === (string)trim($v['rate_ctype'])) {
				$rval = $value['title'];
				break;
			}
		}

		showtablerow('', array('width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"', 'width="10%"'), array($type, $v['name'], $v['username'], $v['price']."{$val}", $v['rate']."{$rval}", $v['case'], $stime, $etime, "<a href=\"admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&uid={$v['uid']}&gid={$v['gid']}&type=2\">编辑</a>", ""));
	}

	showtablerow('', array('width="100%" colspan="9"'), array($pageCss));
	showformfooter();
	showtablefooter();
	exit;
}
else if($_GET['signflag']) {

	if($_POST['submit']) {
		$data = "\$pid=\"{$pid}\";\r\n";
		$data .="\$ifopen=\"{$ifopen}\";\r\n";
		$data .="\$name=\"{$name}\";\r\n";
		$data .="\$phone=\"{$phone}\";\r\n";
		$data .="\$siteurl=\"{$siteurl}\";\r\n";
		$data .="\$sitename=\"{$sitename}\";\r\n";
		$data .="\$qq=\"{$qq}\";\r\n";
		$data .="\$mail=\"{$mail}\";\r\n";
		$data .="\$init=\"{$init}\";\r\n";
		$data .="\$ng_name=\"{$ng_name}\";\r\n";
		$data .="\$db_bbsname=\"{$db_bbsname}\";\r\n";
		$data .="// 个人擂台公共统一配置参数\";\r\n";
		$data .="\$ng_rate=\"{$_POST['ng_rate']}\";\r\n";
		$data .="\$ng_case_config=\"{$_POST['ng_case_config']}\";\r\n";
		$data .="\$ng_case_config_ctype=\"{$_POST['ng_case_config_ctype']}\";\r\n";

		$res = writeover(H_P.'/data/bbsgame_config.php',"<?php\r\n".$data."?>");
		if($res) echo '<font color=red>个人擂台统一配置已经完成</font>，<a href="admin.php?action=plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&signflag=1">查看配置结果</a>';
		exit;
	} else {

		showformheader("plugins&operation=config&do=15&identifier=bbsgame&pmod=adminpk&signflag=1");

		showtablerow('', array('width="15%"', 'width="12%" align="right"', 'width="73%"'), array('', '个人擂台税率设置：','<input type="text" name="ng_rate" value="'.$ng_rate.'" size=4>%【税率设置后将会按此比例回收参加个人擂台赛花费的费用给站点】'));

		$case_str  = "<select name='ng_case_config'>";
		if((string)trim($ng_case_config) === '20~40') $value = "selected"; else $value = "";
		$case_str .= "<option value='20~40' {$value}>20~40</option>";
		if((string)trim($ng_case_config) === '40~60') $value = "selected"; else $value = "";
		$case_str .= "<option value='40~60' {$value}>40~60</option>";
		if((string)trim($ng_case_config) === '60~100') $value = "selected"; else $value = "";
		$case_str .= "<option value='60~100' {$value}>60~100</option>";
		if((string)trim($ng_case_config) === '100~300') $value = "selected"; else $value = "";
		$case_str .= "<option value='100~300' {$value}>100~300</option>";
		if((string)trim($ng_case_config) === '300~500') $value = "selected"; else $value = "";
		$case_str .= "<option value='300~500' {$value}>300~500</option>";
		if((string)trim($ng_case_config) === '500~1000') $value = "selected"; else $value = "";
		$case_str .= "<option value='500~1000' {$value}>500~1000</option>";
		$case_str .= "</select>";

		$str = "";
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;

			if((string)trim($ext) === (string)trim($ng_case_config_ctype)) $value_sel = "selected"; else $value_sel = "";
			$str .= "<option value=\"{$ext} \" {$value_sel}>{$value['title']}</option>";
		}
		$selstr = "<select name=\"ng_case_config_ctype\">{$str}</select>";	// 奖金选择

		showtablerow('', array('width="15%"', 'width="12%"  align="right"', 'width="73%"'), array('', '个人擂台扣费限额设置：',$case_str.$selstr));

		showsubmit('submit','submit');
		showformfooter();
		showtablefooter();
		exit;
	}
}
?>