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
// debug ��ȡδ��Ȩ��request token
if($op == 'init') {

	// debug ֪ͨ�����̳�ౣ��ԭ�󶨹�ϵ�����û�����������Ȩ����
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

	// debug �����û�δ��Ȩ��tmp token
	try {
		$response = $connectOAuthClient->connectGetRequestToken();
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	$request_token = $response['oauth_token'];
	$request_token_secret = $response['oauth_token_secret'];

	// debug ��δ��Ȩ��oauth_token��oauth_token_secret�ǵ�cookie��
	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	// debug �����û���Ӧ����Ȩҳ
	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']) . (!empty($_GET['isqqshow']) ? '&isqqshow=yes' : '');
	$redirect = $connectOAuthClient->getOAuthAuthorizeURL($request_token, $callback);

	// �ֻ��û�����source����
	if(defined('IN_MOBILE') || $_GET['oauth_style'] == 'mobile') {
		$redirect .= '&oauth_style=mobile';
	}

	dheader('Location:' . $redirect);

// debug Callback����
} elseif($op == 'callback') {

	$params = $_GET;

	// debug ����̳��ҳ���ض���connect���е�¼��Ȩǰ��connect���ص���̳��callbackҳ��IE9���˼�������ͼ������callback���⵼��֮ǰ��cookie�޷�ȡ����
	// debug �������������������һ�±�ҳ��ʹ֮�ص�������ͼ��Ŀ���ǽ��IE9������ͼ�ò���cookie
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
	// debug openidͳһת�ɴ�д
	$conopenid = strtoupper($response['openid']);
	if(!$conuin || !$conuinsecret || !$conopenid) {
		showmessage('qqconnect:connect_get_access_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	// debug ������
	loadcache('connect_blacklist');
	if(in_array($conopenid, array_map('strtoupper', $_G['cache']['connect_blacklist']))) {
		$change_qq_url = $_G['connect']['discuz_change_qq_url'];
		showmessage('qqconnect:connect_uin_in_blacklist', $referer, array('changeqqurl' => $change_qq_url));
	}

	// debug ��½�ɹ��󷵻صĵ�ַ
	$referer = $referer && (strpos($referer, 'logging') === false) && (strpos($referer, 'mod=login') === false) ? $referer : 'index.php';

	// debug �ɰ�Connect���û�uin��ֻ�оɰ��û������Ż��д˲���
	if($params['uin']) {
		$old_conuin = $params['uin'];
	}

	// debug �û������Ƿ�ش�Connect
	$is_notify = true;

	// debug QCĬ��������
	$conispublishfeed = $conispublisht = 1;

	// debug �û���Ȩ��ȡ��������
	// debug �û���Ȩ��Feed�ͷ���
	$is_user_info = 1;
	$is_feed = 1;

	$user_auth_fields = 1;

	// debug ��Cookies
	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', $conopenid, $cookie_expires);

	$connect_member = array();
	// debug �ھɰ�connect����̳�Ǵ���uin����󶨹�ϵ������common_member_connect����һ��conuin�ֶΣ��°���Ȼ��������ֶΣ����������Ѿ���access token��
	// debug �����ھɰ��û��󶨹�ϵ�Ļ�������access token��󶨹�ϵ���°��������access token��󶨹�ϵ�ģ������ɰ治ͬ��
	$fields = array('uid', 'conuin', 'conuinsecret', 'conopenid');
	if($old_conuin) {
		// debug ��ȡ��QC���û��󶨹�ϵ
		$connect_member = C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($old_conuin, $fields);
		// $connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conuin='$old_conuin'");
	}
	if(empty($connect_member)) {
		// debug ��ȡ��QC���û��󶨹�ϵ
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

	// debug ��һ��������QQ���ٴ�����̳��ʱ����������ԭ�����connectû�н��յ�����QQ������̳�ǰ�״̬��connect�ǽ��״̬
	// debug Ϊ�˽��������⣬����QQ��¼ʱconnect��Ϊ���״̬��QQ�Ŵ�һ��is_unbind�ֶλ�������̳��������ֶξ�֪������connect��״̬��ͬ����
	// debug ���������ָ�cookie����js��֪ͨһ��connect�����QQ����registerbind��QQ�ŵ�¼�����һ����̳�˺ţ�connect�ͻ�����QQ��״̬��Ϊisbind��
	$connect_is_unbind = $params['is_unbind'] == 1 ? 1 : 0;
	if($connect_is_unbind && $connect_member && !$_G['uid'] && $is_notify) {
		dsetcookie('connect_js_name', 'user_bind', 86400);
		dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'registerbind'))), 86400);
	}

	// debug ������̳�˺ŵ�¼��Ȼ���ٵ����QQ
	if($_G['uid']) {

		// debug ��ǰʹ�õ�QQ���Ѿ�����һ����̳�˺ţ��������̳�˺Ų��ǵ�ǰ��¼��̳���˺�
		if($connect_member && $connect_member['uid'] != $_G['uid']) {
			showmessage('qqconnect:connect_register_bind_uin_already', $referer, array('username' => $_G['member']['username']));
		}

		$isqqshow = !empty($_GET['isqqshow']) ? 1 : 0;

		$current_connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
		// $current_connect_member = DB::fetch_first("SELECT conopenid FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");
		if($_G['member']['conisbind'] && $current_connect_member['conopenid']) {
			// debug ��ǰ��̳��¼���Ѿ�������һ��QQ���ˣ��޷��ٰ󶨵�ǰ���QQ��
			// ���ݿ�����openidת�ɴ�д����֮ǰ�汾
			if(strtoupper($current_connect_member['conopenid']) != $conopenid) {
				showmessage('qqconnect:connect_register_bind_already', $referer);
			}
			//debug ����uin
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

		} else { // debug ��ǰ��¼����̳�˺Ų�û�а��κ�QQ�ţ�����԰󶨵�ǰ�����QQ��
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

			// �󶨺�ɾ��QQ�����ο��û�
			C::t('#qqconnect#common_connect_guest')->delete($conopenid);
		}

		// debug �û���֪ͨConnect
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

		// debug ��¼QC�û���
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

	// debug δ��¼�û�
	} else {

		if($connect_member) { // debug �˷�֧���û�ֱ�ӵ��QQ��¼���������QQ���Ѿ����һ����̳�˺��ˣ���ֱ�ӵǽ���̳��
			// debug ��¼����uin
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

		} else { // debug �˷�֧���û�ֱ�ӵ��QQ��¼���������QQ�Ż�δ���κ���̳�˺ţ�������ת��һ����ҳ�����û�ע�������̳�˺Ż��һ�����е���̳�˺�

			// ���ٵ�¼���룬����Ҫ��ע���ʺ�
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
			// ��Ѷ�������ٵ�¼cookie
			dsetcookie('stats_qc_login', 4, 86400);

			$utilService = Cloud::loadClass('Service_Util');

			// �ֻ��ͻ���show_message
			$refererParams = explode('/', $referer);
			$mobileId = $refererParams[count($refererParams) - 1];

			if (substr($mobileId, 0, 7) == 'Mobile_') {
				showmessage('login_succeed', $referer);
			} else {
				$utilService->redirect($referer);
			}

			/**
			 * // debug Ϊ�����������access token
			 * // debug ��access token���ܺ󣬴���ע�����
			 * $encode[] = authcode($conuin, 'ENCODE');
			 * $encode[] = authcode($conuinsecret, 'ENCODE');
			 * $encode[] = authcode($conopenid, 'ENCODE');
			 * $encode[] = authcode($user_auth_fields, 'ENCODE');
			 * $auth_hash = authcode(implode('|', $encode), 'ENCODE');
			 * // debug ���ܴ���Cookie
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

// debug ����QQ�˺��ص�¼
} elseif($op == 'change') {
	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	// debug �����û�δ��Ȩ��tmp token
	try {
		$response = $connectOAuthClient->connectGetRequestToken();
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	$request_token = $response['oauth_token'];
	$request_token_secret = $response['oauth_token_secret'];

	// debug ��δ��Ȩ��oauth_token��oauth_token_secret�ǵ�cookie��
	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	// debug �����û���Ӧ����Ȩҳ
	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']);
	$redirect = $connectOAuthClient->getOAuthAuthorizeURL($request_token, $callback);

	// �ֻ��û�����source����
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
