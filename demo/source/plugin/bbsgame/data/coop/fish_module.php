<?php
$account = 255;	// 接入渠道号
$key = "EB84A3E9-095B-4678-A49A-F56582B8770E";	// 密钥
$gameId['mj'] = 23081;		// 达人麻将游戏号
$gameId['pw'] = 25011;		// 德州扑克游戏号
$gameId['wgh'] = 25010;		// 天天斗地主游戏号
$gameId['fish'] = 23091;		// 愤怒的渔夫游戏号
$time = date("YmdHis");	// 注册需要的时间戳格式
		
$number = $_GET['coopid'];
$g_id = $gameId[$number];		// 选择的游戏
$sign = md5(strtoupper($account."".$g_id."".$time."".$key));	// 加密字段
// 颁发Ticket服务
$TickerUrl = "http://wgh.lianzhong.com/Services/RequestTicket.ashx?ChannelID={$account}&GameID={$g_id}&Timestamp={$time}&Sign={$sign}";
$xml = file_get_contents($TickerUrl);
$xml_object = simplexml_load_string($xml);
$stats = (int)$xml_object->Stats;		//	获得状态
if ($stats === 0){
	$TiketKey = (string)$xml_object->Data;	// 获得游戏密钥
		
	$Ntime = date("YmdHis");	// 访问游戏需要的时间戳
	$num = rand(1,10000);
	$UserID = $pid."_".$_G[uid];
	$Nsign = md5(strtoupper($account."".$g_id."".$UserID."0".$Ntime."".$key));	// 加密字段
	// Iframe 嵌套用的游戏 URL
	$gameUrl = "http://wgh.lianzhong.com/Services/RequestGame.ashx?ChannelID={$account}&GameID={$g_id}&UserID={$UserID}&CMStatus=0&Timestamp={$Ntime}&Ticket={$TiketKey}&Version=1&Charset=UTF8&Sign={$Nsign}";
}

$outDivConfig = array("width"=>"960px","height"=>"710px");
switch ($_GET['coopid']) {
	case 'fish' :
		$outDivConfig["height"] = "930px";
		$iFrameConfig = array("width"=>'960px',"height"=>'930px',"top"=>'-75px',"src"=>$gameUrl);
		break;
	default:
		$iFrameConfig = array("width"=>'960px',"height"=>'700px');
		break;
}
?>