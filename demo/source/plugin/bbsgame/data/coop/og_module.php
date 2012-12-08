<?php
$gametype = 2;	// 接入渠道号
$serverid = 1;
$type = "ZhouXin";
$key = 'ZhouXin^c@X1$Cx3^Ro*8Q(pG%7qU)9w>K4<Mb';	// 密钥
preg_match("/\/.*\//", $_SERVER['REQUEST_URI'], $matches);
$backurl= urlencode('http://'.$_SERVER[HTTP_HOST].$matches[0]."/plugin.php?id=bbsgame&amp;");

$pnum = 1000000;
$pflag = $pnum + $pid; // 生成规则是 1000000+$pid 与用户序号合
$uid = $pflag.$_G[uid];
$user = UrlEncode($_G['username']);
$time = date("YmdHis");
$str = $uid."".$serverid."".$time."".$key;
$sign = UrlEncode(md5($uid."".$serverid."".$time."".$key));
if($db_charset == 'gbk') {
	$userName = urlencode(mb_convert_encoding($_G['username'], 'UTF-8', 'GB2312'));
}

$payUrl = urlencode("gamesvs.97sng.com/ngcharge/payment/initPayinfo.action?pid={$pid}&uid={$_G['uid']}&gid=og&nickname={$userName}");

$gameUrl =  "http://s1og.97sng.com/ZhouXin/ZhouXinLogin/ZhouXinLogin.aspx?uid={$uid}&uname={$user}&gametype={$gametype}&serverid={$serverid}&type={$type}&time={$time}&sign={$sign}&manhood=0&backurl={$backurl}&payUrl={$payUrl}";

$outDivConfig = array("width"=>"960px","height"=>"710px");
switch ($_GET['coopid']) {
	case 'og':
		$iFrameConfig = array("width"=>'960px',"height"=>'700px',"src"=>$gameUrl);
		break;
	default:
		$iFrameConfig = array("width"=>'960px',"height"=>'700px');
		break;
}
?>