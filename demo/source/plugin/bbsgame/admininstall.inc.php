<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
 
$identifier = 'bbsgame';
$operation = $_G['gp_operation'];
/**********************************/
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

showtableheader('<a href="admin.php?action=plugins&operation=config&do=14&identifier=bbsgame&pmod=admininstall">所有游戏</a>');

/************************************************/
if($_GET['op'] =='install') {

	set_time_limit(300);
	$id = $_GET['id'];
	$query = DB::query("SELECT g.*, t.name as typeName FROM $table_game g  LEFT JOIN $table_type  t ON t.`id`=g.`type` WHERE g.`gid`=$id LIMIT 1");
	$gameInfo = DB::fetch($query);

	//print_r($gameInfo);exit;
	if(function_exists('file_get_contents')){
			
		$oput = file_get_contents(GAME_INFO."?act=2&itemid=".$id);
			
		$xml = simplexml_load_string($oput);
		$item = $xml->item;

		if(count($item)) {
			$gameInfo['gid'] = (int)$item->id;
			$gameInfo['name'] = addslashes((string)unicode_decode($item->name, $ng_charset));
			$gameInfo['describe'] = addslashes((string)unicode_decode($item->detail, $ng_charset));
			$gameInfo['swf'] = (string)$item->swf;
			$gameInfo['url'] = (string)$item->url;
			$gameInfo['img'] = (string)$item->img;
			$gameInfo['shellUrl'] = (string)$item->shell;
			$gameInfo['typeName'] = addslashes((string)unicode_decode($item->type, $ng_charset));
			$gameInfo['operation'] = addslashes((string)unicode_decode($item->operation, $ng_charset));
			$gameInfo['objective'] = addslashes((string)unicode_decode($item->objective, $ng_charset));
			$gameInfo['updatetime'] = (string)unicode_decode($item->updatetime, $ng_charset);

			if($gameInfo['installed'] === '0') {// 如果记录存在则更新安装状态
				$gameInfo['installed'] = 1;
				$query = DB::query("UPDATE {$table_game} SET `installed`=1 WHERE `gid`={$gameInfo['gid']}");
			}
			else{ // 记录不存在则安装
				// 获取游戏类别的序号
				$gameInfo['typeId'] = 1;			
				if(!empty($gameInfo['typeName'])) {
					$query = DB::query("SELECT `id` FROM {$table_type} WHERE name = '{$gameInfo['typeName']}'");
					$res = DB::fetch($query);
					if($res['id']) { 
						$gameInfo['typeId'] = $res['id'];
					} else {
						$query = DB::query("INSERT INTO {$table_type} (`name`) VALUES('{$gameInfo['typeName']}')");
						$query = DB::query("SELECT MAX(id) AS id FROM {$table_type} limit 1");
						$res = DB::fetch($query);
						$gameInfo['typeId'] = $res['id'];
					}
				}

				// 获取壳的序号
				$query = DB::query("SELECT `id` FROM {$table_shell} WHERE url = '{$gameInfo['shellUrl']}'");
				$res = DB::fetch($query);
				if($res['id']) { 
					$gameInfo['shellId'] = $res['id'];
				} else {
					$query = DB::query("INSERT INTO {$table_shell} (`url`) VALUES('{$gameInfo['shellUrl']}')");
					$query = DB::query("SELECT MAX(id) AS id FROM {$table_shell} limit 1");
					$res = DB::fetch($query);
					$gameInfo['shellId'] = $res['id'];
				}

				$sql = "INSERT INTO ng_game (`gid`, `name`, `describe`,`objective`, `operation`, `swf`, `url`,`img`,`type`,`shellid`,`installed`) 
					VALUES({$gameInfo[gid]}, '{$gameInfo[name]}', '{$gameInfo[describe]}', '{$gameInfo[objective]}', '{$gameInfo[operation]}',
					'{$gameInfo[swf]}', '{$gameInfo[url]}', '{$gameInfo[img]}', {$gameInfo[typeId]}, {$gameInfo[shellId]},1)";
				$query = DB::query($sql);
				$gameInfo['type'] = $gameInfo['typeId'];
				$gameInfo['installed'] = 1;
				$gameInfo['useup'] = 0;
				$gameInfo['ctype'] = 'money';
				$gameInfo['rate0'] = 1000;
				$gameInfo['rate1'] = 10;
			}
		}
	}
	
	@require_once(H_P.'/data/game_config.php');
	$conf_game[$id] = $gameInfo;
	$variables = array('key'=>'conf_game','value'=>$conf_game);
	
	updateSetting($variables, H_P.'/data/game_config.php');
}
else if($_GET['op']=='uninstall') {

		$id = $_GET['id'];
		$query = DB::query("UPDATE {$table_game} SET `installed`=0 WHERE `gid`={$id}");
		unset($conf_game[$id]);
		$variables = array('key'=>'conf_game','value'=>$conf_game);
		
		updateSetting($variables, H_P.'/data/game_config.php');
}
/************************************************/
$page = $_GET['page'];
!$page && $page = 1;
$pageSize = 14;
$scope = 3;
if($_GET['show'] == 'installed') {
	$url = "admin.php?action=plugins&operation=config&do=14&identifier=bbsgame&pmod=admininstalled&page=";
	$table_str = "<table width=\"100%\"><tr>";
	foreach($conf_game as $k=>$g) {
		$games[$k] = $g;
		$games[$k]['img']  = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $g['img']);
		$games[$k]['type'] = $g['typeName'];
		$table_str .= "<td align=\"center\">{$games[$k]['name']}({$games[$k]['type']})<br />
						<img src=\"{$games[$k]['img']}\">
					   </td>";
		if(($i+1) % 7 == 0) $table_str .= "</tr><tr>";
	}
}
else {
	
	$url = "admin.php?action=plugins&operation=config&do=14&identifier=bbsgame&pmod=admininstall&page=";
	
	if(function_exists('file_get_contents')){
		$oput = file_get_contents(GAME_INFO."?act=1&p=".$page."&s=".$pageSize);
	}
	$xml = simplexml_load_string($oput);
	if(empty($xml->items)) {
		echo "暂无数据提供,请刷新后再试。";
		exit;
	}
	$count = $xml->items->attributes()->total;
	$items = $xml->items;
	$itemscount = count($items->item);

	@include_once(H_P."/require/pageCss.class.php");
	$pageCss = new PageCss($pageSize, $scope, $url);

	$pageCss->setCounts($count);
	$pageCss->setCurrentpage($page);
	$pageCss = $pageCss->getHTML();
	$table_str = "<table width=\"100%\"><tr>";
	for($i=0; $i<$itemscount; $i++){
		$games[$i]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $items->item[$i]->img);
		$games[$i]['name'] = (string)unicode_decode($items->item[$i]->name,$ng_charset);
		$games[$i]['gid'] = (int)$items->item[$i]->id;
		$games[$i]['type'] = (string)unicode_decode($items->item[$i]->type,$ng_charset);
		$games[$i]['url'] = (string)$items->item[$i]->url;
		$games[$i]['installed'] = $conf_game[$games[$i]['gid']] ?  $conf_game[$games[$i]['gid']]['installed'] : 0;

		$install = null;
		if($games[$i]['installed']==1) 
			$install = "安装完成&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"admin.php?action=plugins&operation=config&do=14&identifier=bbsgame&pmod=admininstall&op=uninstall&id={$games[$i]['gid']}\">卸载</a>";
		else
			$install = "<a href=\"admin.php?action=plugins&operation=config&do=14&identifier=bbsgame&pmod=admininstall&op=install&id={$games[$i]['gid']}\">安装</a>&nbsp;&nbsp;&nbsp;";

		$table_str .= "<td align=\"center\">{$games[$i]['name']}({$games[$i]['type']})<br />
						<img src=\"{$games[$i]['img']}\"><br />
						{$install}
					   </td>";
		if(($i+1) % 7 == 0) $table_str .= "</tr><tr>";
	}
	$table_str .= "</tr></table>";
}
/**********************************/
$page_str = "<table width=\"100%\"><tr>";
$page_str .= "<td align=\"center\">{$pageCss}</td>";
$page_str .= "</tr></table>";
showtablerow('', array('width="100%"'), array($table_str));
showtablerow('', array('width="100%"'), array($page_str));

showtablefooter();
?>