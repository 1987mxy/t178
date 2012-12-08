<?php
//!defined("H_P") && exit("forbidden");
/*
 * functions.php Created on 2012-1-6
 * TODO
 * author:AgudaZaric
 * QQ:384318815
 * msn:coderzl@hotmail.com
 */

 /*
  * function mkdir with path iterator.eg path = a/b/c/d.
  * mkdir a,mkdir a/b, mkdir a/b/c,....
  *
  * parent dirctory path eg "./","../","../../" releative about current directory
  * 	or absulote path /var/www/...
  * @param string $fatherPathRelative
  * @param string $path
  * @param int $mode
  *
  * */
function mkdirWithPath($fatherPathRelative='./',$path,$mode) {
 	$dirs = explode('/',$path);
	$iterator = $fatherPathRelative;
	foreach($dirs as $k=>$dir) {
		$k ==0 && $iterator .= $dir;
		$k > 0 && $iterator .= '/'.$dir;
		!file_exists($iterator) && mkdir($iterator,$mode);
	}
 }

function creditLog($gid, $uid, $affect, $type ,$score){
	$time = time();
	if(empty($score)) $score = 0;
	DB::query("INSERT INTO ng_game_credit(`gid`,`uid`,`affect`,`type`,`score`,`addTime`) VALUES({$gid},{$uid},{$affect},'{$type}',{$score},{$time})");
	return 1;
}

 /*
  * generate css style for pages
  *
  * @param int $count	//总记录数
  * @param int $pageSize //每页显示条数
  * @param int $current //当前页
  * @param int $scope //当前页前or后面显示的按钮,保证显示总数为scope*2+1.默认显示所有页码
  * @param string $href //分页按钮连接base
  * @param int $style //样式选择器，定制。
  * @return string $pageCss HTML
  *
  * */
function pageCss($count, $pageSize, $current=1, $scope=0, $href='', $style=0) {
 	if(!$count) return false;
 	!$current && $current=1;
 	!$scope && $scope = floor(($count-$pageSize)/$pageSize);
 	$scope > floor($count/2) && $scope = floor(($count-$pageSize)/$pageSize);

	$queenLen = ceil($count/$pageSize);
 	$head = $current-$scope;
  	$tail = $current+$scope;

 	if($current <= $scope ) {
 		 $tail = $scope*2 + 1;
 		 $tail > $queenLen && $tail = $queenLen; //超出队列长度，指向队列尾部。
 		 $head = 1;
 	}

 	if($current >= $queenLen) {
 		$current = $tail = $queenLen;
 		$head = $queenLen - $scope*2;
 	}else if($current > $queenLen - $scope && $current < $queenLen) {
 		$tail = $queenLen;
 		$head = $queenLen - $scope*2;
 	}

 	$pre = $current-1;
 	$current <= 1 && $pre = 1;
 	$suf = $current+1;
 	$current+1 >= $queenLen && $suf = $queenLen;

	$pageCss = "<ul class='pageCss'><li><a href='$href 1'><<<</a></li><li><a href='$href$pre'>上一页</a></li>";

	for($index=$head; $index<=$tail; $index++) {
		if($index > 0) {
			if($index == $current) $pageCss .= "<li class='pageCurrent'><a href='$href$index'>$index</a></li>";
			else $pageCss .= "<li><a href='$href$index'>$index</a></li>";
		}
	}
	$pageCss .= "<li><a href='$href$suf'>下一页</a></li><li><a href='$href$queenLen'>>>></a></li></ul>";
	return $pageCss;
 }

 /*
  * get client real ip
  * @param null
  *
  * @return str $onlineip
  *
  * */
function pkgameGetIp() {
	 if ($_SERVER['HTTP_X_FORWARDED_FOR'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_X_FORWARDED_FOR'])) {
	     $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif  ($_SERVER['HTTP_CLIENT_IP']  && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_CLIENT_IP'])) {
	     $onlineip = $_SERVER['HTTP_CLIENT_IP'];
	} else {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}

/**
 *
 * @param string $msgFiled
 *
 * @param array $info
 * if record msg
 * @param int $debug
 * if echo
 * @param bool $io
 *
 * @return json
 * */
function msg($msgFiled = '', $info = array() ,$debug = 0, $ifExit = true, $ajax = 1) {
	global $_G;
	include_once(H_P."/data/lang_gameapi_msg.php");
	
	$result['msg'] = $msgFiled;
	!empty($lang['msg'][$msgFiled]) && $result["msg"] = $lang['msg'][$msgFiled];
	!$ajax && showmessage($result['msg']);
	
	if(is_array($info)){
		foreach($info as $k=>$v) {
			$result[$k] = $info[$k];
		}
	} else {
		$result["ret"] = 0;
	}

	if($debug) {
		$time = date("Y-m-d H:i:s", time());
		list($msec, $sec) = explode(' ', microtime());
 		$data = $time.".".ceil($msec*1000)."\t".$result[msg]."\r\n".$result."\r\n";
		writeover(H_P."/data/log.txt",$data, "ab+");
	}
	if($_G['charset'] == 'gbk') $result["msg"] = mb_convert_encoding($result["msg"], "UTF-8", "GB2312");
	$output = json_encode($result);
	while(!$debug && strlen($output) < 1024 ) {
		$output .= " ";
	}
	echo $output;
 	$ifExit && exit();
}

function getNewPkNum($user_id, $type) {
	$query = DB::query("select * from ng_game_pk WHERE status = 0 AND type = $type AND uid = $user_id");
	return DB::num_rows($query);
}

/*function writeover($file, $sql){
	$fp = fopen($file, "w");
	fwrite($fp, $sql);
	fclose($fp);

	return 1;
}*/

/**
 * 写文件
 *
 * @param string $fileName 文件绝对路径
 * @param string $data 数据
 * @param string $method 读写模式
 * @param bool $ifLock 是否锁文件
 * @param bool $ifCheckPath 是否检查文件名中的“..”
 * @param bool $ifChmod 是否将文件属性改为可读写
 * @return bool 是否写入成功   :注意rb+创建新文件均返回的false,请用wb+
 */
function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
	
	$fileName = S::escapePath($fileName, $ifCheckPath);
	touch($fileName);
	$handle = fopen($fileName, $method);
	$ifLock && flock($handle, LOCK_EX);
	$writeCheck = fwrite($handle, $data);
	$method == 'rb+' && ftruncate($handle, strlen($data));
	fclose($handle);
	$ifChmod && @chmod($fileName, 0777);
	return $writeCheck;
}

/*function readover($file){
	$fp = fopen($file, "r");
	$contents = fread($fp, filesize($file));
	fclose($fp);

	return $contents;
}*/

/**
 * 读取文件
 *
 * @param string $fileName 文件绝对路径
 * @param string $method 读取模式
 */
function readover($fileName, $method = 'rb') {
	$fileName = S::escapePath($fileName);
	$data = '';
	if ($handle = @fopen($fileName, $method)) {
		flock($handle, LOCK_SH);
		$data = @fread($handle, filesize($fileName));
		fclose($handle);
	}
	return $data;
}

/**
 *
 * @param int $start
 * @param int $size
 * @param string $sqlStatment
 * @return array $ips
 * */
function getStatisticsData($start, $size, $sqlStatment) {

	$sql = "SELECT g.name,g.gid FROM ng_game AS g " .
			"GROUP BY g.gid ".
			"ORDER BY g.gid ".
			"LIMIT $start,$size ";
	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$sql = "SELECT SUM(affect) AS affectAdd FROM ng_game_credit " .
			"WHERE affect >= 0 and gid = $result[gid] AND $sqlStatment GROUP BY gid ";
		$quer = DB::query($sql);
		$re = DB::fetch($quer);
		if($re['affectAdd']) $result['affectAdd'] = $re['affectAdd']; else $result['affectAdd'] = 0;
		$sql = "SELECT SUM(affect) AS affectSub FROM ng_game_credit " .
			"WHERE affect <= 0 and gid = $result[gid] AND $sqlStatment GROUP BY gid ";
		$quer = DB::query($sql);
		$re = DB::fetch($quer);
		if($re['affectSub']) $result['affectSub'] = $re['affectSub']; else $result['affectSub'] = 0;
		$sql = "SELECT COUNT(ip) AS sum_ip FROM  ng_game_ip WHERE
				 gid = $result[gid] AND $sqlStatment" ;
		$que = DB::query($sql);
		$sum = DB::fetch($que);
		$result['sum_ip'] = $sum['sum_ip'];
		$games[] = $result;
	}

	return $games;
}

function getStatisticsDatawithToday() {
	$query = DB::query("SELECT COUNT(ip) as ipCount FROM ng_game_ip WHERE addTime >".strtotime(date("Y-m-d",time())));
	$result = DB::fetch($query);
	if($result['ipCount']) $todayStatistics['ip'] = $result['ipCount'];

	$query = DB::query("SELECT SUM(affect) as addAffect FROM ng_game_credit WHERE affect > 0 AND addTime >".strtotime(date("Y-m-d",time())));
	$result = DB::fetch($query);
	if($result['addAffect']) $todayStatistics['addAffect'] = $result['addAffect'];

	$query = DB::query("SELECT SUM(affect)*-1 as usedAffect FROM ng_game_credit WHERE affect < 0 AND addTime >".strtotime(date("Y-m-d",time())));
	$result = DB::fetch($query);
	if($result['usedAffect']) $todayStatistics['usedAffect'] = $result['usedAffect'];

	return $todayStatistics;
}

/**
 * 获得全国擂台赛信息
 *
 * @return 1	返回操作成功
 **/
function getCunPkInfo($pid) {
	define('S_HOST','http://202.75.219.184:777');
	$URL = S_HOST.'/InterfaceForSitepage.aspx?seq=5&checksum=ABC&partner='.$pid.'&id=u001&action=QueryPrompt&entry=PK';

	$json_str = file_get_contents($URL);
	$json_ary = json_decode($json_str);
	//print_r($json_ary);

	$now_time = time();
	$json_file = H_P."/data/cunJsonTemp.txt";

	if(file_exists($json_file)) {
		$fileEndTime = file_get_contents($json_file);
		if($fileEndTime < $now_time) {
			setCunPkInfo($json_file, $json_ary, $now_time);
		}
	} else {
		setCunPkInfo($json_file, $json_ary, $now_time);
	}
	
	return 1;
}

/**
 * 全国赛写缓存入库函数
 *
 * @return 1	返回操作成功
 **/
function setCunPkInfo($json_file, $json_ary, $now_time) {
	$username = (string)$json_ary->gametip;
	$gid = (int)$json_ary->gameid;
	$score = (int)$json_ary->score;
	$endtime = (int)$json_ary->endtime;

	$fp = fopen($json_file, "w");
	fwrite($fp, $endtime);
	fclose($fp);
	$query = DB::query("INSERT INTO ng_game_pk (`uid`, `username`, `gid`, `score`, `starttime`, `endtime`, `ctime`) VALUES('0', '{$username}', '{$gid}', '{$score}', '{$now_time}', '{$endtime}', '{$now_time}')");
	
	return 1;
}

/**
 * 获得擂台赛列表总数
 *
 * @return string $result['total']	返回擂台列表数据
 **/
function getPkListTotal($type) {
	if($type > 0 && $type < 4) $value = "WHERE `type`='{$type}'"; else $value = "";
	$query = DB::query("SELECT count(*) AS total FROM ng_game_pk {$value}");
	$result = DB::fetch($query);
	
	return $result['total'];
}

/**
 * 获得擂台赛列表
 *
 * global array		$_G		DZ 全局环境
 * @param int		$start	列表起始数
 * @param int		$limit	列表显示数目
 * @return array	$gamepks	返回擂台列表数据
 **/
function getPkList($type, $start, $limit) {
	global $_G;

	if($type > 0 && $type < 4) $value = "WHERE p.type='{$type}'"; else $value = "";

	$query = DB::query("SELECT m.username, g.name, p.* FROM ng_game_pk AS p 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member AS m ON p.uid=m.uid
						LEFT JOIN ng_game AS g ON p.gid=g.gid 
						{$value}
						ORDER BY p.ctime DESC LIMIT {$start}, {$limit}");
	while($result = DB::fetch($query)) {
		$gamepks[] = $result;
	}
	
	foreach($gamepks as $k=>$v) {
		$stime = date('Y-m-d', $v['starttime']);
		$etime = date('Y-m-d', $v['endtime']);
		if($v['type'] == 1) $gamepks[$k]['type_name'] = "全国"; 
		elseif($v['type'] == 2) $gamepks[$k]['type_name'] = "站内"; 
		elseif($v['type'] == 3) $gamepks[$k]['type_name'] = "个人"; 
		else $gamepks[$k]['type_name'] = "";

		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;

			if((string)trim($ext) === (string)trim($v['price_ctype'])) {
				$gamepks[$k]['price_ctype_name'] = $value['title'];
				break;
			}
		}

		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = 'extcredits'.$key;

			if((string)trim($ext) === (string)trim($v['rate_ctype'])) {
				$gamepks[$k]['rate_ctype_name'] = $value['title'];
				break;
			}
		}
	}

	return $gamepks;
}

/**
 * 获得挑战记录总数
 *
 * @return string $result['total']	返回挑战记录总数
 **/
function getPkerListTotal($status = 0) {
	if(!$status) $value = "";
	elseif($status) $value = "WHERE status='{$status}'";
	$query = DB::query("SELECT count(*) AS total FROM ng_game_pker {$value}");
	$result = DB::fetch($query);
	
	return $result['total'];
}

/**
 * 获得挑战列表
 *
 * global array		$_G		DZ 全局环境
 * @param int		$start	列表起始数
 * @param int		$limit	列表显示数目
 * @return array	$gamepks	返回挑战记录列表数据
 **/
function getPkerList($start, $limit, $status = 0) {
	global $_G;

	if(!$status) $value = "";
	elseif($status) $value = "WHERE status='{$status}'";

	$query = DB::query("SELECT g.name, p.* FROM ng_game_pker AS p 
						LEFT JOIN ng_game AS g ON p.gid=g.gid 
						{$value}
						ORDER BY p.ctime DESC LIMIT {$start}, {$limit}");
	while($result = DB::fetch($query)) {
		$gamepkers[] = $result;
	}
	
	return $gamepkers;
}

/**
 * 获得用户在游戏中的记录信息
 *
 * @return array $gamepks	返回用户的游戏记录信息
 **/
function getUserGameInfo($gid, $uid) {
	$query = DB::query("SELECT * FROM ng_game_credit WHERE gid='{$gid}' AND uid='{$uid}' ORDER BY score DESC");
	while($result = DB::fetch($query)) {
		$gamepks[] = $result;
	}

	$num = count($gamepks);
	
	$userGames = array();
	$userGames[0]['gid'] = $gamepks[0]['gid'];
	$userGames[0]['uid'] = $gamepks[0]['uid'];
	$userGames[0]['score'] = $gamepks[0]['score'];
	$i = $num - 1;
	$userGames[1]['gid'] = $gamepks[$i]['gid'];
	$userGames[1]['uid'] = $gamepks[$i]['uid'];
	$userGames[1]['score'] = $gamepks[$i]['score'];
		
	return $userGames;
}

/**
 * 获得 Pk 防御者记录信息
 *
 * @return array $result	返回防御者的游戏记录信息
 **/
function getDefGameInfo($gid, $uid) {
	$query = DB::query("SELECT * FROM ng_game_credit WHERE gid='{$gid}' AND uid='{$uid}' ORDER BY score DESC LIMIT 1");
	$result = DB::fetch($query);
			
	return $result;
}

/**
 * 获得擂台主的记录信息
 *
 * @return array $result	返回擂主的游戏记录信息
 **/
function getPkGameInfo($pkid) {
	$query = DB::query("SELECT * FROM ng_game_pk WHERE id='{$pkid}'");
	$result = DB::fetch($query);
			
	return $result;
}

/**
 * convert unicode /uxxxx to character
 * @param string $originalString
 * @param string $charSet
 * @return string $applicableString
 * */
function unicode_decode($string, $charSet = 'UTF-8') {

	preg_match_all('#\\/u([0-9a-f]{2,4})#ism', $string, $matches);
	foreach($matches[1] as $matche){
		$decode .= decode_callback($matche, $charSet);
	}
	return $decode;

}

function decode_callback($matche, $charset){
	$c = pack("H*", $matche);
	if(strlen($c) == 2){
		return mb_convert_encoding($c, "$charset", "UCS-2BE");
	} else {
		return $c;
	}
}

/**
 * 截取字符串
 *
 * @param string $title
 * @param int $length
 * @param string $charset
 *
 * return string
 *
 **/
function subString($title, $length, $charset = "UTF-8", $sufix = "..."){

      if($charset=="GB2312") {
            if (strlen($title)>$length) {
                  $temp = 0;
                  for($i=0; $i<$length; $i++)
                  if (ord($title[$i])> 128) $temp++;
                  if ($temp%2 == 0)
                        $title = substr($title,0,$length).$sufix;
                  else
                        $title = substr($title,0,$length+1).$sufix;
            }
            return $title;
      } else if($charset=="UTF-8") {
            $tmpTitle = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,0}'.
'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$length.'}).*#s','$1',$title);
            if(strlen($title)>$length)
                  $tmpTitle .=$sufix;
            return $tmpTitle;
      }
 }

/**
 * update config file
 *
 * @param array $variable, array("key"=>,"value"=>array(array1,array2,...))
 * @param string $file
 *
 **/
function updateSetting($variable, $file) {

	$data = "<?php\r\n";
	foreach($variable['value'] as $k=>$v) {
		$data .= "\$".$variable['key']."[{$k}] = array(\n";
		foreach($v as $key=>$value) {
			$value = addslashes($value);
			$data .= "\t'{$key}' => '$value',\n";
		}

		$data .= ");\n";
	}
	$data .= "?>";
	$fp = fopen($file, 'w');
	fwrite($fp, $data);
	fclose($fp);
}

function getGameByWhere($where) {
 	global $db_charset;

 	$query = DB::query("SELECT * FROM ng_game $where");
 	while($result = DB::fetch($query)) {
 		if($db_charset == 'gbk') {
 			$result['describe'] = subString($result['describe'], 30, 'GB2312');
 		}else{
 			$result['describe'] = subString($result['describe'], 30, 'utf-8');
 		}
 		$Game[] = $result;
 	}
 	return $Game;
}

function submitSiteInfoToNG($post_data){
	$db_charset = 'utf-8';

 	ini_set('allow_url_include',1);
	$URL='http://www.97sng.com/apps/clubapi.ashx';
	$URL_Info=parse_url($URL);

	if(function_exists('fsockopen')) {
		foreach($post_data as $key=>$value) {
			if($db_charset == 'gbk') {
				$post_data[$key] = mb_convert_encoding($value, "UTF-8", "GB2312");
			}
			$values[]="$key=".urlencode($post_data[$key]);
		}
		$values[]= "sig=".strtoupper(md5("TK6u7RlmpVeQVLvD".$post_data['name'].$post_data['phone']));
		$data_string=implode("&",$values);

		!isset($URL_Info["port"]) && $URL_Info["port"]=80;

		$request.="POST ".$URL_Info["path"]." HTTP/1.1\n";
		$request.="Host: ".$URL_Info["host"]."\n";
		$request.="Referer: $referrer\n";
		$request.="Content-type: application/x-www-form-urlencoded\n";
		$request.="Content-length: ".strlen($data_string)."\n";
		$request.="Connection: close\n";
		$request.="\n";
		$request.=$data_string."\n";

		$fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
		fputs($fp, $request);
	    $inheader = 1;
	    while (!feof($fp)) {
	        $line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据
	        if ($inheader && ($line == "\n" || $line == "\r\n")) { //以响应头和内容间的换行为标记
	            $inheader = 0;
	         }
	    }
		fclose($fp);
		if($line)
			return $line;
		else
			return null;
	}
	else{
		$data_string = http_build_query($post_data);

		$opts = array(
		  'http'=>array(
		    'method'=>"POST",
		    'header'=>"Content-type: application/x-www-form-urlencoded",
		    'content'=>"$data_string"
		)
		);
		$context = stream_context_create($opts);
		$fp = fopen($URL,'r',false,$context);
		if(!$fp) {
			return null;
		}
		else{
			$vars= explode ("&",stream_get_contents($fp));
			return $vars[0];
		}
	}
}

function submitStatisticsData($post_data) {
	global $URL;
	$db_charset = 'utf-8';

	ini_set('allow_url_include',1);

	foreach($post_data as $key=>$value) {
		if($db_charset == 'gbk') {
			$post_data[$key] = mb_convert_encoding($value, "UTF-8", "GB2312");
		}
		$values[]="$key=".urlencode($post_data[$key]);
	}

	$data_string=implode("&",$values);
	$fp = fopen($URL.$data_string,"r");

	$inheader = 1;
	while (!feof($fp)) {
		$line = fgets($fp,1024);
		if ($inheader && ($line == "\n" || $line == "\r\n")) {
			$inheader = 0;
		}
	}
	fclose($fp);
	if($line)
		return $line;
	else
		return null;
}

function getRankingGame($limit = 'LIMIT 1') {
	$query = DB::query("select count(i.gid) as num,i.gid,g.name from ng_game_ip i
			left join ng_game g on g.gid = i.gid
			group by i.gid
			order by num desc
			$limit");
	while($result = DB::fetch($query)) {
		$arr[] = $result;
	}
	return $arr;
}

function getUserActive($limit = 'LIMIT 10') {
	global $db, $_G;
	$description = array("真好玩", "他好得意", "他太给力了", "他太厉害了");
	$query = DB::query("SELECT m.username,c.uid,g.name as gamename,c.gid,max(c.addTime) as addTime FROM ng_game_credit c
                         LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member m ON c.uid = m.uid
                         LEFT JOIN ng_game g ON c.gid = g.gid
                         GROUP BY c.uid,c.gid
                         ORDER BY addTime DESC
						 $limit");
	while( $result = DB::fetch($query) ) {
		if (strtotime(date("Y-m-d",$result['addTime'])) < strtotime(date("Y-m-d",time()))) {
			$result['addTime'] = date("m-d",$result['addTime']);
		}
		else{
			$result['addTime'] = date("H:i",$result['addTime']);
		}
		$result['descripton'] = $description[rand(0, count($description)-1)];
		$result['username'] = subString($result['username'], 6, 'GB2312','.');
		$result['gamename'] = subString($result['gamename'], 6, 'GB2312','.');
		$arr[] = $result;
	}
	return $arr;
}

function getGameInfo($gid) {
	$query = DB::query("select g.*, gs.url as shellUrl from ng_game g
				left join ng_game_shell gs on g.shellId=gs.id
				where g.gid={$gid}");
	$result = DB::fetch($query);

	return $result;
}

function getRecommendsWithcType($gid, $ctype, $count = 10) {
		$sql = "SELECT g.*,s.url as shell FROM ng_game g
				LEFT JOIN ng_game_shell s ON g.shellId = s.id
				WHERE g.type = '$ctype' AND g.gid != $gid
				LIMIT $count";
		$query = DB::query($sql);
		 
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		return $arr;
}

function getPkRankingUser($gid, $limit = 'LIMIT 5') {
	global $_G;
	$query = DB::query("select m.uid, m.username, max(c.score) as score from {$_G['config']['db'][1]['tablepre']}common_member m
				left join ng_game_credit c on m.uid = c.uid
				where c.gid = {$gid}
			 	group by c.uid
			 	order by score desc
				{$limit}");
	while( $result = DB::fetch($query) ) {
		$arr[] = $result;
	}
	return $arr;
}

function validateConfig($ctype, $dz_ext){
	
	foreach($dz_ext as $k => $v) {
		$ext = 'extcredits'.$k;
		if((string)trim($ctype) === (string)trim($ext)) return true;
	}
	return false;
}

function getAffect($score, $gameInfo){
	if($score >= $gameInfo['rate0'])
    	return $gameInfo['rate1'];
    else
    	return 0;
}

function setIndexJs($ggjs, $ggjs_file){
	$old_time = filemtime(H_P."/js/".$ggjs_file);
	$now_time = time();
	$cache_time = $now_time - $old_time;
	if($cache_time >= 0 && $cache_time > (24 * 60 * 60)) {
		$ggjs_var = file_get_contents($ggjs);	
		$ggjs_var = str_replace("\$", "jq", $ggjs_var);
		$ggjs_var = str_replace("open", "ng_open", $ggjs_var);
		$ggjs_var = str_replace("showWindow", "ng_showWindow", $ggjs_var);
		$ggjs_var = str_replace("text-align: center;", "text-align: center;cursor: pointer;", $ggjs_var);

		$fp = fopen(H_P."/js/".$ggjs_file, "w");
		fwrite($fp, $ggjs_var);
		fclose($fp);
	}
    return 1;
}

function get_ngavatar($uid, $size = 'small', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'small';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}

function ng_get_contents($URL){
	if( function_exists('fopen') ) {
		if( ini_get('allow_url_fopen' ) == false) throw new gameException("fail to get game's info. cause php.ini allow_url_fopen = 0");
		$fp = fopen($URL,"r");
		$inheader = 1;
		$data = '';
		while (!feof($fp)) {
			$data .= fgets($fp,1024);
			if ($inheader && ($data == "\n" || $data == "\r\n")) {
				$inheader = 0;
			}
		}
		fclose($fp);
	}
	else if( function_exists('file_get_contents')){
		$data = file_get_contents($URL);
	}
	else {
		throw new gameException("fail to get game's info. cause function fopen,file_get_contents not support.");
	}
	return $data;
}

function isUserExist($uid) {
   	$sql = "SELECT uid FROM ng_game_user WHERE register = 1 AND uid = ".trim($uid);
	$query = DB::query($sql);
	$result = DB::fetch($query);
   	if($result['uid'])
   		return true;
   	else
   		return false;
}

function register($uid, $pid) {
    //if(empty($this->_gid)) throw new gameException("obj game killed");
    if(serverReg($uid ,$pid) !==false) {
	   	$sql = "insert into ng_game_user(`pid`,`uid`, `register`) values($pid,".trim($uid).",1)";
		DB::query($sql);
    }
}

/**
 * 向服务器注册用户信息
 *
 * global array		$_G		DZ 全局环境
 * @param int		$uid	用户ID序号
 * @param int		$pid	站点注册序号
 **/
function serverReg($uid, $pid) {
	global $_G;
	$URL = "http://202.75.219.184:777/InterfaceForSitepage.aspx";
	$db_charset = 'utf8';

	$avatar_ext = 'uc_server/data/avatar/'.get_ngavatar($uid, $size, $type);
	$avatar = $_G['siteurl'] . $avatar_ext; // 获得用户头像
	$ng_path = substr(H_P, 0, (strlen(H_P)-22));
	if(!file_exists($ng_path."/".$avatar_ext)) $avatar = $_G['siteurl'].'uc_server/images/noavatar_small.gif'; // 获得用户头像
	$seq = readover(H_P."/data/requestSeq.txt");
	ini_set('allow_url_include',1);
	$db_charset == 'gbk' && $user[username]= mb_convert_encoding($user[username], "UTF-8", "GB2312");
	$url = $URL."?action=NotifyOnline&partner=$pid&seq=$seq&id={$uid}&avater={$avatar}&nick={$_G['username']}";
	$fp = fopen($url,"r");
	$inheader = 1;
	while (!feof($fp)) {
		$line = fgets($fp,1024);
		if ($inheader && ($line == "\n" || $line == "\r\n")) {
			$inheader = 0;
		}
	}
	fclose($fp);
	if($line) {
		$result = json_decode($line,true);
		if( $result['ret'] == 0 ) {
			$ori = readover(H_P."/data/requestSeq.txt");
			if($seq>$ori) writeover(H_P."/data/requestSeq.txt", $seq);
		}
		else if( $result['ret'] == -2 ) {
			$ori = readover(H_P."/data/requestSeq.txt");
			if($seq>$ori) writeover(H_P."/data/requestSeq.txt", $seq);
			return false;
		}
		else {
			return false;
		}
	}
	else
		return false;
}

/**
 * 获得用户头像
 *
 * global array		$_G		DZ 全局环境
 * @param int		$uid	用户ID序号
 **/
function getUserPhoto($uid) {
	global $_G;
	
	$avatar_ext = 'uc_server/data/avatar/'.get_ngavatar($uid, $size, $type); // DZ 用户头像存放的目录
	$avatar = $_G['siteurl'] . $avatar_ext;			// 获得用户头像
	$ng_path = substr(H_P, 0, (strlen(H_P)-22));	// 获得 DZ 根路径
	if(!file_exists($ng_path."/".$avatar_ext)) $avatar = $_G['siteurl'].'uc_server/images/noavatar_small.gif'; // 获得用户头像
	return $avatar;
}

/**
 * 游戏热评入库
 *
 * global array		$_G		DZ 全局环境
 * @param int		$uid	用户ID序号
 * @param string	$avatar	用户头像路径
 * @param array		$post	用户提交数据
 **/
function setReply($gid, $avatar, $post) {
	global $_G;
	
	$uid = $_G['uid'];
	$username = $_G['username'];
	$userphoto = $avatar;
	$title = $post['title'];
	$contents = $post['contents'];
	$time = time();
	$sql = "INSERT INTO ng_game_replay(`uid`, `username`, `userphoto`, `gid`, `title`, `content`, `ctime`) 
		VALUES('{$uid}', '{$username}', '{$userphoto}', '{$gid}', '{$title}', '{$contents}', '{$time}')";
	$result = DB::query($sql);
	if($result) return 1; 
	else return 0;
}

/**
 * 获得游戏热评列表
 *
 * global array		$_G	DZ 全局环境
 * @param int		$gid	游戏ID序号
 * @return array	$arr	返回热评列表
 **/
function getReply($gid) {
	$sql = "SELECT * FROM ng_game_replay WHERE gid='{$gid}'";
	$query = DB::query($sql);
	while( $result = DB::fetch($query) ) {
		$arr[] = $result;
	}
	return $arr;
}

/**
 * 设置cookie
 *
 * @global string $db_ckpath
 * @global string $db_ckdomain
 * @global int $timestamp
 * @global array $pwServer
 * @param string $cookieName cookie名
 * @param string $cookieValue cookie值
 * @param int|string $expireTime cookie过期时间，为F表示1年后过期
 * @param bool $needPrefix cookie名是否加前缀
 * @return bool 是否设置成功
 */
function Cookie($cookieName, $cookieValue, $expireTime = 'F') {
	$cookiePath = '/';
	$cookieDomain = '';

	$cookieValue = str_replace("=", '', $cookieValue);
	strlen($cookieValue) > 512 && $cookieValue = substr($cookieValue, 0, 512);

	if ($expireTime == 'F') {
		$expireTime = $timestamp + 31536000;
	} 

	return setcookie($cookieName, $cookieValue, $expireTime, $cookiePath, $cookieDomain);
}

/**
 * 变量导出为字符串
 *
 * @param mixed $input 变量
 * @param string $indent 缩进
 * @return string
 */
function dz_var_export($input, $indent = '') {
	switch (gettype($input)) {
		case 'string' :
			return "'" . str_replace(array("\\", "'"), array("\\\\", "\'"), $input) . "'";
		case 'array' :
			$output = "array(\r\n";
			foreach ($input as $key => $value) {
				$output .= $indent . "\t" . dz_var_export($key, $indent . "\t") . ' => ' . dz_var_export($value, $indent . "\t");
				$output .= ",\r\n";
			}
			$output .= $indent . ')';
			return $output;
		case 'boolean' :
			return $input ? 'true' : 'false';
		case 'NULL' :
			return 'NULL';
		case 'integer' :
		case 'double' :
		case 'float' :
			return "'" . (string) $input . "'";
	}
	return 'NULL';
}

/******************** 数据库连接调用类 ******************************/
class ng_dbstuff {
	var $querynum = 0;
	var $link;
	var $histories;
	var $time;
	var $tablepre;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset, $pconnect = 0, $tablepre='', $time = 0) {
		$this->time = $time;
		$this->tablepre = $tablepre;
		if($pconnect) {
			if(!$this->link = mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				$this->halt('Can not connect to MySQL server');
			}
		}

		if($this->version() > '4.1') {
			if($dbcharset) {
				mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->link);
			}

			if($this->version() > '5.0.1') {
				mysql_query("SET sql_mode=''", $this->link);
			}
		}

		if($dbname) {
			mysql_select_db($dbname, $this->link);
		}

	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function result_first($sql) {
		$query = $this->query($sql);
		return $this->result($query, 0);
	}

	function fetch_first($sql) {
		$query = $this->query($sql);
		return $this->fetch_array($query);
	}

	function fetch_all($sql) {
		$arr = array();
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$arr[] = $data;
		}
		return $arr;
	}

	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}

	function query($sql, $type = '', $cachetime = FALSE) {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		return mysql_get_server_info($this->link);
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt($message = '', $sql = '') {
		echo 'run_sql_error', $message.'<br /><br />'.$sql.'<br /> '.mysql_error();
	}
}

/*********** DZ 用户登入类 ******************************/
class logging_ctl {

	function logging_ctl() {
		require_once libfile('function/misc');
		loaducenter();
	}

	function logging_more($questionexist) {
		global $_G;
		if(empty($_G['gp_lssubmit'])) {
			return;
		}
		$auth = authcode($_G['gp_username']."\t".$_G['gp_password']."\t".($questionexist ? 1 : 0), 'ENCODE');
		$js = '<script type="text/javascript">showWindow(\'login\', \'member.php?mod=logging&action=login&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()).(!empty($_G['gp_cookietime']) ? '&cookietime=1' : '').'\')</script>';
		//showmessage('location_login', '', array('type' => 1), array('extrajs' => $js));
	}

	function on_login() {
		global $_G;
		if($_G['uid']) {
			$referer = dreferer();
			$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);
			//showmessage('login_succeed', $referer ? $referer : './', $param, array('showdialog' => 1, 'locationtime' => true, 'extrajs' => $ucsynlogin));
		}

		$from_connect = $this->setting['connect']['allow'] && !empty($_G['gp_from']) ? 1 : 0;
		$seccodecheck = $from_connect ? false : $this->setting['seccodestatus'] & 2;
		$seccodestatus = !empty($_G['gp_lssubmit']) ? false : $seccodecheck;
		$invite = getinvite();

		if(!submitcheck('loginsubmit', 1, $seccodestatus)) {

			$auth = '';
			$username = !empty($_G['cookie']['loginuser']) ? htmlspecialchars($_G['cookie']['loginuser']) : '';

			if(!empty($_G['gp_auth'])) {
				list($username, $password, $questionexist) = explode("\t", authcode($_G['gp_auth'], 'DECODE'));
				$username = htmlspecialchars($username);
				if($username && $password) {
					$auth = htmlspecialchars($_G['gp_auth']);
				} else {
					$auth = '';
				}
			}

			$cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_G['gp_cookietime']) ? 'checked="checked"' : '';

			if($seccodecheck) {
				$seccode = random(6, 1) + $seccode{0} * 1000000;
			}

			if($this->extrafile && file_exists(libfile('member/'.$this->extrafile, 'module'))) {
				require_once libfile('member/'.$this->extrafile, 'module');
			}

			$navtitle = lang('core', 'title_login');
			include template($this->template);

		} else {

			if(!empty($_G['gp_auth'])) {
				list($_G['gp_username'], $_G['gp_password']) = daddslashes(explode("\t", authcode($_G['gp_auth'], 'DECODE')));
			}

			if(!($_G['member_loginperm'] = logincheck($_G['gp_username']))) {
				//showmessage('login_strike');
			}
			if($_G['gp_fastloginfield']) {
				$_G['gp_loginfield'] = $_G['gp_fastloginfield'];
			}
			$_G['uid'] = $_G['member']['uid'] = 0;
			$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
			if(!$_G['gp_password'] || $_G['gp_password'] != addslashes($_G['gp_password'])) {
				//showmessage('profile_passwd_illegal');
			}
			$result = userlogin($_G['gp_username'], $_G['gp_password'], $_G['gp_questionid'], $_G['gp_answer'], $this->setting['autoidselect'] ? 'auto' : $_G['gp_loginfield']);
			$uid = $result['ucresult']['uid'];

			if(!empty($_G['gp_lssubmit']) && ($result['ucresult']['uid'] == -3 || $seccodecheck && $result['status'] > 0)) {
				$_G['gp_username'] = $result['ucresult']['username'];
				$_G['gp_password'] = stripslashes($_G['gp_password']);
				$this->logging_more($result['ucresult']['uid'] == -3);
			}

			if($result['status'] == -1) {
				if(!$this->setting['fastactivation']) {
					$auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');
					//showmessage('location_activation', 'member.php?mod='.$this->setting['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()), array(), array('location' => true));
				} else {
					$result = daddslashes($result);
					$init_arr = explode(',', $this->setting['initcredits']);
					DB::insert('common_member', array(
						'uid' => $uid,
						'username' => $result['ucresult']['username'],
						'password' => md5(random(10)),
						'email' => $result['ucresult']['email'],
						'adminid' => 0,
						'groupid' => $this->setting['regverify'] ? 8 : $this->setting['newusergroupid'],
						'regdate' => TIMESTAMP,
						'credits' => $init_arr[0],
						'timeoffset' => 9999
					));
					DB::insert('common_member_status', array(
						'uid' => $uid,
						'regip' => $_G['clientip'],
						'lastip' => $_G['clientip'],
						'lastvisit' => TIMESTAMP,
						'lastactivity' => TIMESTAMP,
						'lastpost' => 0,
						'lastsendmail' => 0
					));
					DB::insert('common_member_profile', array('uid' => $uid));
					DB::insert('common_member_field_forum', array('uid' => $uid));
					DB::insert('common_member_field_home', array('uid' => $uid));
					DB::insert('common_member_count', array(
						'uid' => $uid,
						'extcredits1' => $init_arr[1],
						'extcredits2' => $init_arr[2],
						'extcredits3' => $init_arr[3],
						'extcredits4' => $init_arr[4],
						'extcredits5' => $init_arr[5],
						'extcredits6' => $init_arr[6],
						'extcredits7' => $init_arr[7],
						'extcredits8' => $init_arr[8]
					));
					manyoulog('user', $uid, 'add');
					$result['member'] = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$uid'");
					$result['status'] = 1;
				}
			}

			if($result['status'] > 0) {

				if($this->extrafile && file_exists(libfile('member/'.$this->extrafile, 'module'))) {
					require_once libfile('member/'.$this->extrafile, 'module');
				}

				setloginstatus($result['member'], $_G['gp_cookietime'] ? 2592000 : 0);

				DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."', lastactivity='".TIMESTAMP."' WHERE uid='$_G[uid]'");
				$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

				if($invite['id']) {
					$result = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_invite')." WHERE uid='$invite[uid]' AND fuid='$uid'");
					if(!$result) {
						DB::update("common_invite", array('fuid'=>$uid, 'fusername'=>$_G['username']), array('id'=>$invite['id']));
						updatestat('invite');
					} else {
						$invite = array();
					}
				}
				if($invite['uid']) {
					require_once libfile('function/friend');
					friend_make($invite['uid'], $invite['username'], false);
					dsetcookie('invite_auth', '');
					if($invite['appid']) {
						updatestat('appinvite');
					}
				}

				$param = array(
					'username' => $result['ucresult']['username'],
					'usergroup' => $_G['group']['grouptitle'],
					'uid' => $_G['member']['uid'],
					'groupid' => $_G['groupid'],
					'syn' => $ucsynlogin ? 1 : 0
				);

				$extra = array(
					'showdialog' => true,
					'locationtime' => true,
					'extrajs' => $ucsynlogin
				);
				$loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';

				$location = $invite || $_G['groupid'] == 8 ? 'home.php?mod=space&do=home' : dreferer();
				if(empty($_G['gp_handlekey']) || !empty($_G['gp_lssubmit'])) {
					if(defined('IN_MOBILE')) {
						//showmessage('location_login_succeed_mobile', $location, array('username' => $result['ucresult']['username']), array('location' => true));
					} else {
						if(!empty($_G['gp_lssubmit'])) {
							if(!$ucsynlogin) {
								$extra['location'] = true;
							}
							//showmessage($loginmessage, $location, $param, $extra);
						} else {
							$href = str_replace("'", "\'", $location);
							/*showmessage('location_login_succeed', $location, array(),
								array(
									'showid' => 'succeedmessage',
									'extrajs' => '<script type="text/javascript">'.
										'setTimeout("window.location.href =\''.$href.'\';", 3000);'.
										'$(\'succeedmessage_href\').href = \''.$href.'\';'.
										'$(\'main_message\').style.display = \'none\';'.
										'$(\'main_succeed\').style.display = \'\';'.
										'$(\'succeedlocation\').innerHTML = \''.lang('message', $loginmessage, $param).'\';</script>'.$ucsynlogin,
									'striptags' => false,
								)
							);*/
						}
					}
				} else {
					//showmessage($loginmessage, $location, $param, $extra);
				}
			} else {
				$password = preg_replace("/^(.{".round(strlen($_G['gp_password']) / 4)."})(.+?)(.{".round(strlen($_G['gp_password']) / 6)."})$/s", "\\1***\\3", $_G['gp_password']);
				$errorlog = dhtmlspecialchars(
					TIMESTAMP."\t".
					($result['ucresult']['username'] ? $result['ucresult']['username'] : dstripslashes($_G['gp_username']))."\t".
					$password."\t".
					"Ques #".intval($_G['gp_questionid'])."\t".
					$_G['clientip']);
				writelog('illegallog', $errorlog);
				loginfailed($_G['gp_username']);
				$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_G['gp_questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
				//showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm']));
			}

		}

	}
}

function challengeAjaxOutput($lists) {
	global $_G;
	
	if(!$_G['charset']) return;
	if( !empty($lists) ) {
		foreach( $lists as $key=>$pk ) {
			if (empty($pk['gid'])) {
				unset($lists[$key]); //将导致json解析问题。返回json对象无法转换为数组。
				continue;
			}
			$lists[$key]['endtime'] = $pk['endtime'] < time() ? "已结束" : date("Y-m-d", $pk['endtime']);
			$db_charset == 'gbk' && $lists[$key]['endtime'] = mb_convert_encoding($lists[$key]['endtime'], 'UTF-8','GB2312');
			$lists[$key]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $pk['img']);
			$db_charset == 'gbk' && $lists[$key]['name'] = mb_convert_encoding($pk['name'], 'UTF-8','GB2312');
			$db_charset == 'gbk' && $lists[$key]['username'] = mb_convert_encoding($pk['username'], 'UTF-8','GB2312');
			$db_charset == 'gbk' && $lists[$key]['pkname'] = mb_convert_encoding($pk['pkname'], 'UTF-8','GB2312');
		}
		msg("",array("list"=>$lists),false,true);
	}
	else {
		msg("",array("ret"=>0),false,true);
	}
}

function pkListOutput($lists) {
	global $_G;
	@include_once H_P.'/require/challenge.class.php';
	
	if( !empty($lists) ) 
	{
		foreach( $lists as $k=>$pk ) 
		{
			//list($lists[$k]['act_usericon']) = "img.png";
			//list($lists[$k]['def_usericon']) = "img.png";
			$lists[$k]['endtime'] = date("m月d日结束",$pk['endtime']);
			if($lists[$k]['status'] == challenge::PK_STATUS_OK)
			{
				if ($_G['uid'] == $pk['def_uid']) {
					$pk['lose'] && $img = 'zhanshuwin.png';
					!$pk['lose'] && $img = 'zhanshulose.png';
				}
				else {
					$pk['lose'] && $img = 'zhanshulose.png';
					!$pk['lose'] && $img = 'zhanshuwin.png';
				}
				if($pk[draw]){
					$img = 'zhanshudraw.png';
				}
				
				$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/'.$img.'">';
				
			}
			elseif($lists[$k]['status'] == challenge::PK_STATUS_ING) 
			{
				$img = 'zhanshuIng.gif';
				$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/'.$img.'">';
				
			}
			elseif($lists[$k]['status'] == challenge::PK_STATUS_MAKESURE)
			{
				if ($_G['uid'] == $pk['def_uid'])
				{
					$pk['endtime'] = date("m月d日",$pk['endtime']);
					foreach($_G['setting']['extcredits'] as $key => $value){
						$ext = 'extcredits'.$key;
						//getuserprofile($ext);
						//echo $value['title']."=>".$_G['member'][$ext];
						if((string)trim($ext) === (string)trim($pk[case_ctype])) {
							$case_ctypeTitle = $value['title'];
							break;
						}
					}
					$lists[$k]['result'] = "<div class='pk_result_notice' style='float:left; width:70%'>
										<p style='color:red'>温馨提示：</p>
										<p><a style='color:#248181' href='home.php?mod=space&uid={$pk[act_uid]}'>{$pk['act_username']}</a>
											向您发起挑战，参与挑战扣除{$pk['case']}{$case_ctypeTitle}，
											胜利者将获得<font color='#248181'>双倍</font>奖励.</p>
										<p style='color:#B6B6B6;'>结束时间：{$pk['endtime']}</p>
									</div>
									<div class='pk_result_op' style='float:right; width:29%;'>
										<p><a href='plugin.php?id=bbsgame&action=game&mode=pk&gid={$pk[gid]}&pk_id={$pk[pkid]}'><img src='source/plugin/bbsgame/images/2/accept.gif'></a></p>
										<p><a onclick='return refusePk({$pk[pkid]});' href='javascript:void(0);'><img src='source/plugin/bbsgame/images/2/refuse.gif'></a></p>
									</div>";
				}
				else{
					$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/zhanshunotsure.gif">';
				}
			}
			elseif($lists[$k]['status'] == challenge::PK_STATUS_REFUSE)
			{
				$img = 'zhanshuRefuse.gif';
				$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/'.$img.'">';
			}
		}
	}
	return $lists;
}

function pkListAjaxOutput($lists) {
	global $_G;
	
	if (!$_G['charset']) return;
	
	if (!empty($lists)) {
		
		foreach ( $lists as $k=>$pk ) {
			
			$lists[$k]['endtime'] = date("m月d日结束",$pk['endtime']);
			
			$lists[$k]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $pk['img']);
			if($_G['charset'] == 'gbk')
			{
				$lists[$k]['endtime'] = mb_convert_encoding($lists[$k]['endtime'], 'UTF-8', 'GB2312');
				$lists[$k]['def_username'] = mb_convert_encoding($pk['def_username'], 'UTF-8','GB2312');
				$lists[$k]['act_username'] = mb_convert_encoding($pk['act_username'], 'UTF-8','GB2312');
			}
		
			if($lists[$k]['status'] == challenge::PK_STATUS_OK)
			{
				if ($winddb['uid'] == $pk['def_uid']) {
					$pk['lose'] && $img = 'zhanshuwin.png';
					!$pk['lose'] && $img = 'zhanshulose.png';
				}
				else {
					$pk['lose'] && $img = 'zhanshulose.png';
					!$pk['lose'] && $img = 'zhanshuwin.png';
				}
				
				if($pk[draw])
					$img = 'zhanshudraw.png';
				
				$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/'.$img.'">';
				
			}
			elseif($lists[$k]['status'] == challenge::PK_STATUS_ING) 
			{
				$img = 'zhanshuIng.gif';
				$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/'.$img.'">';
			}
			elseif($lists[$k]['status'] == challenge::PK_STATUS_MAKESURE)
			{
				if ($winddb['uid'] == $pk['def_uid'])
				{
					$lists[$k]['case_ctype'] = $credit->cType[$pk[case_ctype]];
					$lists[$k]['endtime'] = date("m月d日",$pk['endtime']);
					if ($_G['charset'] == 'gbk')
					{
						$lists[$k]['case_ctype'] = mb_convert_encoding($lists[$k]['case_ctype'], 'UTF-8', 'GB2312');
						$lists[$k]['endtime'] = mb_convert_encoding($lists[$k]['endtime'], 'UTF-8', 'GB2312');
					}
					$lists[$k]['result'] = "acceptOrRefuse";
				}
				else
				{
					$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/zhanshunotsure.gif">';
				}
			}
			elseif($lists[$k]['status'] == challenge::PK_STATUS_REFUSE)
			{
				$img = 'zhanshuRefuse.gif';
				$lists[$k]['result'] = '<img src="source/plugin/bbsgame/images/3/'.$img.'">';
			}
		}
		msg("got_pk",array("list"=>$lists,"ret"=>1),false,true);
	}
	else {
		msg("null",array("ret"=>1),false,true);
	}

}

function getPKAll() {
   	$strSQL = "SELECT gid, name FROM ng_game WHERE installed = 1";
	$query = DB::query($strSQL);
	while( $result = DB::fetch($query) ) {
		$arr[] = $result;
	}
	return $arr;
}

function getPkInfo($pk_id) {
	$strSQL = "SELECT * FROM ng_game_pk WHERE id = {$pk_id} LIMIT 1";
	$query = DB::query($strSQL);
	while( $result = DB::fetch($query) ) {
		$arr = $result;
	}
	return $arr;
}

function getReplyCount($gid) {
	$strSQL = "SELECT count(gid) AS count FROM ng_game_reply WHERE validate = 1 AND gid = {$gid}";
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
	return $result['count'];
}

function getReplyList($gid, $limit = "LIMIT 10", $order = "ORDER BY ctime DESC") {
	global $_G;
	$strSQL = "SELECT r.*,m.username FROM ng_game_reply r 
						LEFT JOIN {$_G['config']['db'][1]['tablepre']}common_member m ON m.uid = r.uid 
						where r.gid = {$gid} AND r.validate = 1
						$order {$limit}";
	$query = DB::query($strSQL);
	while( $result = DB::fetch($query) ) {
		$result['ctime'] = date("Y-m-d H:i:s", $result['ctime']);
		$result['content'] = preg_replace('/\[S:([^[]*)\]/', "<img src='source/plugin/bbsgame/images/pic/$1'>",$result['content']);
		$arr[] = $result;
	}
	return $arr;
}

//记录最高得分
function pkRecord( $pk_id, $score ,$userType = 'ACT', $uid = 0)
{
	if( $userType == 'ACT') {
		if (empty($uid)) throw new challengeException("\$uid = $uid; is empty");
		$strSQL = "UPDATE ng_game_pker SET act_score='{$score}' WHERE pkid = {$pk_id} AND act_uid = {$uid} AND act_score < {$score}";
		$query = DB::query($strSQL);
	}
	else{
		$strSQL = "UPDATE ng_game_pker SET def_score='{$score}' WHERE pkid = {$pk_id} AND def_score < {$score}";
		$query = DB::query($strSQL);
	}
	return true;
}

function getUserMaxScore($uid, $gid) {
	$strSQL = "SELECT MAX(score) FROM ng_game_credit WHERE gid = {$gid} AND uid=".S::sqlEscape($uid);
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
    return $result;
}

// 获得用户积分
function getUsercredit($uid, $credit) {
	global $_G;
	$strSQL = "SELECT {$credit} FROM {$_G['config']['db'][1]['tablepre']}common_member_count WHERE uid = {$uid}";
	$query = DB::query($strSQL);
	$result = DB::fetch($query);
    return $result[$credit];
}

function  getUserInfoWithUid($uid) {
	global $_G;
	
	$strSQL = "SELECT * FROM {$_G['config']['db'][1]['tablepre']}common_member WHERE uid = {$uid}";
	$query = DB::query($strSQL);
	$user = DB::fetch($query);
	
	return $user;
}

function  getUserInfoWithUsername($user) {
	global $_G;
	
	$strSQL = "SELECT * FROM {$_G['config']['db'][1]['tablepre']}common_member WHERE username = '{$user}'";
	$query = DB::query($strSQL);
	$user = DB::fetch($query);
	
	return $user['uid'];
}

function logQueryCache($file, $sql, $mode) {

	writeover($file, $sql, $mode);
}
?>