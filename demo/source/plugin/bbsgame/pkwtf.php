<?php
error_reporting(E_ALL & ~(E_NOTICE));
//ini_set("display_errors", 1);

define('R_P', dirname(__FILE__));
define('H_P', R_P);
$ng_path = substr(H_P, 0, (strlen(H_P)-22));
include_once($ng_path."/source/class/class_core.php");
$discuz = & discuz_core::instance();
$discuz->init();

@include_once(H_P."/data/api_config.php");
@include_once(H_P."/require/functions.php");
@include_once(H_P."/data/bbsgame_config.php");
@include_once(H_P."/data/basic_config.php");
@include_once(H_P.'/require/security.php');
date_default_timezone_set("Asia/Shanghai");

$db_charset = "utf-8";
if($db_charset == 'gbk') {
	$ng_charset = 'GB2312';
}else if($db_charset == 'utf-8') {
	$ng_charset = 'UTF-8';
}

$s = isset($_GET['s'])?trim($_GET['s']):'';
$t = isset($_GET['t'])?trim($_GET['t']):'';
$c = isset($_GET['c'])?trim($_GET['c']):'';

$cacheArray = array("pk", "xyx", "recmd" ,"topscore",'ivSet');
if( in_array($s, $cacheArray) ) {
	$pkgame_ivSet_cachetime = 360;
	if (empty($t) && empty($c)) {
		$cachefile = H_P."/data/cache/pkgame_".$s."_cache.php";
	}
	elseif (!empty($t)) {
		$cachefile = H_P."/data/cache/pkgame_".$s."_".$t."_cache.php";
	}
	elseif (!empty($c)) {
		$cachefile = H_P."/data/cache/pkgame_".$s."_".$c."_cache.php";
	}
	if ($s == 'topscore') {
		if (!isset($_GET['gameid'])) {
			exit('bad request');
		}
		else {
			$cachefile = H_P."/data/cache/pkgame_".$s."_".intval($_GET['gameid'])."_cache.php";
		}
	}
	eval("\$cachetime = \$pkgame_".$s."_cachetime;");
	$variables = "\$$s"."data";
	if( !file_exists($cachefile) || time() - filemtime($cachefile) > $cachetime )
		$needCache = true;
	else
		$needCache = false;
}
if($s =='pk') {
	if ($needCache) 
	{
		$line = ng_get_contents(API_PK_URL);
		$data = json_decode($line, true);
		$result = $data['result'];
		$result['endTime'] = strtotime($result['endTime']);
		
		//全国擂台
		@include_once H_P.'/require/challenge.class.php';
		
		try{
			$challenge = new challenge();
			if (empty($result['gameId'])) throw new BaseException("Error: result[gameId] from API is empty");

			$sql = "SELECT pk.* FROM ".challenge::$table_pk." AS pk WHERE pk.gid = $result[gameId] AND type = ".challenge::PK_NATIONAL;
			$query = DB::query($sql);
			while( $res = DB::fetch($query) ) {
				$arr[] = $res;
			}
			if (!$arr[0]['gid']) {
				$challenge->endtime = S::sqlEscape($result['endTime']);
				$challenge->case['num'] =  0;
				$challenge->case['ctype'] = S::sqlEscape($pkgame_challenge_case_ctype);
				$challenge->desc = S::sqlEscape("全国擂台");
				$challenge->pkname = S::sqlEscape('');
				$challenge->status = challenge::PK_STATUS_ING;
				$challenge->ctime = S::sqlEscape(time());
				
				if($db_charset == 'gbk') $result['gameName'] = mb_convert_encoding($result['gameName'], "GB2312", "UTF-8");
				$challenge->gamename = $result['gameName'];
				
				$vars = get_object_vars($challenge);
				foreach($vars as $name=>$value) {
					if (empty($value)) {
						$challenge->$name = "''";
					}
				}
				
				$challenge->add(array("uid"=>1,"username"=>"'admin'"), $result['gameId'], challenge::PK_NATIONAL);
			}
			unset($arr);
			
		}
		catch (challengeException $e) {
			$e->log();
		}
		catch (BaseException $e) {
			$e->log();
		}
		$cachedata = "<?php\n\r";
		$cachedata .= "return ".var_export($result, true);
		$cachedata .= "\n\r?>";
		!empty($result) && writeover($cachefile, $cachedata);
		$result['topgameName'] = $result['gameName'];
		if($db_charset == 'gbk') {			
			$result['gameName'] = mb_convert_encoding($result['gameName'], "UTF-8", "GB2312");
		}		
	}
	else{
		$result = @include_once($cachefile);
		$result['topgameName'] = $result['gameName'];
		if($db_charset == 'gbk') {
			$result['gameName'] = mb_convert_encoding($result['gameName'], "UTF-8", "GB2312");
		}
	}
	
	echo json_encode( $result);
	exit;
}
elseif($s == 'pkranking'){
	$line = ng_get_contents(API_PKRANKING_URL);
	$data = json_decode($line,true);
	foreach($data['result'] as $k => $v) {
		$data['result'][$k]['awdTime'] = date("Y-m-d", strtotime($v['awdTime']));
	}
	echo json_encode($data['result']);
	exit;
}
elseif($s == 'recmd') {
	if ($needCache) {
		$top = isset($_GET['t'])?intval($_GET['t']):10;
		$URL = API_HOST.'/apps/clubapi.ashx?act=5&top='.$top;
		$json = ng_get_contents($URL);
		$oput = json_decode($json, true);
		foreach($oput['list'] as $k=>$v){
			if($db_charset == 'gbk') $v['gname'] = mb_convert_encoding($value, "GB2312", "UTF-8");
			$oput['list'][$k]['gname'] = unicode_decode($v['gname']);
			$ids[] = $v['gid']; 
		}
		$ids = implode(",", $ids);
		$sql = "SELECT `img`,`gid` FROM `ng_game` WHERE `gid` IN ($ids)";
		$query = DB::query($sql);
		while( $result = DB::fetch($query) ) {
			$imgs[$result['gid']]['img'] = $result['img'];
		}
		
		foreach($oput['list'] as $k=>$v){
			$oput['list'][$k]['gimg'] = PIC_HOST.'/'.$imgs[$oput['list'][$k]['gid']]['img'];
		}
		
		$cachedata = "<?php\n\r";
		$cachedata .= "return ".var_export($oput, true);
		$cachedata .= "\n\r?>";
		!empty($oput) && writeover($cachefile, $cachedata);
	}
	else {
		$oput = @include_once($cachefile);
	}
	echo json_encode($oput);
	exit;
}
elseif($s == 'topscore'){
	
	$gamename = trim($_GET['gamename']);
	$gameid = intval($_GET['gameid']);
	if (isset($_GET['activeFlag']) && intval($_GET['activeFlag']) !=0 ) {
		$hdflag = 9;
	}
	else{
		$hdflag = 0;
	}
	if ($needCache || $hdflag == 9) {
		$db_charet = 'gbk' && $gamename = mb_convert_encoding($gamename, 'UTF-8','GB2312');
		$URL = API_TOPSCORE_URL."?game=$gamename&gid=$gameid&hdflag=$hdflag&pid=$pid";
		$line = ng_get_contents($URL);
		$data = json_decode($line, true);
		$result = $data['result'];
		if (is_array($result)) {
			$cachedata = "<?php\n\r";
			$cachedata .= "return ".var_export($result, true);
			$cachedata .= "\n\r?>";
			!empty($result) && writeover($cachefile, $cachedata);
		}
	}
	else {
		$result = include_once($cachefile);
	}
	echo json_encode($result);
	exit;
}
elseif ($s == 'rel'){
	$gid = intval(trim($_GET['gid']));
	@include_once H_P.'/require/game.class.php';
	@include_once(R_P.'/require/credit.php');
	$game = new game($gid);
	$gameInfo = $game->getAll();
	$relatives =  $game->getRecommendsWithcType($game->getTypeid(),7);
	foreach($relatives as $k=>$v) {
		$relatives[$k]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $v['img']);
		$relatives[$k]['swf'] = SWF_HOST."/".$relatives[$k]['swf'];
		$relatives[$k]['shell'] = SWF_HOST."/".$relatives[$k]['shell'];
		$relatives[$k]['name'] = mb_convert_encoding($relatives[$k]['name'], 'UTF-8', $ng_charset);
	}
	$gameInfo['creditdesc'] = "开始游戏消耗{$gameInfo[useup]}{$credit->cType[$gameInfo[ctype]]}，游戏积分达到$gameInfo[rate0]，奖励$gameInfo[rate1]".$credit->cType[$gameInfo['ctype']];
	$gameInfo['name'] = mb_convert_encoding($gameInfo['name'], 'UTF-8', $ng_charset);
	$gameInfo['creditdesc'] = mb_convert_encoding($gameInfo['creditdesc'], 'UTF-8', $ng_charset);
	unset($gameInfo['rate0']);
	unset($gameInfo['rate1']);
	unset($gameInfo['useup']);
	echo json_encode(array('relatives'=>$relatives,'game'=>$gameInfo));
	exit;
}
elseif ($s == 'ivSet') {
	if($needCache) {
		$line = ng_get_contents(API_IPPV_URL);
		$data = json_decode($line, true);
		$result = $data['result'];
		if (is_string($result)) {
			$cachedata = "<?php\n\r";
			$cachedata .= "return ".var_export($result, true);
			$cachedata .= "\n\r?>";
			!empty($result) && writeover($cachefile, $cachedata);
		}
	}
	else {
		$result = @include_once($cachefile);
	}
	echo json_encode($result);
	exit;
}
elseif (isset($_GET['vcode'])) {
	session_start();
	//生成验证码图片
	Header("Content-type: image/PNG");
	$im = imagecreate(44,18);
	$back = ImageColorAllocate($im, 245,245,245);
	imagefill($im,0,0,$back); //背景
	srand((double)microtime()*1000000);
	$vcodes='';
	//生成4位数字
	for($i=0;$i<4;$i++){
		$font = ImageColorAllocate($im, rand(100,255),rand(0,100),rand(100,255));
		$authnum=rand(1,9);
		$vcodes.=$authnum;
		imagestring($im, 5, 2+$i*10, 1, $authnum, $font);
	}
	for($i=0;$i<100;$i++) //加入干扰象素
	{
		$randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
		imagesetpixel($im, rand()%70 , rand()%30 , $randcolor);
	}
	$_SESSION['VCODE'] = $vcodes;
	ImagePNG($im);
	ImageDestroy($im);
}
else if(isset($_GET['sign'])) {
	session_start();
	require_once H_P.'/require/signIn.class.php';
	$uid = $_G['uid'];
	empty($uid) && msg("uid_null");
	try{
		if (isset($_SESSION['signin'])) {
			$signIn = unserialize($_SESSION['signin']);
			
			if ($signIn->initTime < filemtime(H_P.'/data/basic_config.php') || time() - $signIn->initTime >= $pkgame_qd_limit_interval*3600) {
				$signIn = new SignIn($uid, $pkgame_qd_limit_interval, $pkgame_qd_limit_number,$pkgame_qd_reward_min,$pkgame_qd_reward_max,$pkgame_qd_reward_ctype);
			}
		}
		else {
			$signIn = new SignIn($uid, $pkgame_qd_limit_interval,$pkgame_qd_limit_number,$pkgame_qd_reward_min,$pkgame_qd_reward_max,$pkgame_qd_reward_ctype);
		}

		$message = $signIn->judgeSignInTime($uid, time());
		$_SESSION['signin'] = serialize($signIn);
	}
	catch (SignInException $e) {
		$e->log();
		msg('抱歉，签到配置错误，暂时无法使用',array('ret'=>0,'err'=>1));
	}
	msg($message);
}
else if ($_GET['s'] == 'checkUpdate'){
	$needUpdate = 0;
	if ($_G['uid'] == 1)
	{
		$str = HACK_UPDATE_URL."?currentVerNum=".$pkgame_version."&pluginType=2&lang=utf8&site=".$_SERVER['HTTP_HOST'];
		$data = ng_get_contents($str);

		$update = json_decode($data, true);
		if ($update['files'])
		{
			$needUpdate = 1;
			$exp = $update['exp'];
			$jsSrc = "http://www.97sng.com/public/bbs/pkgameUpdateNoticedz_$db_charset.js";
		}
	}
	echo json_encode(array('needUpdate'=>$needUpdate,'exp'=>$exp, 'jsSrc'=>$jsSrc));		
	exit;
}