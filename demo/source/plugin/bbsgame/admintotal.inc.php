<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
 
$identifier = 'bbsgame';
$operation = $_G['gp_operation'];

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

/*****************************************************/
$page = $_GET['page'];
$date = $_GET['date'];
$url = "admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&page=";
if(!empty($date)) {

	if(!in_array($date,array("day","week","month","custom"))) $date = "day";
	if($date == 'custom') {
		$datestart = $_POST['datestart'];
		$dateend = $_POST['dateend'];
		if(!preg_match("/[0-9]{1,4}-[0-9]{1,2}-[0-9]{1,2}/",$datestart)|| !preg_match("/[0-9]{1,4}-[0-9]{1,2}-[0-9]{1,2}/",$dateend)) {
			echo '自定义查询日期不符合规则，请重试！';
		}
		$sqlStatment = "addTime > ".strtotime($datestart)." AND addTime < ".strtotime($dateend);
		$url = "admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=custom&datestart=".$datestart."&dateend=".$dateend."&page=";
	}
	else if($date == 'day') {
		$sqlStatment = "addTime > ".strtotime(date("Y-m-d",time()));
		$url = "admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=day&page=";
	}
	else if($date == 'week') {
		$sqlStatment = "addTime > ".strtotime(date("Y-m-d",time()-604800));
		$url = "admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=week&page=";
	}
	else if($date == 'month') {
		$sqlStatment = "addTime > ".strtotime(date("Y-m-d",time()-2592000));
		$url = "admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=month&page=";
	}
}
else {
	$sqlStatment = "addTime > 0";  //select all
}

$currentPage = $page;
!$page && $page = 1;
$pageSize = 10;
$start =($page-1)*$pageSize;

$statisticsData = getStatisticsData($start, $pageSize, $sqlStatment);

$query = DB::query("select count(gid) as count from $table_game where 1");
$res = DB::fetch($query);
@include_once(H_P."/require/pageCss.class.php");
$scope = 3;

$pageCss = new PageCss($pageSize, $scope, $url, $style);
$pageCss->setCurrentPage($page);
$pageCss->setCounts($res['count']);
$pageCss = $pageCss->getHTML();

$todayStatistics = getStatisticsDatawithToday(); //当天统计综合
//print_r($statisticsData);
/************************************************/
showtableheader('数据统计');
$table_str = " <form action=\"$basename\"  method=\"post\" name=\"FORM\"><table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
		<td>数据时间</td>
		<td><a href=\"admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=day&page=$page\">查看今天</a></td>
		<td><a href=\"admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=week&page=$page\">查看本周</a></td>
		<td><a href=\"admin.php?action=plugins&operation=config&do=".$_GET['do']."&identifier=bbsgame&pmod=admintotal&date=month&page=$page\">查看本月</a></td>
			<input type=\"hidden\" name=\"date\" value=\"custom\" />
			<td><!--自定义时间：
				<input type=\"text\" name=\"datestart\" value=\"".$datestart."\" />&nbsp;到&nbsp;
				<input type=\"text\" name=\"dateend\" value=\"".$dateend."\" />
				<input type=\"submit\" value=\"查询\" /-->
			</td></tr><tr>
		<td>日统计量</td>
		<td colspan=\"2\">
			<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\">
					<tr><td>日PV总量:&nbsp;&nbsp;{$todayStatistics[ip]}</td>
					<td>日消耗积分总量:&nbsp;&nbsp;{$todayStatistics[usedAffect]}</td>
					<td>日奖励积分总量:&nbsp;&nbsp;{$todayStatistics[addAffect]}</td></tr>
			</table></td></tr>
	<tr><td>已安装游戏</td><td colspan=\"10\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr><td><b>游戏名称</b></td><td><b>PV统计</b></td><td><b>消耗积分</b></td><td><b>奖励积分</b></td></tr>";
foreach($statisticsData as $game) {
		$table_str .= "<tr><td>{$game['name']}</td><td>{$game['sum_ip']}</td><td>{$game['affectSub']}</td><td>{$game['affectAdd']}</td></tr>";
}
$table_str .= "</table></td></tr></table></form><br/><br/><br/>{$pageCss}";

showtablerow('', array('width="100%"'), array($table_str));
showtablefooter();
?>