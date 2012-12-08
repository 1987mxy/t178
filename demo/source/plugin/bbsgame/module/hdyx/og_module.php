<?php
$gametype = 2;	// ½ÓÈëÇþµÀºÅ
$serverid = 1;
$type = "ZhouXin";
$key = 'ZhouXin^c@X1$Cx3^Ro*8Q(pG%7qU)9w>K4<Mb';	// ÃÜÔ¿
preg_match("/\/.*\//", $_SERVER['REQUEST_URI'], $matches);
$backurl= urlencode('http://'.$_SERVER[HTTP_HOST].$matches[0]."/plugin.php?id=bbsgame&amp;");

$uid = $pid."0990".$_G[uid];
$user = UrlEncode($_G['username']);
$time = date("YmdHis");
$str = $uid."".$serverid."".$time."".$key;
$sign = UrlEncode(md5($uid."".$serverid."".$time."".$key));
	
$gameUrl =  "http://s1og.97sng.com/ZhouXin/ZhouXinLogin/ZhouXinLogin.aspx?uid={$uid}&uname={$user}&gametype={$gametype}&serverid={$serverid}&type={$type}&time={$time}&sign={$sign}&manhood=0&backurl={$backurl}";
?>