<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
 
$identifier = 'bbsgame';
$operation = $_G['gp_operation'];
define('R_P', dirname(__FILE__));
define('H_P', R_P);
@include_once(H_P.'/require/functions.php');
@include_once(H_P.'/require/security.php');
@include_once(H_P.'/data/bbsgame_config.php');

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

if(!$_POST['submit']) {
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
	showtableheader('资料完善');

	showformheader("plugins&operation=config&do=15&identifier=bbsgame&pmod=admincp");
	$v_check = $v_check_no = '';
	/*showtablerow('', array('width="15%"', 'width="7%"', 'width="78%"'), array('是否开启游戏中心插件', '<input type="radio" name="ifopen" value="1" checked>是<input type="radio" name="ifopen" value="0">否'), '');
	showtablerow('', array('width="15%"', 'width="7%"', 'width="78%"'), array('站长信息管理', '姓名','<input type="text" name="name" value="'.$name.'">*'));
	showtablerow('', array('width="15%"', 'width="7%"', 'width="78%"'), array('', '手机号码','<input type="text" name="phone" value="'.$phone.'">*'));
	showtablerow('', array('width="15%"', 'width="7%"', 'width="78%"'), array('', 'QQ','<input type="text" name="qq" value="'.$qq.'">*'));
	showtablerow('', array('width="15%"', 'width="7%"', 'width="78%"'), array('', '邮箱','<input type="text" name="mail" value="'.$mail.'">*'));
	showtablerow('', array('width="15%"', 'width="7%"', 'width="78%"'), array('站点设置', '网站名称', '<input type="text" name="sitename" value="'.$sitename.'">*'));*/
	$strtab = "";
	$strtab .= "<table><tr>";
	
	$strtab .= "<td width='45%'>";
	$strtab .= "<table>";
	$strtab .= "<tr>
					<td width='28%' align='right'>是否开启游戏中心插件</td>
					<td width='15%'></td>
					<td width='57%'><input type='radio' name='ifopen' value='1'0 checked>是<input type='radio' name='ifopen' value='0'>否</td>
				</tr>";
	$strtab .= "<tr>
					<td width='28%' align='right'>站长信息管理</td>
					<td width='15%' align='right'>姓名</td>
					<td width='57%'><input type='text' name='name' value='{$name}'>*</td>
				</tr>";
	$strtab .= "<tr>
					<td width='28%'></td>
					<td width='15%' align='right'>手机号码</td>
					<td width='57%'><input type='text' name='phone' value='{$phone}'>*</td>
				</tr>";
	$strtab .= "<tr>
					<td width='28%'></td>
					<td width='15%' align='right'>QQ</td>
					<td width='57%'><input type='text' name='qq' value='{$qq}'>*</td>
				</tr>";
	$strtab .= "<tr>
					<td width='28%'></td>
					<td width='15%' align='right'>邮箱</td>
					<td width='57%'><input type='text' name='mail' value='{$mail}'>*</td>
				</tr>";
	$strtab .= "<tr>
					<td width='28%' align='right'>站点设置</td>
					<td width='15%' align='right'>网站名称</td>
					<td width='57%'><input type='text' name='sitename' value='{$sitename}'>*</td>
				</tr>";
	$strtab .= "</table>";
	$strtab .= "</td>";

	$strtab .= "<td width='55%' valign='top'>";
	$strtab .= "<table>";
	$strtab .= "<tr><td>";
	$strtab .= "<table><tr><td>";
	$strtab .= "声明：对于贵方的联系方式，我司只有指定维护人员获取，并保证联系方式仅在公司业务合作中使用，<br />
如用于业务合作以外的非正当行为，或因我司外泄联系方式等造成贵方形成困扰的，我司愿意承担责任。<br />
填写有效的联系方式必要性如下：<br /><br />
1、安装过程中可能存在的一些问题，技术可以有效的支撑；<br />
2、插件内活动奖励可能来源于本站用户，联系以站内名义发放；<br />
3、我司定期会提供无偿的活动方案支持，可根据自身站点选择性作为对外宣传使用；<br />
4、视站点市场推广需求，我司或主动给站点投放宣传活动费用，或站长可向我司申请；<br />
5、对于站点，我司可定向修改一些功能，亦可提供免费空间及为插件外适当的技术支持；<br />
6、我司与贵站点为合作运营，沟通很重要，如有合作中更好的想法也可通过我方平台对外盈利。";
	$strtab .= "</td></tr></table>";
	$strtab .= "</td></tr>";
	$strtab .= "</table>";
	$strtab .= "</td>";
	
	$strtab .= "</tr></table>";
	showtablerow('', array('width="100%"'), array($strtab));
	showsubmit('submit','submit');
	showformfooter();
	showtablefooter();
}
else {
	$pid = $_POST['pid'] ? $_POST['pid']:0;
	$name = $_POST['name'];
	$phone = $_POST['phone'];
	$qq = $_POST['qq'];
	$mail = $_POST['mail'];
	$sitename = $_POST['sitename'];
	$siteurl = $_POST['siteurl'];
	$ifopen = $_POST['ifopen'];
	if(!$name) {
		echo '<font color=red>站长请完善信息：名称</font>';
		exit;
	}
	if(!$phone) {
		echo '<font color=red>站长请完善信息：电话</font>';
		exit;
	}
	if(!$qq) {
		echo '<font color=red>站长请完善信息：QQ</font>';
		exit;
	}
	if(!$mail) {
		echo '<font color=red>站长请完善信息：邮箱</font>';
		exit;
	}
	if(!$sitename) {
		echo '<font color=red>站点名称不能为空</font>';
		exit;
	}
	if(!$siteurl) {
		$siteurl = $_SERVER['SERVER_NAME'];
	}
	if(preg_match("/[a-zA-Z`=\\\\[\];\',\.\/~!@#$%^&*()_+|{}:\"<>?]+/",$phone)) {
		echo '<font color=red>电话号码有误,包含其他字符</font>';
		exit;
	}
	
	if(!file_exists(H_P."/pluginsign.txt")) {
		echo '<font color=red>版本标识丢失，请重新下载安装包</font>';
		exit;
	}

	$post = array(
				'pid'=>"$pid",
				'name' => "$name",
				'phone'=> "$phone",
				'mail'=> "$mail",
				'qq'=>"$qq",
				'siteurl'=> "$siteurl",
				"sitename"=> "$sitename",
				"act"=> 3,
			);

	empty($pid) && $post['plugsign'] = readover(H_P."/pluginsign.txt");

	if($response = submitSiteInfoToNG($post)) {
		$result = json_decode($response, true);
		$init = 1;
	}
	else{
		echo '<font color=red>响应失败，请重试</font>';
		exit;
	}

	if($result['ret'] > 0 ){
		$data = "\$pid=\"{$result['ret']}\";\r\n";
		$data .="\$ifopen=\"{$ifopen}\";\r\n";
		$data .="\$name=\"{$name}\";\r\n";
		$data .="\$phone=\"{$phone}\";\r\n";
		$data .="\$siteurl=\"{$siteurl}\";\r\n";
		$data .="\$sitename=\"{$sitename}\";\r\n";
		$data .="\$qq=\"{$qq}\";\r\n";
		$data .="\$mail=\"{$mail}\";\r\n";
		$data .="\$init=\"{$init}\";\r\n";
		$data .="\$ng_name=\"{$ng_name}\";\r\n";
		$data .="\$db_bbsname=\"{$db_bbsname}\";\r\n";
		$data .="\$pkgame_version=\"{$pkgame_version}\";\r\n";

		$res = writeover(H_P.'/data/bbsgame_config.php',"<?php\r\n".$data."?>","w");
		if($res) echo '<font color=red>资料已完善成功</font>，<a href="admin.php?action=plugins&operation=config&do='.$_GET['do'].'&identifier=bbsgame&pmod=admininstall">进游戏安装界面</a>';
		exit;
	}
	else {
		$result[msg] = unicode_decode($result['msg'], $ng_charset);
		echo "<font color=red>$result[msg]</font>";
		exit;
	}
}
?>