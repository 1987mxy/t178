<?php

/**
 *	  [Discuz! X] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: spacecp.inc.php 31459 2012-08-30 07:05:37Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$pluginop = !empty($_GET['pluginop']) ? $_GET['pluginop'] : 'config';
if (!in_array($pluginop, array('config', 'share', 'new', 'sync_tthread'))) {
	showmessage('undefined_action');
}
$sh_type = trim(intval($_GET['sh_type']));
$tid = trim(intval($_GET['thread_id']));
$connectService = Cloud::loadClass('Service_Connect');
// debug QC配置
if ($pluginop == 'config') {

	$connectService->connectMergeMember();

	// debug 是否为新版本QC用户
	$_G['connect']['is_oauth_user'] = true;
	if (empty($_G['member']['conuinsecret'])) {
		$_G['connect']['is_oauth_user'] = false;
	}

	$referer = str_replace($_G['siteurl'], '', dreferer());
	if(!empty($_GET['connect_autoshare'])) {
		if(strpos($referer, '?') !== false) {
			$referer .= '&connect_autoshare=1';
		} else {
			$referer .= '?connect_autoshare=1';
		}
	}

	$_G['connect']['loginbind_url'] = $_G['siteurl'].'connect.php?mod=login&op=init&type=loginbind&referer='.urlencode($_G['connect']['referer'] ? $_G['connect']['referer'] : 'index.php');

// debug 显示分享表单
} elseif ($pluginop == 'share') {

	// debug 本地分享代理页
	$_GET['share_url'] = $_G['connect']['discuz_new_share_url'];

	//$posttable = getposttablebytid($tid);
	//$post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid = '$tid' AND first='1' AND invisible='0'");
	$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid, 0);
//	$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid = '$tid' AND displayorder >= 0");
	$thread = C::t('forum_thread')->fetch_by_tid_displayorder($tid, 0);
	$msglower = strtolower($post['message']);
	// 过滤引用
	if(strpos($msglower, '[/quote]') !== FALSE) {
		$post['message'] = preg_replace('/\[quote\].*\[\/quote\](\r\n|\n|\r){0,}/is', '', $post['message']);
	}
	// 过滤视频
	if(strpos($msglower, '[/media]') !== FALSE) {
		$post['message'] = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", '', $post['message']);
	}
	// 过滤Flash
	if(strpos($msglower, '[/flash]') !== FALSE) {
		$post['message'] = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", '', $post['message']);
	}

	$html_content = $connectService->connectParseBbcode($post['message'], $thread['fid'], $post['pid'], $post['htmlon'], $attach_images);
	// debug 发分享图片
	if ($_G['group']['allowgetimage'] && $thread['price'] == 0 && $post['pid']) {
		if ($attach_images && is_array($attach_images)) {
			// debug 只显示三张图片
			$_GET['share_images'] = array_slice($attach_images, 0, 3);

			$attach_images = array();
			foreach ($_GET['share_images'] as $image) {
				$attach_images[] = $image['big'];
			}
			$_GET['attach_image'] = implode('|', $attach_images);
			unset($attach_images);
		}
	}
	$share_message = lang('plugin/qqconnect', 'connect_spacecp_share_a_post', array('bbname' => cutstr($_G['setting']['bbname'], 20,''), 'subject' => cutstr($thread['subject'], 120), 'message' => cutstr(strip_tags(str_replace('&nbsp;', ' ', $html_content)), 80)));
	$share_message = str_replace(array('\'', "\r\n", "\r", "\n"), array('"', '', '', ''), $share_message);
// debug 处理提交分享内容
} elseif ($pluginop == 'new') {
	// hash 验证
	if (trim($_GET['formhash']) != formhash()) {
		showmessage('submit_invalid');
	}

	$sh_type = intval(trim($_POST['sh_type']));
	$tid = intval(trim($_POST['thread_id']));
	$dialog_id = $_POST['dialog_id'];
	//$sync_post = $_POST['sync_post'];

	$connectService->connectMergeMember();

	if($_G['setting']['rewritestatus'] && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
		$url = rewriteoutput('forum_viewthread', 1, $_G['siteurl'], $tid);
	} else {
		$url = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid;
	}

	$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
	$connectService = Cloud::loadClass('Service_Connect');
	if($sh_type == 1 || $sh_type == 3) {

		$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid, 0);
		$summary = $connectService->connectParseBbcode($firstpost['message'], $firstpost['fid'], $firstpost['pid'], $firstpost['htmlon'], $attach_images);

		$qzone_params = array(
			'title' => $_POST['share_subject'],
			'url' => $url,
			'comment' => $_POST['reason'],
			'summary' => $summary,
			'images' => $_POST['attach_image'],
			'nswb' => '1', // 不自动同步到微博
		);

		try {
			$response = $connectOAuthClient->connectAddShare($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $qzone_params);
		} catch(Exception $e) {
			$errorCode = $e->getCode();
		}

		if($errorCode) {
			$code = $errorCode;
			if($errorCode == 41001) { // 用户未授权
				$message = lang('plugin/qqconnect', 'connect_user_unauthorized', array('login_url' => $_G['connect']['login_url'].'&reauthorize=yes&formhash='.FORMHASH));
			} elseif($errorCode == 41003 || $errorCode == 40006) { // access token失效或非法
				$message = lang('plugin/qqconnect', 'connect_share_token_outofdate', array('login_url' => $_G['connect']['login_url']));
			} elseif ($errorCode == 3021) { // 同一账号连续分享了同一链接
				$message = lang('plugin/qqconnect', 'connect_qzone_share_same_url');
			} else {
				$code = 100;
				$message = lang('plugin/qqconnect', 'connect_server_busy');
				$connectService->connectErrlog($code, lang('plugin/qqconnect', 'connect_errlog_server_no_response'));
			}
		} else {
			$code = $response['ret'];
			$message = lang('plugin/qqconnect', 'connect_share_success');
		}
	} elseif($sh_type == 2) {

		$t_params = array(
			'content' => $_POST['reason'],
		);

		$aid = intval($_POST['attach_image_id']);
		if ($aid) {
			$method = 'connectAddPicT';
			$attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid);
			if($attach['remote']) {
				$t_params['pic'] = $_G['setting']['ftp']['attachurl'].'forum/'.$attach['attachment'];
				$t_params['remote'] = true;
			} else {
				$t_params['pic'] = $_G['setting']['attachdir'].'forum/'.$attach['attachment'];
			}
		} else {
			$method = 'connectAddT';
		}

		try {
			$response = $connectOAuthClient->$method($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $t_params);
		} catch(Exception $e) {
			$errorCode = $e->getCode();
		}

		if($errorCode) {
			$code = $errorCode;
			if($errorCode == 41001) { // 用户未授权
				$message = lang('plugin/qqconnect', 'connect_user_unauthorized', array('login_url' => $_G['connect']['login_url'].'&reauthorize=yes&formhash='.FORMHASH));
			} elseif($errorCode == 41003 || $errorCode == 40006) { // access token失效或非法
				$message = lang('plugin/qqconnect', 'connect_share_token_outofdate', array('login_url' => $_G['connect']['login_url']));
			} elseif ($errorCode == 3013) { // 在短时间（一分钟）内发表同样内容的微博
				$message = lang('plugin/qqconnect', 'connect_qzone_weibo_same_content');
			} else if($errorCode == 3020) { // 用户未开通微博
				$message = lang('plugin/qqconnect', 'connect_weibo_account_not_signup');
			} else {
				$code = 100;
				$message = lang('plugin/qqconnect', 'connect_server_busy');
				$connectService->connectErrlog($code, lang('plugin/qqconnect', 'connect_errlog_server_no_response'));
			}
		} else {
			$thread = C::t('forum_thread')->fetch($tid);
			if($response['data']['id'] && $_G['setting']['connect']['t']['reply'] && $thread['tid'] && !$thread['closed'] && !getstatus($thread['status'], 3) && empty($_G['forum']['replyperm'])) {

				//DB::insert('connect_tthreadlog', array(
				C::t('#qqconnect#connect_tthreadlog')->insert(array(
					'twid' => $response['data']['id'],
					'tid' => $tid,
					'conopenid' => $_G['member']['conopenid'],
					'pagetime' => 0,
					'lasttwid' => '0',
					'nexttime' => $_G['timestamp'] + 30 * 60,
					'updatetime' => 0,
					'dateline' => $_G['timestamp'],
				));
			}
			if(!getstatus($thread['status'], 8)) {
				C::t('forum_thread')->update($tid, array('status' => setstatus(8, 1, $thread['status'])));//note 分享也增加推送微博标志
			}
			$code = $response['ret'];
			$message = lang('plugin/qqconnect', 'connect_broadcast_success');
    	}
	}
} elseif($pluginop == 'sync_tthread') {
	// hash 验证
	if (trim($_GET['formhash']) != formhash()) {
		showmessage('submit_invalid');
	}
	if(!$_G['setting']['connect']['t']['reply']) {
		exit;//note 未开启
	}
	$tid = $_GET['tid'];
	$processname = 'connect_tthread_'.$tid.'_cache';
	if(discuz_process::islocked($processname, 600)) {//note 防止并发
		exit;
	}
	$thread = C::t('forum_thread')->fetch($tid);
	if(!$thread || $thread['closed'] == 1 || getstatus($thread['status'], 3) || $thread['displayorder'] < 0 || !empty($_G['forum']['replyperm'])) {
		discuz_process::unlock($processname);
		exit;//note 主题不存在、主题关闭、主题未显示出来或者为抢楼贴时，不处理回流信息
	}

	//$updatetime = DB::result_first("SELECT updatetime FROM ".DB::table('connect_tthreadlog')." WHERE tid='$tid' ORDER BY updatetime DESC LIMIT 1");
	$updatetime = C::t('#qqconnect#connect_tthreadlog')->fetch_max_updatetime_by_tid($tid);
	if($_G['timestamp'] < $updatetime + 10 * 60) {
		discuz_process::unlock($processname);
		exit;//note 10分钟更新一次, 如果可以thread里面加一个字段weiboupdatetime更好了
	}
	//$tthread = DB::fetch_first("SELECT * FROM ".DB::table('connect_tthreadlog')." WHERE tid='$tid' ORDER BY nexttime ASC LIMIT 1");
	$tthread = C::t('#qqconnect#connect_tthreadlog')->fetch_min_nexttime_by_tid($tid);
	if(empty($tthread)) {
		discuz_process::unlock($processname);
		exit;
	}

	$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
	$connectmember = C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($tthread['conopenid']);
	$param = array();
	$param['format'] = 'xml';
	$param['flag'] = '2';
	$param['rootid'] = $tthread['twid'];
	$param['pageflag'] = 2;
	$param['pagetime'] = $tthread['pagetime'];
	$param['reqnum'] = 20;
	$param['twitterid'] = $tthread['lasttwid'];

	try {
		$response = $connectOAuthClient->connectGetRepostList($tthread['conopenid'], $connectmember['conuin'], $connectmember['conuinsecret'], $param);
	} catch(Exception $e) {
		showmessage('qqconnect:server_busy');
	}
	//if($response && $response['status'] == 0 && $response['result']) {
	if($response && $response['ret'] == 0 && $response['data']['info']) {
		//note 处理评论数据

		include_once libfile('function/forum');
		$forum = C::t('forum_forum')->fetch($thread['fid']);
		$pinvisible = $forum['modnewposts'] ? -2 : 0;//note 版块设置审核回帖，则进入审核。用户组的权限不考虑

		$pids = array();
		$i = 0;
		//foreach($response['result'] as $post) {
		$responseinfo = array();
		if(!isset($response['data']['info'][0])) {
			$responseinfo[] = $response['data']['info'];
		} else {
			$responseinfo = $response['data']['info'];
			krsort($responseinfo);
		}
		foreach($responseinfo as $post) {
			//$message = diconv(trim($post['content']), 'UTF-8');
			$message = trim($post['text']);
			//$post['username'] = diconv(trim($post['username']), 'UTF-8');
			$post['username'] = trim($post['name']);
			$post['nick'] = trim($post['nick']);
			$message = preg_replace("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+\s*)/i", '', $message);
			$message = str_replace(explode(' ', lang('plugin/qqconnect', 'connect_reply_filter_smiley')), '', $message);
			if($message) {
				$newmessage = censor($message, null, true);
				if($message != $newmessage) {
					continue;//note 有过滤词直接跳过,不入库
				}
			} else {
				$message = lang('plugin/qqconnect', 'connect_tthread_broadcast');
			}
			if($_G['setting']['connect']['t']['reply_showauthor']) {
				//$message .= lang('plugin/qqconnect', 'connect_tthread_message', array('username' => $post['username'], 'nick' => $post['nick']));
				$message .= '[tthread='.$post['username'].', '.$post['nick'].']'.$post['head'].'[/tthread]';
			}

			$pid = insertpost(array(
				'fid' => $thread['fid'],
				'tid' => $thread['tid'],
				'first' => '0',
				'author' => '',
				'authorid' => '0',
				'subject' => '',
				'dateline' => $_G['timestamp'] + $i,//note 保持微博回流的数据的顺序
				'message' => $message,
				'useip' => '',//note 能不能传过来？
				'invisible' => $pinvisible,
				'anonymous' => '0',
				'usesig' => '0',
				'htmlon' => '1',
				'bbcodeoff' => '0',
				'smileyoff' => '0',
				'parseurloff' => '0',
				'attachment' => '0',
				'status' => 16,//note 再使用一个状态位
			));
			if($pid) {
				$pids[] = $pid;
			}
			$i++;
		}

		//note 更新回复数及最后发表人
		if($pinvisible) {
			updatemoderate('pid', $pids);
			C::t('forum_forum')->update_forum_counter($thread['fid'], 0, 0, count($pids), 1);
		} else {
			$fieldarr = array(
				'lastposter' => array(''),
				'replies' => count($pids),
			);
			if($thread['lastpost'] < $_G['timestamp']) {
				$fieldarr['lastpost'] = array($_G['timestamp']);
			}
			C::t('forum_thread')->increase($tid, $fieldarr);
			$postionid = C::t('forum_post')->fetch_maxposition_by_tid($thread['posttableid'], $tid);
			C::t('forum_thread')->update($tid, array('maxposition' => $postionid));

			$lastpost = "$thread[tid]\t$thread[subject]\t$_G[timestamp]\t".'';
			C::t('forum_forum')->update($thread['fid'], array('lastpost' => $lastpost));
			C::t('forum_forum')->update_forum_counter($thread['fid'], 0, count($pids), count($pids));
			if($forum['type'] == 'sub') {
				C::t('forum_forum')->update($forum['fup'], array('lastpost' => $lastpost));
			}
		}

		//note 同时还要记录最后一条的id和时间
		$setarr['pagetime'] = $post['timestamp'];
		$setarr['lasttwid'] = $post['id'];
		//if(count($response['result']) < $param['req_num']) {
		if(count($responseinfo) < $param['reqnum']) {
			$setarr['nexttime'] = $_G['timestamp'] + 2 * 3600;
		} else {
			$setarr['nexttime'] = $_G['timestamp'] + 30 * 60;
		}
	} else {
		$setarr['nexttime'] = $_G['timestamp'] + 3 * 3600;
	}
	$setarr['updatetime'] = $_G['timestamp'];
	//DB::update('connect_tthreadlog', $setarr, array('twid' => $tthread['twid']));
	C::t('#qqconnect#connect_tthreadlog')->update($tthread['twid'], $setarr);

	discuz_process::unlock($processname);
	exit;
}
