<?php
/*
* 
* Jun 13, 2012
* GBK
* 2:25:46 PM
* AgudaZaric
* reply.act.php
*/
header("Location:$_SERVER[HTTP_REFERER]&flicker=1#list");
empty($uid) && Showmsg("not_login");
S::gp(array('content',"act","vcode"));
session_start();
if($act == 'add') {
	
	empty($content) && Showmsg("抱歉，内容不能为空");
	empty($vcode) && Showmsg("抱歉，验证码不能为空");
	$_SESSION['VCODE'] != $vcode && Showmsg("抱歉，验证码错误，请重新填写");
	
	$sql = "INSERT INTO ng_game_reply(`uid`, `username`, `userphoto`, `gid`, `title`, `content`, `ctime`)
				VALUES('$uid', '$username', '$icon', '$gid', '$title', ".S::sqlEscape($content).", '$time')";
	DB::query($sql);

	header("Location:$_SERVER[HTTP_REFERER]#list&flicker=1");

}
else if ($act == 'validate') {

	S::gp(array('rid'));

	if( $uid === 1 || in_array($winddb['groupid'],array(3,4,5)) ) {
		//管理员、总版主、论坛版主
		$sql = "UPDATE ng_game_reply SET `validate` = 0 WHERE `gid`=$gid AND `id`=$rid LIMIT 1";
		DB::query($sql);

		Showmsg("屏蔽成功");
	}
	else {
		Showmsg("无此操作权限");
	}

}