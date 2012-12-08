<?php
//error_reporting(E_ALL^E_NOTICE^E_WARNING);

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
include_once H_P.'/data/bbsgame_config.php';
include_once H_P.'/data/game_config.php';

// $db_hackdb;
$table_game = 'ng_game';
$table_ip = 'ng_game_ip';
$table_credit = 'ng_game_credit';
$table_type = 'ng_game_type';
$table_shell = "ng_game_shell";

define('API_HOST','http://www.97sng.com');
define('SWF_HOST','http://img.97sng.com/flash');
define('PIC_HOST','http://img.97sng.com/pic');
define('GAME_INFO',API_HOST.'/apps/clubapi.ashx');

$db_charset = 'utf-8';
if($db_charset == 'gbk') {
	$ng_charset = 'GB2312';
	$gj = 'gonggao_gbk.js';
	$cj = 'contact_gbk.js';
}
else if($db_charset == 'utf-8') {
	$ng_charset = 'UTF-8';
	$gj = 'gonggao_utf8.js';
	$cj = 'contact_utf8.js';
}

$action = $_GET['action'];
$id = $_GET['id'];

!$page && $page = 1;
$pageSize = 14;
$scope = 3;

foreach($conf_game as $k=>$g) {
	$games[$k] = $g;
	$games[$k]['img']  = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $g['img']);
	$games[$k]['type'] = $g['typeName'];
}

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

for($i=0; $i<$itemscount; $i++){
	$games[$i]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $items->item[$i]->img);
	$games[$i]['name'] = (string)unicode_decode($items->item[$i]->name,$ng_charset);
	$games[$i]['gid'] = (int)$items->item[$i]->id;
	$games[$i]['type'] = (string)unicode_decode($items->item[$i]->type,$ng_charset);
	$games[$i]['url'] = (string)$items->item[$i]->url;
	$games[$i]['installed'] = $conf_game[$games[$i]['gid']] ?  $conf_game[$games[$i]['gid']]['installed'] : 0;

	if($games[$i]['installed']!=1) {
/**********************************************************/
	set_time_limit(300);
	$id = $games[$i]['gid'];
	$query = DB::query("SELECT g.*, t.name as typeName FROM $table_game g  LEFT JOIN $table_type  t ON t.`id`=g.`type` WHERE g.`gid`=$id LIMIT 1");
	$gameInfo = DB::fetch($query);

	$gameInfo['gid'] = (int)$items->item[$i]->id;
	$gameInfo['name'] = addslashes((string)unicode_decode($items->item[$i]->name, $ng_charset));
	$gameInfo['describe'] = addslashes((string)unicode_decode($items->item[$i]->detail, $ng_charset));
	$gameInfo['swf'] = (string)$items->item[$i]->swf;
	$gameInfo['url'] = (string)$items->item[$i]->url;
	$gameInfo['img'] = (string)$items->item[$i]->img;
	$gameInfo['shellUrl'] = (string)$items->item[$i]->shell;
	$gameInfo['typeName'] = addslashes((string)unicode_decode($items->item[$i]->type, $ng_charset));
	$gameInfo['operation'] = addslashes((string)unicode_decode($items->item[$i]->operation, $ng_charset));
	$gameInfo['objective'] = addslashes((string)unicode_decode($items->item[$i]->objective, $ng_charset));
	$gameInfo['updatetime'] = (string)unicode_decode($items->item[$i]->updatetime, $ng_charset);

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

		$conf_game[$id] = $gameInfo;
		$variables = array('key'=>'conf_game','value'=>$conf_game);
	
		updateSetting($variables, H_P.'/data/game_config.php');
/*********************************************************/
		echo $games[$i]['name']."安装完成！<br />";
	}
}
?>