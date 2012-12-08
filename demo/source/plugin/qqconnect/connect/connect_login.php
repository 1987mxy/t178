<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_login.php 30537 2012-06-01 07:11:25Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = !empty($_GET['op']) ? $_GET['op'] : '';
if(!in_array($op, array('init', 'callback', 'change'))) {
	showmessage('undefined_action');
}

$referer = dreferer();

try {
	$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
} catch(Exception $e) {
	showmessage('qqconnect:connect_app_invalid');
}
// debug 获取未授权的request token
if($op == 'init') {

	// debug 通知解绑，论坛侧保持原绑定关系，让用户可以重新授权分享
	if($_G['member']['conisbind'] && $_GET['reauthorize']) {
		if($_GET['formhash'] == FORMHASH) {
			$connectService->connectMergeMember();
			$connectService->connectUserUnbind();
		} else {
			showmessage('submit_invalid');
		}
	}

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	// debug 请求用户未授权的tmp token
	try {
		$response = $connectOAuthClient->connectGetRequestToken();
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	$request_token = $response['oauth_token'];
	$request_token_secret = $response['oauth_token_secret'];

	// debug 将未授权的oauth_token和oauth_token_secret记到cookie中
	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	// debug 引导用户至应用授权页
	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']) . (!empty($_GET['isqqshow']) ? '&isqqshow=yes' : '');
	$redirect = $connectOAuthClient->getOAuthAuthorizeURL($request_token, $callback);

	// 手机用户增加source参数
	if(defined('IN_MOBILE') || $_GET['oauth_style'] == 'mobile') {
		$redirect .= '&oauth_style=mobile';
	}

	dheader('Location:' . $redirect);

// debug Callback处理
} elseif($op == 'callback') {

	$params = $_GET;

	// debug 在论坛将页面重定向到connect进行登录授权前和connect返回到论坛的callback页后，IE9用了兼容性视图来接收callback，这导致之前的cookie无法取到了
	// debug 这个操作就是重新载入一下本页面使之回到正常视图，目的是解决IE9兼容视图拿不到cookie
	if(!isset($params['receive'])) {
		$utilService = Cloud::loadClass('Service_Util');
		echo '<script type="text/javascript">setTimeout("window.location.href=\'connect.php?receive=yes&'.str_replace("'", "\'", $utilService->httpBuildQuery($_GET, '', '&')).'\'", 1)</script>';
		exit;
	}

	try {
		$response = $connectOAuthClient->connectGetAccessToken($params, $_G['cookie']['con_request_token_secret']);
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_access_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	$conuin = $response['oauth_token'];
	$conuinsecret = $response['oauth_token_secret'];
	// debug openid统一转成大写
	$conopenid = strtoupper($response['openid']);
	if(!$conuin || !$conuinsecret || !$conopenid) {
		showmessage('qqconnect:connect_get_access_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	// debug 黑名单
	loadcache('connect_blacklist');
	if(in_array($conopenid, array_map('strtoupper', $_G['cache']['connect_blacklist']))) {
		$change_qq_url = $_G['connect']['discuz_change_qq_url'];
		showmessage('qqconnect:connect_uin_in_blacklist', $referer, array('changeqqurl' => $change_qq_url));
	}

	// debug 登陆成功后返回的地址
	$referer = $referer && (strpos($referer, 'logging') === false) && (strpos($referer, 'mod=login') === false) ? $referer : 'index.php';

	// debug 旧版Connect的用户uin，只有旧版用户过来才会有此参数
	if($params['uin']) {
		$old_conuin = $params['uin'];
	}

	// debug 用户资料是否回传Connect
	$is_notify = true;

	// debug QC默认设置项
	$conispublishfeed = $conispublisht = 1;

	// debug 用户授权获取个人资料
	// debug 用户授权发Feed和分享
	$is_user_info = 1;
	$is_feed = 1;

	$user_auth_fields = 1;

	// debug 种Cookies
	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', $conopenid, $cookie_expires);

	$connect_member = array();
	// debug 在旧版connect与论坛是传递uin来查绑定关系，所以common_member_connect表有一个conuin字段，新版虽然还用这个字段，但里面存的已经是access token了
	// debug 不存在旧版用户绑定关系的话，再用access token差绑定关系（新版程序是用access token查绑定关系的，这点与旧版不同）
	$fields = array('uid', 'conuin', 'conuinsecret', 'conopenid');
	if($old_conuin) {
		// debug 获取旧QC绑定用户绑定关系
		$connect_member = C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($old_conuin, $fields);
		// $connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conuin='$old_conuin'");
	}
	if(empty($connect_member)) {
		// debug 获取新QC绑定用户绑定关系
		$connect_member = C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($conopenid, $fields);
		// $connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conopenid='$conopenid'");
	}
	if($connect_member) {
		//$member = DB::fetch_first("SELECT uid, conisbind FROM ".DB::table('common_member')." WHERE uid='$connect_member[uid]'");
		$member = getuserbyuid($connect_member['uid']);
		if($member) {
			if(!$member['conisbind']) {
				C::t('#qqconnect#common_member_connect')->delete($connect_member['uid']);
				// DB::delete('common_member_connect', array('uid' => $connect_member['uid']));
				unset($connect_member);
			} else {
				$connect_member['conisbind'] = $member['conisbind'];
			}
		} else {
			C::t('#qqconnect#common_member_connect')->delete($connect_member['uid']);
			// DB::delete('common_member_connect', array('uid' => $connect_member['uid']));
			unset($connect_member);
		}
	}

	// debug 当一个解绑过的QQ号再次在论坛绑定时，由于网络原因可能connect没有接收到，即QQ号在论坛是绑定状态在connect是解绑状态
	// debug 为了解决这个问题，在用QQ登录时connect会为解绑状态的QQ号传一个is_unbind字段回来，论坛看到这个字段就知道它和connect的状态不同步了
	// debug 就在这里种个cookie，用js再通知一下connect，这个QQ号是registerbind（QQ号登录后绑定了一个论坛账号，connect就会把这个QQ号状态改为isbind）
	$connect_is_unbind = $params['is_unbind'] == 1 ? 1 : 0;
	if($connect_is_unbind && $connect_member && !$_G['uid'] && $is_notify) {
		dsetcookie('connect_js_name', 'user_bind', 86400);
		dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'registerbind'))), 86400);
	}

	// debug 先用论坛账号登录，然后再点击绑定QQ
	if($_G['uid']) {

		// debug 当前使用的QQ号已经绑有一个论坛账号，且这个论坛账号不是当前登录论坛的账号
		if($connect_member && $connect_member['uid'] != $_G['uid']) {
			showmessage('qqconnect:connect_register_bind_uin_already', $referer, array('username' => $_G['member']['username']));
		}

		$isqqshow = !empty($_GET['isqqshow']) ? 1 : 0;

		$current_connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
		// $current_connect_member = DB::fetch_first("SELECT conopenid FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");
		if($_G['member']['conisbind'] && $current_connect_member['conopenid']) {
			// debug 当前论坛登录者已经绑了另一个QQ号了，无法再绑定当前这个QQ号
			// 数据库里面openid转成大写兼容之前版本
			if(strtoupper($current_connect_member['conopenid']) != $conopenid) {
				showmessage('qqconnect:connect_register_bind_already', $referer);
			}
			//debug 更新uin
			C::t('#qqconnect#common_member_connect')->update($_G['uid'],
				array(
					'conuin' => $conuin,
					'conuinsecret' => $conuinsecret,
					'conopenid' => $conopenid,
					'conisregister' => 0,
					'conisfeed' => 1,
					'conisqqshow' => $isqqshow,
				)
			);
			// DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conisregister='0', conisfeed='1', conisqqshow='$isqqshow' WHERE uid='$_G[uid]'");

		} else { // debug 当前登录的论坛账号并没有绑定任何QQ号，则可以绑定当前的这个QQ号
			if(empty($current_connect_member)) {
				C::t('#qqconnect#common_member_connect')->insert(
					array(
						'uid' => $_G['uid'],
						'conuin' => $conuin,
						'conuinsecret' => $conuinsecret,
						'conopenid' => $conopenid,
						'conispublishfeed' => 1,
						'conispublisht' => 1,
						'conisregister' => 0,
						'conisfeed' => 1,
						'conisqqshow' => $isqqshow,
					)
				);
				// DB::query("INSERT INTO ".DB::table('common_member_connect')." (uid, conuin, conuinsecret, conopenid, conispublishfeed, conispublisht, conisregister, conisfeed, conisqqshow) VALUES ('$_G[uid]', '$conuin', '$conuinsecret', '$conopenid', '1', '1', '0', '1', '$isqqshow')");
			} else {
				C::t('#qqconnect#common_member_connect')->update($_G['uid'],
					array(
						'conuin' => $conuin,
						'conuinsecret' => $conuinsecret,
						'conopenid' => $conopenid,
						'conispublishfeed' => 1,
						'conispublisht' => 1,
						'conisregister' => 0,
						'conisfeed' => 1,
						'conisqqshow' => $isqqshow,
					)
				);
				// DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conispublishfeed='1', conispublisht='1', conisregister='0', conisfeed='1', conisqqshow='$isqqshow' WHERE uid='$_G[uid]'");
			}
			C::t('common_member')->update($_G['uid'], array('conisbind' => '1'));

			// 绑定后删除QQ互联游客用户
			C::t('#qqconnect#common_connect_guest')->delete($conopenid);
		}

		// debug 用户绑定通知Connect
		if($is_notify) {
			dsetcookie('connect_js_name', 'user_bind', 86400);
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'loginbind'))), 86400);
		}
		dsetcookie('connect_login', 1, 31536000);
		dsetcookie('connect_is_bind', '1', 31536000);
		dsetcookie('connect_uin', $conopenid, 31536000);
		dsetcookie('stats_qc_reg', 3, 86400);
		if($is_feed) {
			dsetcookie('connect_synpost_tip', 1, 31536000);
		}

		// debug 记录QC用户绑定
		C::t('#qqconnect#connect_memberbindlog')->insert(
			array(
				'uid' => $_G['uid'],
				'uin' => $conopenid,
				'type' => 1,
				'dateline' => $_G['timestamp'],
			)
		);
		// DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$_G[uid]', '$conopenid', '1', '$_G[timestamp]')");

		showmessage('qqconnect:connect_register_bind_success', $referer);

	// debug 未登录用户
	} else {

		if($connect_member) { // debug 此分支是用户直接点击QQ登录，并且这个QQ号已经绑好一个论坛账号了，将直接登进论坛了
			// debug 登录更新uin
			C::t('#qqconnect#common_member_connect')->update($connect_member['uid'],
				array(
					'conuin' => $conuin,
					'conuinsecret' => $conuinsecret,
					'conopenid' => $conopenid,
					'conisfeed' => 1,
				)
			);
			// DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conisfeed='1' WHERE uid='$connect_member[uid]'");

			$params['mod'] = 'login';
			connect_login($connect_member);

			loadcache('usergroups');
			$usergroups = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle']);

//			DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."' WHERE uid='$connect_member[uid]'");
			C::t('common_member_status')->update($connect_member['uid'], array('lastip'=>$_G['clientip'], 'lastvisit'=>TIMESTAMP, 'lastactivity' => TIMESTAMP));
			$ucsynlogin = '';
			if($_G['setting']['allowsynlogin']) {
				loaducenter();
				$ucsynlogin = uc_user_synlogin($_G['uid']);
			}

			dsetcookie('stats_qc_login', 3, 86400);
			showmessage('login_succeed', $referer, $param, array('extrajs' => $ucsynlogin));

		} else { // debug 此分支是用户直接点击QQ登录，并且这个QQ号还未绑定任何论坛账号，将将跳转到一个新页引导用户注册个新论坛账号或绑一个已有的论坛账号

			// 快速登录代码，不再要求注册帐号
			$auth_hash = authcode($conopenid, 'ENCODE');
			$insert_arr = array(
				'conuin' => $conuin,
				'conuinsecret' => $conuinsecret,
				'conopenid' => $conopenid,
			);

			$connectGuest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
			if ($connectGuest['conqqnick']) {
				$insert_arr['conqqnick'] = $connectGuest['conqqnick'];
			} else {
				try {
					$connectOAuthClient = Cloud::loadClass('Service_Client_ConnectOAuth');
					$connectUserInfo = $connectOAuthClient->connectGetUserInfo($conopenid, $conuin, $conuinsecret);
					if ($connectUserInfo['nickname']) {
						$connectUserInfo['nickname'] = strip_tags($connectUserInfo['nickname']);
						$insert_arr['conqqnick'] = $connectUserInfo['nickname'];
					}
				} catch(Exception $e) {
				}
			}

			if ($insert_arr['conqqnick']) {
				dsetcookie('connect_qq_nick', $insert_arr['conqqnick'], 86400);
			}

			C::t('#qqconnect#common_connect_guest')->insert($insert_arr, false, true);

			dsetcookie('con_auth_hash', $auth_hash, 86400);
			dsetcookie('connect_js_name', 'guest_ptlogin', 86400);
			// 腾讯分析快速登录cookie
			dsetcookie('stats_qc_login', 4, 86400);

			$utilService = Cloud::loadClass('Service_Util');

			// 手机客户端show_message
			$refererParams = explode('/', $referer);
			$mobileId = $refererParams[count($refererParams) - 1];

			if (substr($mobileId, 0, 7) == 'Mobile_') {
				showmessage('login_succeed', $referer);
			} else {
				$utilService->redirect($referer);
			}

			/**
			 * // debug 为避免二次请求access token
			 * // debug 将access token加密后，传给注册程序
			 * $encode[] = authcode($conuin, 'ENCODE');
			 * $encode[] = authcode($conuinsecret, 'ENCODE');
			 * $encode[] = authcode($conopenid, 'ENCODE');
			 * $encode[] = authcode($user_auth_fields, 'ENCODE');
			 * $auth_hash = authcode(implode('|', $encode), 'ENCODE');
			 * // debug 加密串种Cookie
			 * dsetcookie('con_auth_hash', $auth_hash);

			 * unset($params['op']);
			 * $params['mod'] = 'register';
			 * $params['referer'] = $referer;
			 * $params['con_auth_hash'] = $auth_hash;

			 * $utilService = Cloud::loadClass('Service_Util');
			 * $redirect = 'connect.php?'.$utilService->httpBuildQuery($params, '', '&');
			 * $utilService->redirect($redirect);
			 */
		}
	}

// debug 更换QQ账号重登录
} elseif($op == 'change') {
	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	// debug 请求用户未授权的tmp token
	try {
		$response = $connectOAuthClient->connectGetRequestToken();
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	$request_token = $response['oauth_token'];
	$request_token_secret = $response['oauth_token_secret'];

	// debug 将未授权的oauth_token和oauth_token_secret记到cookie中
	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	// debug 引导用户至应用授权页
	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']);
	$redirect = $connectOAuthClient->getOAuthAuthorizeURL($request_token, $callback);

	// 手机用户增加source参数
	if(defined('IN_MOBILE') || $_GET['oauth_style'] == 'mobile') {
		$redirect .= '&oauth_style=mobile';
	}

	dheader('Location:' . $redirect);
}

function connect_login($connect_member) {
	global $_G;

	//$member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$connect_member[uid]'");
	if(!($member = getuserbyuid($connect_member['uid'], 1))) {
		return false;
	} else {
		if(isset($member['_inarchive'])) {
			C::t('common_member_archive')->move_to_master($member['uid']);
		}
	}

	require_once libfile('function/member');
	$cookietime = 1296000;
	setloginstatus($member, $cookietime);

	dsetcookie('connect_login', 1, $cookietime);
	dsetcookie('connect_is_bind', '1', 31536000);
	dsetcookie('connect_uin', $connect_member['conopenid'], 31536000);
	return true;
}

function getErrorMessage($errroCode) {
	$str = sprintf('connect_error_code_%d', $errroCode);

	return lang('plugin/qqconnect', $str);
}
