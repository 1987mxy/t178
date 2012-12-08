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

define('API_HOST','http://www.97sng.com');
define('SWF_HOST','http://img.97sng.com/flash');
define('PIC_HOST','http://img.97sng.com/pic');
define('GAME_INFO',API_HOST.'/apps/clubapi.ashx'); //人气接口,1：游戏列表（按添加时间倒序）

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
	showtablerow('', array('width="100%"'), array("资料尚未完善，请先<a href=\"admin.php?action=plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=admincp\">完善资料</a><br /><br />请先确认 bbsgame/data 缓存目录的可写权限已经开启"));
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

/*************************************************************/
if(!$_POST['submit']) {
	if($_GET['flag'] && $_GET['gid']) {
		$gid = $_GET['gid'];
		if($_GET['del']) {
			$uid = $_GET['uid'];
			$sql = "SELECT gid FROM ng_game_credit 
				WHERE gid='{$gid}' AND uid='{$uid}'";
			$query = DB::query($sql);
			$result = DB::fetch($query); 

			if($result['gid']) {
				$sql = "DELETE FROM ng_game_credit 
					WHERE gid='{$gid}' AND uid='{$uid}'";
				$query = DB::query($sql);
			}
		}
		
		$sql = "SELECT g.gid, g.name, c.uid, m.username, max(c.score) as score FROM ng_game_credit c 
			LEFT JOIN ng_game g ON c.gid=g.gid 
			LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member m ON c.uid = m.uid
			WHERE c.gid='{$gid}'
			group by c.uid
			order by score desc";
		$query = DB::query($sql);
		while($result = DB::fetch($query)) {
			$games[] = $result;
		}

		$tabStr = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr class=\"tr1 vt\"><th><b>游戏</b></th><th>玩家</th><th>游戏分</th><th>清除</th></tr>";
					
		foreach($games as $v) {
			$tabStr .= "<tr><td>{$v[name]}</td><td>{$v[username]}</td><td>{$v[score]}</td><td><a href=\"admin.php?action=plugins&operation=config&do=13&identifier=bbsgame&pmod=adminacc&flag=1&del=1&gid=$v[gid]&uid=$v[uid]\">清除</a></td></tr>";
		}

		$tabStr .= "</table>";
		showtablerow('', array('width="100%"'), array($tabStr));
		exit;
	} else {
		if(isset($_GET['J']) && $J = $_GET['J']){
			$_sql = "AND `gid` in (".implode(",", $J_cnf).")";
		}
		else {
			$_sql = "AND `gid` NOT in (".implode(",", $J_cnf).")";
		}

		$query = DB::query("SELECT * FROM {$table_game} WHERE installed = 1 {$_sql}");
		while($result = DB::fetch($query)) {

			if(is_array($conf_game[$result[gid]])){
				$result['rate0'] = $conf_game[$result[gid]]['rate0'];
				$result['rate1'] = $conf_game[$result[gid]]['rate1'];
				$result['useup'] = $conf_game[$result[gid]]['useup'];
			}
			$games[] = $result;
		}
		//$moneyset = 'current';
		
		showtableheader('提示信息');
		showtablerow('', array('width="5%"', 'width="95%"'), array('公告：', '关于社区游戏的最新动态，请关注<a href="http://www.97sng.com">97SNG</a>'));
		showtablerow('', array('width="5%"', 'width="95%"'), array('联系', '意见反馈: <a href="tencent://message/?uin=168360500">QQ留言</a><br/>技术支持: <a href="tencent://message/?uin=394318815">QQ留言</a><br/>官方交流群:153503288'));
		showtableheader('<a href="admin.php?action=plugins&operation=config&do=13&identifier=bbsgame&pmod=adminacc">积分PK游戏</a> | <a href="admin.php?action=plugins&operation=config&do=13&identifier=bbsgame&pmod=adminacc&J=1">奖励积分游戏</a>');

		showformheader("plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminacc");
		showtablerow('', array('width="20%"', 'width="20%"', 'width="20%"', 'width="20%"', 'width="20%"'), 
					array('<b>游戏</b>', '开始游戏消耗', '游戏币', '积分', '查看/清除用户排名'));
		foreach($games as $k=>$v) {
			$row1 = "{$v['name']}<input name=\"game[{$v['gid']}]\" type=\"hidden\" value=\"{$v['gid']}\" />";
			$row2 = "<input {$disable} type=\"text\" name=\"useup[{$v['gid']}]\" value=\"{$v['useup']}\" size=\"14\" />";
			$str = "";
			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				//getuserprofile($ext);
				//echo $value['title']."=>".$_G['member'][$ext];
				if((string)trim($ext) === (string)trim($v['useup_ctype'])) $value_sel = "selected"; else $value_sel = "";
				$str .= "<option value=\"{$ext} \" {$value_sel}>{$value['title']}</option>";
			}
			$row2 .= "<select name=\"useup_ctype[{$v['gid']}]\">{$str}</select>";
			$row3 = "<label>达到</label>&nbsp;&nbsp;<input class=\"input\" type=\"text\" name=\"rate0[{$v['gid']}]\" value=\"{$v['rate0']}\" size=\"14\" />";
			$row4 = "<label>奖励</label>&nbsp;&nbsp;<input class=\"input\" type=\"text\" name=\"rate1[{$v['gid']}]\" value=\"{$v[rate1]}\" size=\"14\" />";
			$str = "";
			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				//getuserprofile($ext);
				//echo $value['title']."=>".$_G['member'][$ext];
				if((string)trim($ext) === (string)trim($v['ctype'])) $value_sel = "selected"; else $value_sel = "";
				$str .= "<option value=\"{$ext} \" {$value_sel}>{$value['title']}</option>";
			}
			$row4 .= "<select name=\"ctype[{$v['gid']}]\">{$str}</select>";
			$row5 = "<a href=\"admin.php?action=plugins&operation=config&do=13&identifier=bbsgame&pmod=adminacc&flag=1&gid={$v['gid']}\">查看/清除用户排名</a>";
			showtablerow('', array('width="20%"', 'width="20%"', 'width="20%"', 'width="20%"', 'width="20%"'), array($row1, $row2, $row3, $row4, $row5));
		}
		showsubmit('submit','submit');
		showformfooter();
		showtablefooter();
	}
}
else{
	//print_r($_POST); exit;

	$game = $_POST['game'];
	$useup = $_POST['useup'];
	$useup_ctype = $_POST['useup_ctype'];
	$rate0 = $_POST['rate0'];
	$rate1 = $_POST['rate1'];
	$ctype = $_POST['ctype'];
	if(!empty($game)) {
		foreach($game as $gid=>$v) {
			empty($rate0[$gid]) && $rate0[$gid] = 0;
			empty($rate1[$gid]) && $rate1[$gid] = 0;
			empty($useup[$gid]) && $useup[$gid] = 0;
			//echo "UPDATE {$table_game} SET rate0 = '{$rate0[$gid]}', rate1 = '{$rate1[$gid]}', ctype = '{$ctype[$gid]}', useup = '{$useup[$gid]}', useup_ctype='{$useup_ctype[$gid]}' WHERE gid = {$gid}<br/>";
			//更新数据库
			DB::query("UPDATE {$table_game} SET rate0 = '{$rate0[$gid]}', rate1 = '{$rate1[$gid]}', ctype = '{$ctype[$gid]}', useup = '{$useup[$gid]}', useup_ctype='{$useup_ctype[$gid]}'  WHERE gid = {$gid}");
			//更新配置文件
			$query = DB::query("SELECT * FROM {$table_game} WHERE 1");
			while($result = DB::fetch($query)) {
				$typeName = $conf_game[$result['gid']]['typeName'];
				$conf_game[$result['gid']] = $result;
				$conf_game[$result['gid']]['typeName'] =$typeName;
			}
		}
		$variables = array('key'=>'conf_game','value'=>$conf_game);
		updateSetting($variables, H_P.'/data/game_config.php');
	}
	echo 'operate_success';
	exit;
}
?>