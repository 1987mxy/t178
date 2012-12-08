<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: connect_feed.php 31459 2012-08-30 07:05:37Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// formhash 处理
if (trim($_GET['formhash']) != formhash()) {
	exit('Access Denied');
}

$params = $_GET;
$op = !empty($_GET['op']) ? $_GET['op'] : '';
$action = !empty($_GET['action']) ? $_GET['action'] : 'thread';
if (!in_array($op, array('new'))) {
	$connectService->connectJsOutputMessage('', 'undefined_action', 1);
}

if ($op == 'new') {

	if ($action == 'thread') {
		// debug 帖子id
		$tid = trim(intval($_GET['thread_id']));
		if (empty($tid)) {
			$connectService->connectJsOutputMessage('', 'connect_thread_id_miss', 1);
		}

		$connectService->connectMergeMember();

		//$posttable = getposttablebytid($tid);
		//$post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid = '$tid' AND first='1' AND invisible='0'");
		$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid, 0);
		//$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid = '$tid' AND displayorder >= 0");
		$thread = C::t('forum_thread')->fetch_by_tid_displayorder($tid, 0);
		$feedlog = C::t('#qqconnect#connect_feedlog')->fetch_by_tid($thread['tid']);
		// 如果当前用户与作者不是同一个人
		if ($_G['uid'] != $thread['authorid']) {
			$connectService->connectJsOutputMessage('', 'connect_about', 2);
		}
		// 如果没有需要发送的标记，不发送
		if (!getstatus($thread['status'], 7) && !getstatus($thread['status'], 8)) {
			$connectService->connectJsOutputMessage('', 'connect_about', 2);
		}

		// 发送次数超过 5 次，距离发帖时间超过半个小时，不再发送
		if ($feedlog['publishtimes'] >= 5 || $_G['timestamp'] - $thread['dateline'] > 1800) {
			$connectService->connectJsOutputMessage('', 'connect_about', 2);
		}

		// debug feed的类型 1 QZone、2 t、3 QZone + t
		$f_type = trim(dintval($_GET['type']));

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
		$html_content = preg_replace('/(&nbsp;)+/', ' ', $html_content);

		if($_G['setting']['rewritestatus'] && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
			$url = rewriteoutput('forum_viewthread', 1, $_G['siteurl'], $tid);
		} else {
			$url = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid;
		}

		$qzone_params = array(
			'title' => $thread['subject'],
			'url' => $url,
			'summary' => $html_content,
			'nswb' => '1', // 不自动同步到微博
		);

		$_t_content = lang('plugin/qqconnect', 'connect_feed_iam');
		$_t_content .= '#' . cutstr($_G['setting']['bbname'], 20,'') . '#';
		$_t_content .= lang('plugin/qqconnect', 'connect_feed_published_thread', array('subject' => cutstr($thread['subject'], 120)));
		$_t_content .= cutstr(strip_tags($html_content), 80);
		$_t_content .= ' ' . $url;

		$t_params = array(
		'content' => $_t_content,
		);

		// debug 注意过滤掉非图片附件，图片附件只发前一张
		if($attach_images && is_array($attach_images)) {
			$attach_image = array_shift($attach_images);
			$qzone_params['images'] = $attach_image['big'];
			$t_params['pic'] = $attach_image['path'];
			$t_params['remote'] = $attach_image['remote'];
		}

		$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
		$feed_succ = $weibo_succ = false;
		// 推送到空间
		if(getstatus($f_type, 1)) {
			try {
				$response = $connectOAuthClient->connectAddShare($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $qzone_params);

				$f_type = setstatus(1, 0, $f_type);
				// 返回代码正确
				if ($response['ret'] == 0) {
					$feed_succ = true;
				}
			} catch(Exception $e) {
				if($e->getCode()) {
					$f_type = setstatus(1, 0, $f_type);
					$shareErrorCode = $e->getCode();
				}
				$feed_succ = false;
			}
		}
		// 推送到微博
		if(getstatus($f_type, 2)) {
			try {
				if ($t_params['pic']) {
					$method = 'connectAddPicT';
				} else {
					$method = 'connectAddT';
				}

				$response = $connectOAuthClient->$method($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $t_params);
				// 推送到微博成功产生微博id， 加入到微博回流的表中
				if($response['data']['id']) {
					if($_G['setting']['connect']['t']['reply'] && $thread['tid'] && !$thread['closed'] && !getstatus($thread['status'], 3) && empty($_G['forum']['replyperm'])) {
						$memberConnect = C::t('#qqconnect#common_member_connect')->fetch($thread['authorid']);
						$conopenid = $memberConnect['conopenid'];
						// $conopenid = DB::result_first("SELECT conopenid FROM ".DB::table('common_member_connect')." WHERE uid='".$thread['authorid']."'");
						//DB::insert('connect_tthreadlog', array(
						C::t('#qqconnect#connect_tthreadlog')->insert(array(
							'twid' => $response['data']['id'],
							'tid' => $tid,
							'conopenid' => $conopenid,
							'pagetime' => 0,
							'lasttwid' => '0',
							'nexttime' => $_G['timestamp'] + 30 * 60,
							'updatetime' => 0,//note 最后更新时间
							'dateline' => $_G['timestamp'],
						));
					}
				}

				$f_type = setstatus(2, 0, $f_type);
				// 返回代码正确
				if ($response['ret'] == 0) {
					$weibo_succ = true;
				}
			} catch(Exception $e) {
				if($e->getCode()) {
					$f_type = setstatus(2, 0, $f_type);
					$weiboErrorCode = $e->getCode();
				}
				$weibo_succ = false;
			}
		}

		// 推送成功，修改 thread 表的状态位
		$thread_status = $thread['status'];
		$feedlog_status = $feedlog['status'];
		if ($feed_succ) {
			$thread_status = setstatus(7, 0, $thread_status);
			// 已推送标记
			$feedlog_status = setstatus(2, 1, $feedlog_status);
			// 不需要推送标记
			$feedlog_status = setstatus(1, 0, $feedlog_status);
		}
		if ($weibo_succ) {
			$thread_status = setstatus(8, 0, $thread_status);
			// 标记已推送到微博
			$thread_status = setstatus(14, 1, $thread_status);
			// 已推送标记
			$feedlog_status = setstatus(4, 1, $feedlog_status);
			// 不需要推送标记
			$feedlog_status = setstatus(3, 0, $feedlog_status);
		}
		// 推送成功，更新表
		if ($feed_succ || $weibo_succ) {
			C::t('#qqconnect#connect_feedlog')->update_by_tid($thread['tid'],
				array(
					'status' => $feedlog_status,
					'lastpublished' => $_G['timestamp'],
					'publishtimes' => $feedlog['publishtimes'] + 1,
				));
			C::t('forum_thread')->update($thread['tid'], array('status' => $thread_status));
		}

		// debug 所有有错误码的失败均不重试
		if(!$shareErrorCode && !$weiboErrorCode) {
			$connectService->connectJsOutputMessage(lang('plugin/qqconnect', 'connect_feed_sync_success'), '', 0);
		} else {
			// // debug TODO 重试次数也要限制，不能无限重试
			// if($f_type > 0) {
				// dsetcookie('connect_js_name', 'feed_resend');
				// dsetcookie('connect_js_params', base64_encode(serialize(array('type' => $f_type, 'thread_id' => $tid, 'ts' => TIMESTAMP))), 86400);
			// }
			// 推送失败，更新 feedlog 表
			C::t('#qqconnect#connect_feedlog')->update_by_tid($thread['tid'],
				array(
					'lastpublished' => $_G['timestamp'],
					'publishtimes' => $feedlog['publishtimes'] + 1,
				));
			$connectService->connectJsOutputMessage('', '', $shareErrorCode.'|'.$weiboErrorCode);
		}
	} elseif ($action == 'post') {

		$tid = trim(intval($_GET['thread_id']));
		$pid = trim(intval($_GET['post_id']));

		if (empty($pid)) {
			$connectService->connectJsOutputMessage('', 'connect_thread_id_miss', 1);
		}

		$thread = C::t('forum_thread')->fetch_by_tid_displayorder($tid, 0);
		$post = C::t('forum_post')->fetch('tid:'.$tid, $pid);

		if (empty($thread) || empty($post)) {
			$connectService->connectJsOutputMessage('', 'connect_thread_id_miss', 1);
		}

		if ($_G['uid'] != $post['authorid']) {
			$connectService->connectJsOutputMessage('', 'connect_about', 2);
		}

		$feedlog = C::t('#qqconnect#connect_postfeedlog')->fetch_by_pid($pid);
		// 不存在的记录说明不需要同步
		if (!$feedlog || $feedlog['publishtimes'] >= 5 || $_G['timestamp'] - $post['dateline'] > 1800) {
			$connectService->connectJsOutputMessage('', 'connect_about', 2);
		}

		$connectService->connectMergeMember();

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
		$html_content = $connectService->connectParseBbcode($post['message'], $post['fid'], $post['pid'], $post['htmlon'], $attach_images);
		$html_content = preg_replace('/(&nbsp;)+/', ' ', $html_content);

		$url = $_G['siteurl'].'forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid;

		$_t_content = lang('plugin/qqconnect', 'connect_feed_iam');
		$_t_content .= '#' . cutstr($_G['setting']['bbname'], 20,'') . '#';
		$_t_content .= lang('plugin/qqconnect', 'connect_feed_published_post', array('subject' => cutstr($thread['subject'], 120)));
		$_t_content .= cutstr(strip_tags($html_content), 80);
		$_t_content .= ' ' . $url;
		$t_params = array(
		'content' => $_t_content,
		);

		if($attach_images && is_array($attach_images)) {
			$attach_image = array_shift($attach_images);
			$t_params['pic'] = $attach_image['path'];
			$t_params['remote'] = $attach_image['remote'];
		}

		$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
		$weibo_succ = false;

		try {
			if ($t_params['pic']) {
				$method = 'connectAddPicT';
			} else {
				$method = 'connectAddT';
			}

			$response = $connectOAuthClient->$method($_G['member']['conopenid'], $_G['member']['conuin'], $_G['member']['conuinsecret'], $t_params);

			if ($response['ret'] == 0) {
				$weibo_succ = true;
			}
		} catch(Exception $e) {
			if($e->getCode()) {
				$weiboErrorCode = $e->getCode();
			}
			$weibo_succ = false;
		}

		if ($weibo_succ) {
			$feedlog_status = $feedlog['status'];
			$feedlog_status = setstatus(4, 1, $feedlog_status);
			$feedlog_status = setstatus(3, 0, $feedlog_status);
			C::t('#qqconnect#connect_postfeedlog')->update_by_pid($pid,
				array(
					'status' => $feedlog_status,
					'lastpublished' => $_G['timestamp'],
					'publishtimes' => $feedlog['publishtimes'] + 1,
				));
		}

		if(!$weiboErrorCode) {
			dsetcookie('connect_sync_post');
			$connectService->connectJsOutputMessage(lang('plugin/qqconnect', 'connect_feed_sync_success'), '', 0);
		} else {
			C::t('#qqconnect#connect_postfeedlog')->update_by_pid($pid,
				array(
					'lastpublished' => $_G['timestamp'],
					'publishtimes' => $feedlog['publishtimes'] + 1,
				));
			$connectService->connectJsOutputMessage('', '', $weiboErrorCode);
		}
	}
}
