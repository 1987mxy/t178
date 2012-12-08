<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$identifier = 'bbsgame';
$operation = $_G['gp_operation'];
define('R_P', dirname(__FILE__));
define('H_P', R_P);
@include_once(H_P.'/require/functions.php');
@include_once(H_P.'/data/bbsgame_config.php');
@include_once H_P.'/data/jljf_config.php';
@include_once(H_P.'/require/security.php');
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
$act = $_GET['act'];
!$act && $act = 'setstyle';
if($act == 'setstyle') {
	require_once(H_P.'/data/styleset_config.php');
	$bbsgame_etra = "<br><font color=red>说明</font>：风格管理，如果想将游戏以指定风格展示在前台，需先将此游戏安装。" .
					"<br>&nbsp;&nbsp;&nbsp;模板二：修改网站根目录下global.php 在pwOutPut();前加上	@require_once R_P.'hack/bbsgame/template/float.htm';";
	//展示管理
	$style = $_GET['style'];
	if($style) {
		require_once(H_P.'/data/explorer_config.php');
		$query = DB::query("SELECT * FROM $table_type  WHERE 1 ");

		while($result = DB::fetch($query)) {
			$gameTypes[] = $result;
			$types[] = $result['id'];
		}
		
		if(!empty($types)) {

			$inTypes = implode(',', $types);
			$query = DB::query("SELECT gid FROM {$table_game} WHERE type in($inTypes)");
			while($result = DB::fetch($query)) {
				$gids[] = $result['gid'];
			}

			$inGids = implode(',', $gids);
			$query = DB::query("SELECT * FROM {$table_game} WHERE gid in({$inGids}) AND installed = 1 ORDER BY type");
			while($result = DB::fetch($query)) {
				$result['describe'] = subString($result['describe'], 25, $ng_charset);
				$lists[] = $result;
			}

		}
		else {
			$lists = '';
		}
	}

	$issty1open == "1" ? $issty1open1 = 'checked' : $issty1open0 = 'checked';
	$issty2open == "1" ? $issty2open1 = 'checked' : $issty2open0 = 'checked';

	$styleset = 'current';
	include template('bbsgame:setstyle');
	exit;
}
else if($act=='savestyle') {
	
	InitGP(array("issty1open", "issty2open"),'P',2);

	$data ="<?php\r\n".
	"\$version=\"$version\";\r\n".
	"\$issty1open=\"$issty1open\";\r\n".
	"\$issty2open=\"$issty2open\";\r\n".
	"?>";
	writeover(H_P.'/data/styleset_config.php',$data,"w");
	exit;
} 
else if($act == 'tpl') {//visible view management
		require_once(H_P.'/data/explorer_config.php');
		$gids = $_GET['gids'];
		$tids = $_GET['tids'];
		$name = $_GET['name'];
		$classname = $_GET['classname'];
		$iden = $_GET['iden'];
		$step = $_GET['step'];
		if(empty($step)) {
			empty($classname) && msg("empty_modeclass",array("ret"=>0));
			if( $classname == 'float' ) {
				$mode[$classname] = null;
				$query = DB::query("SELECT g.*,s.url as shellUrl FROM $table_game g  LEFT JOIN $table_shell s ON g.shellid = s.id WHERE g.gid IN($gids)");
				while($result = DB::fetch($query)) {
					$result['describe'] = subString($result['describe'], 45, $ng_charset);
					$result['img'] = PIC_HOST.'/'.$result['img'];
					$mode[$classname][] = $result;
				}
			}
			else {

				empty($name) && msg("empty_modename",array("ret"=>0));
				empty($iden) && msg("empty_identifier",array("ret"=>0));

				$mode[$classname][$iden] = null;
				$mode[$classname][$iden]["gids"] = $gids;
				$mode[$classname][$iden]["tids"] = $tids;
				$mode[$classname][$iden]["name"] = $name;
			}

			$data = "<?php\r\n";
			foreach($mode as $k=>$v) {
				$data .= "\$mode[$k] = ".dz_var_export($mode[$k]).";\r\n";
			}
			$data .= "?>";
			writeover(H_P.'/data/explorer_config.php',$data,"w");

			if( $classname =='float') {
				if($db_charset == 'gbk'){
					foreach($mode[$classname] as $k=>$v) {
						$mode[$classname][$k]['name'] = mb_convert_encoding($v['name'], "UTF-8", "GB2312");
						$mode[$classname][$k]['describe'] = mb_convert_encoding($v['describe'], "UTF-8", "GB2312");
					}
				}
			}
			ob_end_clean();
			msg("config_done",array('ret'=>1,'games'=>$mode[$classname]));
			exit;
		}
		else if($step == 'delete') {

			!isset($mode[$classname][$iden]) && msg("success",array("ret"=>1));
			unset($mode[$classname][$iden]);

			$data = "<?php\r\n";
			foreach($mode as $k=>$v) {
				$data .= "\$mode[$k] = ".dz_var_export($mode[$k]).";\r\n";
			}
			$data .= "?>";
			writeover(H_P.'/data/explorer_config.php',$data,"w");
			msg("success",array("ret"=>1));
			exit;
		}

	}
?>