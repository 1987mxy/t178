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
	
	empty($content) && Showmsg("��Ǹ�����ݲ���Ϊ��");
	empty($vcode) && Showmsg("��Ǹ����֤�벻��Ϊ��");
	$_SESSION['VCODE'] != $vcode && Showmsg("��Ǹ����֤�������������д");
	
	$sql = "INSERT INTO ng_game_reply(`uid`, `username`, `userphoto`, `gid`, `title`, `content`, `ctime`)
				VALUES('$uid', '$username', '$icon', '$gid', '$title', ".S::sqlEscape($content).", '$time')";
	DB::query($sql);

	header("Location:$_SERVER[HTTP_REFERER]#list&flicker=1");

}
else if ($act == 'validate') {

	S::gp(array('rid'));

	if( $uid === 1 || in_array($winddb['groupid'],array(3,4,5)) ) {
		//����Ա���ܰ�������̳����
		$sql = "UPDATE ng_game_reply SET `validate` = 0 WHERE `gid`=$gid AND `id`=$rid LIMIT 1";
		DB::query($sql);

		Showmsg("���γɹ�");
	}
	else {
		Showmsg("�޴˲���Ȩ��");
	}

}