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

require_once H_P.'/data/basic_config.php';

if( !empty($_POST['submit']) ) {
	
	$pkgame_debug = $_POST['pkgame_debug'];
	$pkgame_isdownload = $_POST['pkgame_isdownload'];
	$pkgame_name = $_POST['pkgame_name'];
	$pkgame_topscore_cachetime = $_POST['pkgame_topscore_cachetime'];
	$pkgame_recmd_cachetime = $_POST['pkgame_recmd_cachetime'];
	$pkgame_pk_cachetime = $_POST['pkgame_pk_cachetime'];
	$pk_min_score = $_POST['pk_min_score'];
	$pk_max_score = $_POST['pk_max_score'];
	$pkgame_challenge_case_ctype = $_POST['pkgame_challenge_case_ctype'];
	$pkgame_challenge_case_num = $_POST['pkgame_challenge_case_num'];
	$pkgame_challenge_algorithm_personal_pkerPercent = $_POST['pkgame_challenge_algorithm_personal_pkerPercent'];
	$challenge_max_score = $_POST['challenge_max_score'];
	$challenge_min_score = $_POST['challenge_min_score'];
	$pkgame_qd_reward_min = $_POST['challenge_min_score'];
	$pkgame_qd_reward_max = $_POST['pkgame_qd_reward_max'];
	$pkgame_qd_reward_ctype = $_POST['pkgame_qd_reward_ctype'];
	$pkgame_qd_limit_number = $_POST['pkgame_qd_limit_number'];
	$pkgame_qd_limit_interval = $_POST['pkgame_qd_limit_interval'];
		
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
	HEADER("LOCATION: admin.php?action=plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminbase");
	exit;
}
if($_GET['act'] == 'update') {
	
	if (!chmod(H_P.'/data/bbsgame_config.php', 0777))
		showtablerow('', array('width="100%"'), array("$strnotice"));
	
	$pluginType = array("PW"=>1,"DZ"=>2);
	$str = HACK_UPDATE_URL."?currentVerNum=".$pkgame_version."&pluginType=$pluginType[DZ]&lang={$db_charset}&site=".$_SERVER['HTTP_HOST'];
	$data = ng_get_contents($str);

	$update = json_decode($data, true);

	if ($update['files'])
	{
		$files = explode('~ng~', $update['files']);
		$updateFlag = 1;
		foreach ($files as $k=>$src)
		{
			$dst = H_P.'/'.$src;
			$src = S_HOST.'/games/'.$update['rulbase'].'/'.$src;
				
			if (file_exists($dst) && !chmod($dst, 0777)) 
				showtablerow('', array('width="100%"'), array("<font color=red>请将 $dst 属性设为可写</font>"));
				
			$dir = dirname($dst);
			if (!file_exists($dir))
			{	
				if (!mkdir($dir) || !chmod($dir, 0777))
					showtablerow('', array('width="100%"'), array("请创建$dir 并设置其权限为可写"));
			}
					
			if (!writeover($dst,ng_get_contents($src), 'wb+'))
			{
				$updateFlag = 0;
				break;
			}				
		}
		if(!$updateFlag) 
			showtablerow('', array('width="100%"'), array("抱歉，升级失败，请联系相关技术或业务人员"));
		$update_version = $update['ver'];
	
		$data ="\$ifopen=\"$ifopen\";\r\n";
		$data .="\$name=\"$name\";\r\n";
		$data .="\$phone=\"$phone\";\r\n";
		$data .="\$siteurl=\"$siteurl\";\r\n";
		$data .="\$sitename=\"$sitename\";\r\n";
		$data .="\$qq=\"$qq\";\r\n";
		$data .="\$mail=\"$mail\";\r\n";
		$data .="\$init=\"$init\";\r\n";
		$data .="\$ng_name=\"$ng_name\";\r\n";
		$data .="\$db_bbsname=\"{$db_bbsname}\";\r\n";
		$data .="\$pkgame_version=\"$update_version\";\r\n";
		$data .="\$pid=\"$pid\";\r\n";

		writeover(H_P.'/data/bbsgame_config.php',"<?php\r\n".$data."?>");
		showtablerow('', array('width="100%"'), array("恭喜，升级完成"));
	}
	else {
		showtablerow('', array('width="100%"'), array("抱歉，不需要升级."));
	}
	exit;
}

!empty($pkgame_debug) && $ifdebug = $pkgame_debug == 'on'?'checked':'';
!empty($pkgame_isdownload) && $ifdownload = $pkgame_isdownload == 'on'?'checked':'';
!empty($pkgame_active_endtime) && $pkgame_active_endtime = date("Y-m-d", $pkgame_active_endtime);
!empty($pkgame_active_endtime) && $pkgame_active_starttime = date("Y-m-d", $pkgame_active_starttime);
$basic = 'current';
$tabStr = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>版本更新</td><td><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">版本号：</td><td>{$pkgame_version}&nbsp;&nbsp;&nbsp;&nbsp;";
$tabStr .= "<a href='admin.php?action=plugins&operation=config&do=13&identifier=bbsgame&pmod=adminbase&act=update'>升级</a>";
$tabStr .= "</td></tr></table></td></tr>";
$tabStr .= "<tr><td>插件信息管理</td><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">名称：</td><td><input class=\"input\" type=\"text\" name=\"pkgame_name\" value=\"{$pkgame_name}\" size=\"14\" /></td></tr>";
$tabStr .= "<tr><td width=\"10%\">下载游戏到本站：</td><td><input class=\"input\" type=\"checkbox\" name=\"pkgame_isdownload\" {$ifdownload}/> &nbsp;&nbsp;&nbsp;说明：开启后会下载游戏到站点</td></tr></table></td></tr>";
$tabStr .= "<tr><td>签到管理</td><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">奖励：</td><td><input type=\"text\" name=\"pkgame_qd_reward_min\" value=\"{$pkgame_qd_reward_min}\" size=4 />	- <input type=\"text\" name=\"pkgame_qd_reward_max\" value=\"{$pkgame_qd_reward_max}\" size=4 />";
foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if((string)trim($pkgame_qd_reward_ctype) === (string)trim($ext)) {
				$ctypeTitle = $value['title'];
				
				break;
			}
		}
$tabStr .= "<select name='pkgame_qd_reward_ctype'><option value='{$pkgame_qd_reward_ctype}'>{$ctypeTitle}</option>";

foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			$tabStr .= "<option value=\"{$ext}\">{$value['title']}</option>";
		}
$tabStr .= "</select></td></tr>";
$tabStr .= "<tr><td width=\"10%\">限制：</td><td colspan='2'><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">每日最多签到次数：<input type=\"text\" value='{$pkgame_qd_limit_number}' name=\"pkgame_qd_limit_number\" size=4/></td></tr>";
$tabStr .= "<tr><td width=\"10%\">每次间隔时长：<input type=\"text\" value='{$pkgame_qd_limit_interval}' name=\"pkgame_qd_limit_interval\" size=4/>小时   *推荐≥1</td></tr></table></td></tr></table></td></tr>";
$tabStr .= "<tr><td>个人 PK 挑战管理设置</td><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">允许的积分类型：</td><td>";

foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			if($pk_ctype[$ext] == $value['title']) $value1 = "checked"; else $value1 = "";
			$tabStr .= "<input type=\"checkbox\" name=\"pk_ctype[{$ext}]\" value=\"{$value['title']}\" {$value1}>{$value['title']}";
		}
$tabStr .= "</td></tr><tr><td width=\"10%\">允许的积分大小：</td><td><input class=\"input\" type=\"text\" name=\"pk_min_score\" value=\"{$pk_min_score}\" size=\"8\" /> 至 <input class=\"input\" type=\"text\" name=\"pk_max_score\" value=\"{$pk_max_score}\" size=\"8\" /> 之间</td></tr></table></td></tr>";
$tabStr .= "<tr><td>个人擂台管理</td><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td width=\"10%\">积分奖励规则：</td><td><input type=\"text\" name=\"pkgame_challenge_algorithm_personal_pkerPercent\" value=\"{$pkgame_challenge_algorithm_personal_pkerPercent}\" size=\"14\" />*个人擂台的奖励规则。示例5:3:2将按比例将奖金奖励给前三名</td></tr>";

$tabStr .= "<tr><td width=\"10%\">积分类型设置：</td><td><select name='pkgame_challenge_case_ctype'><option value='{$pkgame_challenge_case_ctype}'>{$ctypeTitle}</option>";

foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;
			getuserprofile($ext);
			$tabStr .= "<option value=\"{$ext}\">{$value['title']}</option>";
		}

$tabStr .= "</select></td></tr><tr><td width=\"10%\">开擂消耗：</td><td><input type=\"text\" name=\"pkgame_challenge_case_num\" value=\"{$pkgame_challenge_case_num}\" size=\"14\" />*擂台主将被扣除</td></tr><tr><td width=\"10%\">允许的积分大小：</td><td><input type=\"text\" name=\"challenge_min_score\" value=\"{$challenge_min_score}\" size=\"8\" /> 至 <input type=\"text\" name=\"challenge_max_score\" value=\"{$challenge_max_score}\" size=\"8\" /> 之间</td></tr></table></td></tr>";
$tabStr .= "<tr><td>缓存管理</td><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><th >缓存项</th><th>时间设置（单位：s）</th></tr><tr><td width=\"10%\">积分排行前十名缓存：</td><td><input type=\"text\" name=\"pkgame_topscore_cachetime\" value=\"{$pkgame_topscore_cachetime}\" /></td></tr><tr><td width=\"10%\">总人气排行缓存：</td><td><input type=\"text\" name=\"pkgame_recmd_cachetime\" value=\"{$pkgame_recmd_cachetime}\"/></td></tr><tr><td width=\"10%\">pk接口缓存：</td><td><input type=\"text\" name=\"pkgame_pk_cachetime\" value=\"{$pkgame_pk_cachetime}\"/></td></tr></table></td></tr></table>";
showformheader("plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminbase");
showtablerow('', array('width="100%"'), array($tabStr));
showsubmit('submit','submit');
showtablefooter();
?>