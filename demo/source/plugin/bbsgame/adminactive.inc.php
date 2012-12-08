<?php
//ini_set("display_errors", 1);

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
 
$identifier = 'bbsgame';
$operation = $_G['gp_operation'];
/**********************************/
define('R_P', dirname(__FILE__));
define('H_P', R_P);

@include_once(H_P.'/data/api_config.php');
@include_once(H_P.'/data/bbsgame_config.php');
@include_once H_P.'/data/jljf_config.php';
@include_once(H_P.'/require/functions.php');
@include_once(H_P.'/require/game.class.php');
@include_once(H_P.'/require/security.php');

// $db_hackdb;
$table_game = 'ng_game';
$table_ip = 'ng_game_ip';
$table_credit = 'ng_game_credit';
$table_type = 'ng_game_type';
$table_shell = "ng_game_shell";

define('GAME_INFO',API_HOST.'/apps/clubapi.ashx'); //人气接口,1：游戏列表（按添加时间倒序）
define('GAME_INFO_V2', S_HOST.'/games/svs/getgamescoreinit.action');

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

showtableheader('基础设置');
/******************* 中间内容体开始 *******************************/
require_once H_P.'/data/basic_config.php';

if( !empty($_POST['submit']) ) {
	$starttime = mktime(0, 0, 0, $_POST['smonth'], $_POST['sday'], $_POST['syear']);
	$endtime = mktime(0, 0, 0, $_POST['emonth'], $_POST['eday'], $_POST['eyear']);
	$pkgame_active_ifopen = $_POST['pkgame_active_ifopen'];
	$pkgame_active_name = $_POST['pkgame_active_name'];
	$pkgame_active_gameid = $_POST['pkgame_active_gameid'];
	$pkgame_active_content = $_POST['pkgame_active_content'];
	$pkgame_active_starttime = $starttime;
	$pkgame_active_endtime = $endtime;
	$pkgame_active_case = $_POST['pkgame_active_case'];
	$pkgame_active_case_ctype = $_POST['pkgame_active_case_ctype'];
	$pkgame_challenge_algorithm_sitenal_pkerPercent = $_POST['pkgame_challenge_algorithm_sitenal_pkerPercent'];
	$pkgame_active_reward_price = $_POST['pkgame_active_reward_price'];
	$pkgame_active_reward_ctype = $_POST['pkgame_active_reward_ctype'];

	@include_once H_P.'/require/challenge.class.php';
	$data = '';
	 
	if ($pkgame_active_gameid)
	{
		try{
			$challenge = new challenge();
			
			$strSQL = "SELECT pk.* FROM ng_game_pk AS pk WHERE pk.gid = {$pkgame_active_gameid} AND endtime > {$pkgame_active_starttime} AND type = ".challenge::PK_SITENAL;
			$query = DB::query($strSQL);
			$result = DB::fetch($query);
			
			if( !$result['gid'] ) {
				$challenge->endtime = S::sqlEscape($pkgame_active_endtime);
				$challenge->ctime = S::sqlEscape($pkgame_active_starttime);
				$challenge->starttime = S::sqlEscape($pkgame_active_starttime);
				$challenge->case['num'] =  $pkgame_active_case;
				$challenge->case['ctype'] = S::sqlEscape($pkgame_active_case_ctype);
				$challenge->desc = S::sqlEscape("站内擂台");
				$challenge->pkname = S::sqlEscape('站内擂台赛');
				$challenge->status = challenge::PK_STATUS_ING;
				$challenge->price['num'] = S::sqlEscape($pkgame_active_reward_price);
				$challenge->price['ctype'] = S::sqlEscape($pkgame_active_reward_ctype);
				$challenge->price['ext'] = S::sqlEscape('');
	
				$result = getGameInfo($pkgame_active_gameid);
				$challenge->gamename = $result['name'];
	
				$challenge->add(array("uid"=>1,"username"=>"'admin'"), $pkgame_active_gameid, challenge::PK_SITENAL);
				unset($challenge);
			}
			else{
				unset($challenge);
				echo '抱歉，活动已存在。请换别的游戏。';
			}
					
		}
		catch (challengeException $e) {
			$e->log();
			echo '抱歉，发生错误了。';
		}
	}

	$data .="\$pkgame_active_ifopen=\"$pkgame_active_ifopen\";\r\n";
	$data .="\$pkgame_active_name=\"$pkgame_active_name\";\r\n";
	$data .="\$pkgame_active_content=\"$pkgame_active_content\";\r\n";
	$data .="\$pkgame_active_gameid=\"$pkgame_active_gameid\";\r\n";
	$data .="\$pkgame_active_starttime=\"$pkgame_active_starttime\";\r\n";
	$data .="\$pkgame_active_endtime=\"$pkgame_active_endtime\";\r\n";
	$data .="\$pkgame_active_case=\"$pkgame_active_case\";\r\n";
	$data .="\$pkgame_active_case_ctype=\"$pkgame_active_case_ctype\";\r\n";
	$data .="\$pkgame_active_reward_price=\"$pkgame_active_reward_price\";\r\n";
	$data .="\$pkgame_active_reward_ctype=\"$pkgame_active_reward_ctype\";\r\n";
	
	$data .="\$pkgame_debug=\"$pkgame_debug\";\r\n";
	$data .="\$pkgame_isdownload=\"$pkgame_isdownload\";\r\n";
	$data .="\$pkgame_name=\"$pkgame_name\";\r\n";
	$data .="\$pkgame_topscore_cachetime=\"$pkgame_topscore_cachetime\";\r\n";
	$data .="\$pkgame_recmd_cachetime=\"$pkgame_recmd_cachetime\";\r\n";
	$data .="\$pkgame_pk_cachetime=\"$pkgame_pk_cachetime\";\r\n";
		
	$data .="\$pkgame_qd_reward_min=\"$pkgame_qd_reward_min\";\r\n";
	$data .="\$pkgame_qd_reward_max=\"$pkgame_qd_reward_max\";\r\n";
	$data .="\$pkgame_qd_reward_ctype=\"$pkgame_qd_reward_ctype\";\r\n";
	$data .="\$pkgame_qd_limit_number=\"$pkgame_qd_limit_number\";\r\n";
	$data .="\$pkgame_qd_limit_interval=\"$pkgame_qd_limit_interval\";\r\n";
		
	$data .= "\$pk_ctype = array(\r\n";
	foreach($_POST['pk_ctype'] as $k=>$v) {
		$data .= "\t'{$k}' => '$v',\r\n";
	}
	$data .= ");\r\n";
	$data .="\$pk_min_score=\"$pk_min_score\";\r\n";
	$data .="\$pk_max_score=\"$pk_max_score\";\r\n";
		
	$data .="\$challenge_min_score=\"$challenge_min_score\";\r\n";
	$data .="\$challenge_max_score=\"$challenge_max_score\";\r\n";
	$data .="\$pkgame_challenge_case_ctype=\"$pkgame_challenge_case_ctype\";\r\n";
	$data .="\$pkgame_challenge_case_num=\"$pkgame_challenge_case_num\";\r\n";
	$data .="\$pkgame_challenge_algorithm_personal_pkerPercent=\"$pkgame_challenge_algorithm_personal_pkerPercent\";\r\n";
	$data .="\$pkgame_challenge_algorithm_sitenal_pkerPercent=\"$pkgame_challenge_algorithm_sitenal_pkerPercent\";\r\n";
		
	writeover(H_P.'/data/basic_config.php',"<?php\r\n".$data."?>", "w");
	HEADER("LOCATION: admin.php?action=plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminactive");
	exit;
}

!empty($pkgame_active_ifopen) && $pkgame_active_ifopen = $pkgame_active_ifopen == 'on'?'checked':'';
!empty($pkgame_active_endtime) && $pkgame_active_endtime = date("Y-m-d", $pkgame_active_endtime);
!empty($pkgame_active_endtime) && $pkgame_active_starttime = date("Y-m-d", $pkgame_active_starttime);
$basic = 'current';

$tabStr = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>站内活动(站内擂台赛)管理</td><tdcolspan=\"2\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">开启活动：</td><td><input type=\"checkbox\" name=\"pkgame_active_ifopen\" {$pkgame_active_ifopen} size=\"14\" /></td></tr><tr><td width=\"10%\">活动名称：</td><td><input type=\"text\" name=\"pkgame_active_name\" value=\"{$pkgame_active_name}\" size=\"14\" /></td></tr><tr><td width=\"10%\">游戏ID：</td><td><input type=\"text\" name=\"pkgame_active_gameid\" value=\"{$pkgame_active_gameid}\" size=\"14\" /></td></tr><tr><td width=\"10%\">积分消耗：</td><td>数值：<input type=\"text\" name=\"pkgame_active_case\" value=\"{$pkgame_active_case}\" size=\"14\" />单位：<select name='pkgame_active_case_ctype'>";

foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			$tabStr .= "<option value=\"{$ext}\">{$value['title']}</option>";
		}
$tabStr .= "</select></td></tr>";

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

$tabStr .= "<tr><td width=\"10%\">积分奖励规则：</td><td><input type=\"text\" name=\"pkgame_challenge_algorithm_sitenal_pkerPercent\" value=\"{$pkgame_challenge_algorithm_sitenal_pkerPercent}\" size=\"14\" />*所有站内擂台的奖励规则。示例5:3:2将按比例将奖金奖励给前三名</td></tr><tr><td width=\"10%\">非积分奖励：</td><td>数值：<input type=\"text\" name=\"pkgame_active_reward_price\" value=\"{$pkgame_active_reward_price}\" size=\"14\" />单位：<input type=\"text\" name=\"pkgame_active_reward_ctype\" value=\"{$pkgame_active_reward_ctype}\" size=\"14\" />*现金或其他奖励，由站长自定义</td></tr><tr><td width=\"10%\">活动内容：</td><td><textarea name=\"pkgame_active_content\" cols=\"50\" rows=\"10\">{$pkgame_active_content}</textarea><div>可只用标签：&lt;br&gt;换行 &lt;h3&gt;&lt;/h3&gt;标题 &lt;b&gt;&lt;/b&gt;重点标注 </div></td></tr><tr><td width=\"10%\">活动时间：</td><td>从 {$selstart} 到 {$selend}</td></tr></table></td></tr></table>";

/******************* 中间内容体结束 *******************************/

showformheader("plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminactive");
showtablerow('', array('width="100%"'), array($tabStr));
showsubmit('submit','submit');
showtablefooter();
?>