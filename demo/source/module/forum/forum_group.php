<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_group.php 31053 2012-07-12 03:53:04Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/group');
$_G['action']['action'] = 3;
$_G['action']['fid'] = $_G['fid'];
$_G['basescript'] = 'group';

$actionarray = array('join', 'out', 'create', 'viewmember', 'manage', 'index', 'memberlist', 'recommend', 'contribute', 'signing', 'group_join_game', 'group_member_join_game', 'check_group', 'group_relation', 'my_manage');
$action = getgpc('action') && in_array($_GET['action'], $actionarray) ? $_GET['action'] : 'index';
if(in_array($action, array('join', 'out', 'create', 'manage', 'recommend'))) {
	if(empty($_G['uid'])) {
		showmessage('not_loggedin', '', '', array('login' => 1));
	}
}
if(empty($_G['fid']) && $action != 'create') {
	showmessage('group_rediret_now', 'group.php');
}
$first = &$_G['cache']['grouptype']['first'];
$second = &$_G['cache']['grouptype']['second'];
$rssauth = $_G['rssauth'];
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="'.$_G['setting']['bbname'].' - '.$navtitle.'" href="'.$_G['siteurl'].'forum.php?mod=rss&fid='.$_G['fid'].'&amp;auth='.$rssauth."\" />\n") : '';
if($_G['fid']) {
	if($_G['forum']['status'] != 3) {
		showmessage('forum_not_group', 'group.php');
	} elseif($_G['forum']['level'] == -1) {
		showmessage('group_verify', '', array(), array('alert' => 'info'));
	} elseif($_G['forum']['jointype'] < 0 && !$_G['forum']['ismoderator']) {
		showmessage('forum_group_status_off', 'group.php');
	}
	$groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'), 604800);

	$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
	$_G['forum']['banner'] = get_groupimg($_G['forum']['banner']);
	$_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');
	$_G['forum']['posts'] = intval($_G['forum']['posts']);
	$_G['grouptypeid'] = $_G['forum']['fup'];
	$groupuser = C::t('forum_groupuser')->fetch_userinfo($_G['uid'], $_G['fid']);
	
	//调取t178公会信息Moxiaoyong		2012-12-24
	if($groupuser){
		$group_member = C::t('my_group_member')->get_member_info($_G['mygroup']['groupid'], $_G['uid']);
	}
	
	$onlinemember = grouponline($_G['fid'], 1);
	$groupmanagers = $_G['forum']['moderators'];
	$nav = get_groupnav($_G['forum']);
	$groupnav = $nav['nav'];

	$seodata = array('forum' => $_G['forum']['name'], 'first' => $nav['first']['name'], 'second' => $nav['second']['name'], 'gdes' => $_G['forum']['description']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('grouppage', $seodata);
	if(!$navtitle) {
		$navtitle = helper_seo::get_title_page($_G['forum']['name'], $_G['page']).' - '.$_G['setting']['navs'][3]['navname'];
		$nobbname = false;
	} else {
		$nobbname = true;
	}
	if(!$metakeywords) {
		$metakeywords = $_G['forum']['name'];
	}
	if(!$metadescription) {
		$metadescription = $_G['forum']['name'];
	}
	$_G['seokeywords'] = $_G['setting']['seokeywords']['group'];
	$_G['seodescription'] = $_G['setting']['seodescription']['group'];
}

if(in_array($action, array('out', 'viewmember', 'manage', 'index', 'memberlist'))) {
	$status = groupperm($_G['forum'], $_G['uid'], $action, $groupuser);
	if($status == -1) {
		showmessage('forum_not_group', 'group.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	}
	if($action != 'index') {
		if($status == 2) {
			showmessage('forum_group_noallowed', "forum.php?mod=group&fid=$_G[fid]");
		} elseif($status == 3) {
			showmessage('forum_group_moderated', "forum.php?mod=group&fid=$_G[fid]");
		}
	}
}

if(in_array($action, array('index')) && $status != 2) {

	$newuserlist = $activityuserlist = array();
	foreach($groupcache['newuserlist']['data'] as $user) {
		$newuserlist[$user['uid']] = $user;
		$newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
	}

	$activityuser = array_slice($groupcache['activityuser']['data'], 0, 8);
	foreach($activityuser as $user) {
		$activityuserlist[$user['uid']] = $user;
		$activityuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
	}

	$groupviewed_list = get_viewedgroup();

}

$showpoll = $showtrade = $showreward = $showactivity = $showdebate = 0;
if($_G['forum']['allowpostspecial']) {
	$showpoll = $_G['forum']['allowpostspecial'] & 1;
	$showtrade = $_G['forum']['allowpostspecial'] & 2;
	$showreward = isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($_G['forum']['allowpostspecial'] & 4);
	$showactivity = $_G['forum']['allowpostspecial'] & 8;
	$showdebate = $_G['forum']['allowpostspecial'] & 16;
}

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && $showpoll;
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && $showtrade;
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && $showreward;
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && $showactivity;
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && $showdebate;
}
if($action == 'index') {

	$newthreadlist = array();
	if($status != 2) {
		loadcache('forumstick');
		$forumstickytids = '';
		if(isset($_G['cache']['forumstick'][$_G['forum']['fup']])) {
			$forumstickytids = $_G['cache']['forumstick'][$_G['forum']['fup']];
		}
		require_once libfile('function/feed');
		if($forumstickytids) {
			foreach(C::t('forum_thread')->fetch_all_by_tid_or_fid($_G['fid'], $forumstickytids) as $row) {
				$row['dateline'] = dgmdate($row['dateline'], 'd');
				$row['lastpost'] = dgmdate($row['lastpost'], 'u');
				$row['lastposterenc'] = rawurlencode($row['lastposter']);
				$stickythread[$row['tid']] = $row;
			}
		}
		$newthreadlist = getgroupcache($_G['fid'], array('dateline'), 0, 10, 0, 1);
		foreach($newthreadlist['dateline']['data'] as $key => $thread) {
			if(!empty($stickythread) && $stickythread[$thread[tid]]) {
				unset($newthreadlist['dateline']['data'][$key]);
				continue;
			}
			if($thread['closed'] == 1) {
				$newthreadlist['dateline']['data'][$key]['folder'] = 'lock';
			} elseif(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
				$newthreadlist['dateline']['data'][$key]['folder'] = 'new';
			} else {
				$newthreadlist['dateline']['data'][$key]['folder'] = 'common';
			}
		}
		if($stickythread) {
			$newthreadlist['dateline']['data'] = array_merge($stickythread, $newthreadlist['dateline']['data']);
		}
		$groupfeedlist = array();
		if(!IS_ROBOT) {
			$activityuser = array_keys($groupcache['activityuser']['data']);
			if($activityuser) {
				$query = C::t('home_feed')->fetch_all_by_uid_dateline($activityuser);
				foreach($query as $feed) {
					if($feed['friend'] == 0) {
						$groupfeedlist[] = mkfeed($feed);
					}
				}
			}
		}
		
		//公会首页信息Moxiaoyong		2012-12-20
		$group_next_tcp = C::t('forum_grouplevel')->fetch_by_credits($_G['mygroup']['tcp']);
		$_G['mygroup']['next_level_require_tcp'] = $group_next_tcp['creditslower'] - $_G['mygroup']['tcp'];
		$_G['mygroup']['tcp_rank'] = C::t('my_group')->get_tcp_rank($_G['fid']);
		$_G['mygroup']['capital_rank'] = C::t('my_group')->get_capital_rank($_G['fid']);
		$my_groupid = $_G['mygroup']['groupid'];
		$_G['mygroup']['member_contribute_list'] = C::t('my_group_member')->get_member_contribute_list($my_groupid);
		$_G['mygroup']['member_capital_list'] = C::t('my_group_member')->get_member_capital_list($my_groupid);
		$_G['mygroup']['group_games'] = C::t('my_group_game')->get_group_games($my_groupid);
		$friend_groups = C::t('my_group_relation')->get_friend_group($my_groupid);
		foreach( $friend_groups as &$group ){
			$group = $group['group_id_b'];
		}
		$friend_group_fids = C::t('my_group')->groupids2fids($friend_groups);
		$_G['mygroup']['friend_group'] = C::t('forum_forum')->fetch_all_name_by_fid($friend_group_fids);
		
		//获取当前用户的fid Moxiaoyong	2013-01-12
		$user_groupid = C::t('my_group_member')->get_user_groupid($_G['uid']);
		$user_fids = C::t('my_group')->groupids2fids($user_groupid);
		$_G['usergroup']['fid'] = $user_fids[0];
		
		//敌对公会逻辑Moxiaoyong		2013-01-07
		$enemy_groups = C::t('my_group_relation')->get_enemy_group($my_groupid);
		foreach( $enemy_groups as &$group ){
			$group = $group['group_id_b'];
		}
		$enemy_group_fids = C::t('my_group')->groupids2fids($enemy_groups);
		$_G['mygroup']['enemy_group'] = C::t('forum_forum')->fetch_all_name_by_fid($enemy_group_fids);
		
		//获取t178游戏信息Moxiaoyong		2012-12-29
		$_G['mygroup']['games'] = C::t('game_game')->get_all_game_info();
		
	} else {
		$newuserlist = $activityuserlist = array();
		$newuserlist = array_slice($groupcache['newuserlist']['data'], 0, 4);
		foreach($newuserlist as $user) {
			$newuserlist[$user['uid']] = $user;
			$newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
		}
	}

	write_groupviewed($_G['fid']);
	include template('diy:group/group:'.$_G['fid']);

} elseif($action == 'memberlist') {

	$oparray = array('card', 'address', 'alluser');
	$op = getgpc('op') && in_array($_GET['op'], $oparray) ?  $_GET['op'] : 'alluser';
	$page = intval(getgpc('page')) ? intval($_GET['page']) : 1;
	$perpage = 50;
	$start = ($page - 1) * $perpage;

	$alluserlist = $adminuserlist = array();
	$staruserlist = $page < 2 ? C::t('forum_groupuser')->groupuserlist($_G['fid'], 'lastupdate', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';
	$adminlist = $groupmanagers && $page < 2 ? $groupmanagers : array();

	if($op == 'alluser') {
		$alluserlist = C::t('forum_groupuser')->groupuserlist($_G['fid'], 'lastupdate', $perpage, $start, "AND level='4'", '', $onlinemember['list']);
		$multipage = multi($_G['forum']['membernum'], $perpage, $page, 'forum.php?mod=group&action=memberlist&op=alluser&fid='.$_G['fid']);

		if($adminlist) {
			foreach($adminlist as $user) {
				$adminuserlist[$user['uid']] = $user;
				$adminuserlist[$user['uid']]['online'] = $onlinemember['list'] && is_array($onlinemember['list']) && $onlinemember['list'][$user['uid']] ? 1 : 0;
			}
		}
	}

	include template('diy:group/my_group_manage:'.$_G['fid']);

} elseif($action == 'join') {
	$jioned = DB::result_first("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE uid='$_G[uid]'");
	if(!empty($jioned))showmessage('已加过公会，请退出再加入！', "forum.php?mod=group&fid=$_G[fid]");

	$inviteuid = 0;
	$membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = C::t('forum_groupuser')->fetch_count_by_fid($_G['fid']);
		if($curnum >= $membermaximum) {
			showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
		}
	}
	if($groupuser['uid']) {
		showmessage('group_has_joined', "forum.php?mod=group&fid=$_G[fid]");
	} else {
		$modmember = 4;
		$showmessage = 'group_join_succeed';
		$confirmjoin = TRUE;
		$inviteuid = C::t('forum_groupinvite')->fetch_uid_by_inviteuid($_G['fid'], $_G['uid']);
		if($_G['forum']['jointype'] == 1) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_need_invite';
			}
		} elseif($_G['forum']['jointype'] == 2) {
			$modmember = !empty($groupmanagers[$inviteuid]) || $_G['adminid'] == 1 ? 4 : 0;
			!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
		}

		if($confirmjoin) {
			C::t('forum_groupuser')->insert($_G['fid'], $_G['uid'], $_G['username'], $modmember, TIMESTAMP, TIMESTAMP);
			
			//会员加入t178公会，且不用审核Moxiaoyong		2012-12-20
			if($modmember == 4){
				$group_member_info = array( 'uid'		=> $_G['uid'],
											'username'	=> $_G['username'],
											'groupid'	=> $_G['mygroup']['groupid'] );
				if($inviteuid){
					$group_member_info['referrer'] = $inviteuid;
					C::t('my_group_member') -> get_tcp( $_G['mygroup']['groupid'], $inviteuid, 10 );		//暂定邀请别人入会加TCP10 Moxiaoyong		2012-12-20
				}
				C::t('my_group_member') -> insert( $group_member_info );
			}
			
			if($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
				foreach($groupmanagers as $manage) {
					notification_add($manage['uid'], 'group', 'group_member_join', array('fid' => $_G['fid'], 'groupname' => $_G['forum']['name'], 'url' => $_G['siteurl'].'forum.php?mod=group&action=manage&op=checkuser&fid='.$_G['fid']), 1);
				}
			} else {
			}
			if($inviteuid) {
				C::t('forum_groupinvite')->delete_by_inviteuid($_G['fid'], $_G['uid']);
			}
			if($modmember == 4) {
				C::t('forum_forumfield')->update_membernum($_G['fid']);
			}
			C::t('forum_forumfield')->update($_G['fid'], array('lastupdate' => TIMESTAMP));
		}
		include_once libfile('function/stat');
		updatestat('groupjoin');
		delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
		showmessage($showmessage, "forum.php?mod=group&fid=$_G[fid]");
	}

} elseif($action == 'out') {

	if($_G['uid'] == $_G['forum']['founderuid']) {
		showmessage('group_exit_founder');
	}
	$showmessage = 'group_exit_succeed';
	C::t('forum_groupuser')->delete_by_fid($_G['fid'], $_G['uid']);
	C::t('forum_forumfield')->update_membernum($_G['fid'], -1);
	update_groupmoderators($_G['fid']);
	delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
	
	//公会会员离开t178公会Moxiaoyong		2012-10-20
	C::t('my_group_member')->leave_group($_G['uid']);
	C::t('my_group_member_game')->clear_games($_G['uid']);
	
	showmessage($showmessage, "forum.php?mod=forumdisplay&fid=$_G[fid]");

} elseif($action == 'create') {
	$jioned = DB::result_first("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE uid='$_G[uid]'");
	/* if(!empty($jioned))showmessage('已加过公会，请退出再创建！', "forum.php?mod=group&fid=$_G[fid]"); */
	
	if(!$_G['group']['allowbuildgroup']) {
		showmessage('group_create_usergroup_failed', "group.php");
	}
	$groupnum = C::t('forum_forumfield')->fetch_groupnum_by_founderuid($_G['uid']);
	$allowbuildgroup = $_G['group']['allowbuildgroup'] - $groupnum;
	if($allowbuildgroup < 1) {
		showmessage('group_create_max_failed');
	}
	$_GET['fupid'] = intval($_GET['fupid']);
	$_GET['groupid'] = intval($_GET['groupid']);

	if(!submitcheck('createsubmit')) {
		$groupselect = get_groupselect(getgpc('fupid'), getgpc('groupid'));
	} else {
		$parentid = intval($_GET['parentid']);
		$fup = intval($_GET['fup']);
		$name = censor(dhtmlspecialchars(cutstr(trim($_GET['name']), 20, '')));
		$censormod = censormod($name);
		if(empty($name)) {
			showmessage('group_name_empty');
		} elseif($censormod) {
			showmessage('group_name_failed');
		} elseif(empty($parentid) && empty($fup)) {
			showmessage('group_category_empty');
		}
		if(empty($_G['cache']['grouptype']['first'][$parentid]) && empty($_G['cache']['grouptype']['second'][$fup])
			|| $_G['cache']['grouptype']['first'][$parentid]['secondlist'] &&  !in_array($_G['cache']['grouptype']['second'][$fup]['fid'], $_G['cache']['grouptype']['first'][$parentid]['secondlist'])) {
			showmessage('group_category_error');
		}
		if(empty($fup)) {
			$fup = $parentid;
		}
		if(C::t('forum_forum')->fetch_fid_by_name($name)) {
			showmessage('group_name_exist');
		}
		require_once libfile('function/discuzcode');
		$descriptionnew = discuzcode(dhtmlspecialchars(censor(trim($_GET['descriptionnew']))), 0, 0, 0, 0, 1, 1, 0, 0, 1);
		$censormod = censormod($descriptionnew);
		if($censormod) {
			showmessage('group_description_failed');
		}
		if(empty($_G['setting']['groupmod']) || $_G['adminid'] == 1) {
			$levelinfo = C::t('forum_grouplevel')->fetch_by_credits();
			$levelid = $levelinfo['levelid'];
		} else {
			$levelid = -1;
		}
// 		$newfid = C::t('forum_forum')->insert_group($fup, 'sub', $name, '3', $levelid);
		$newfid = C::t('forum_forum')->insert_group($fup, 'sub', $name, '3', 0);			//公会被直接创建，无需后台审核Moxiaoyong		2012-12-20
		if($newfid) {
			$jointype = intval($_GET['jointype']);
			$gviewperm = intval($_GET['gviewperm']);
			$fieldarray = array('fid' => $newfid, 'description' => $descriptionnew, 'jointype' => $jointype, 'gviewperm' => $gviewperm, 'dateline' => TIMESTAMP, 'founderuid' => $_G['uid'], 'foundername' => $_G['username'], 'membernum' => 1);
			C::t('forum_forumfield')->insert($fieldarray);
			C::t('forum_forumfield')->update_groupnum($fup, 1);
			C::t('forum_groupuser')->insert($newfid, $_G['uid'], $_G['username'], 1, TIMESTAMP);
			require_once libfile('function/cache');
			updatecache('grouptype');
			
			//t178公会表创建Moxiaoyong	2012-12-20
			require_once libfile('function/my_group');
			create_my_group( $newfid, $name, $_G['uid'], $_G['username'] );
		}
		include_once libfile('function/stat');
		updatestat('group');
		if($levelid == -1) {
			showmessage('group_create_mod_succeed', "group.php?mod=my&view=manager", array(), array('alert' => 'right', 'showdialog' => 1, 'showmsg' => true, 'locationtime' => true));
		}
		showmessage('group_create_succeed', "forum.php?mod=group&action=manage&fid=$newfid", array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true));
	}

	include template('diy:group/my_group_manage:'.$_G['fid']);

} elseif($action == 'manage'){
	if(!$_G['forum']['ismoderator']) {
		showmessage('group_admin_noallowed');
	}
	$specialswitch = $_G['current_grouplevel']['specialswitch'];

	$oparray = array('group', 'checkuser', 'manageuser', 'threadtype', 'demise', 'notice');			//添加notice的action Moxiaoyong		2012-12-20
	$_GET['op'] = getgpc('op') && in_array($_GET['op'], $oparray) ?  $_GET['op'] : 'group';
	if(empty($groupmanagers[$_G[uid]]) && !in_array($_GET['op'], array('group', 'threadtype', 'demise')) && $_G['adminid'] != 1) {
		showmessage('group_admin_noallowed');
	}
	$page = intval(getgpc('page')) ? intval($_GET['page']) : 1;
	$perpage = 50;
	$start = ($page - 1) * $perpage;
	$url = 'forum.php?mod=group&action=manage&op='.$_GET['op'].'&fid='.$_G['fid'];
	if($_GET['op'] == 'group') {
		$domainlength = checkperm('domainlength');
		if(submitcheck('groupmanage')) {
			$forumarr = array();
			if(isset($_GET['domain']) && $_G['forum']['domain'] != $_GET['domain']) {
				$domain = strtolower(trim($_GET['domain']));
				if($_G['setting']['allowgroupdomain'] && !empty($_G['setting']['domain']['root']['group']) && $domainlength) {
					checklowerlimit('modifydomain');
				}
				require_once libfile('function/delete');
				if(empty($domainlength) || empty($domain)) {
					$domain = '';
					deletedomain($_G['fid'], 'group');
				} else {
					require_once libfile('function/domain');
					if(domaincheck($domain, $_G['setting']['domain']['root']['group'], $domainlength)) {
						deletedomain($_G['fid'], 'group');
						C::t('common_domain')->insert(array('domain' => $domain, 'domainroot' => $_G['setting']['domain']['root']['group'], 'id' => $_G['fid'], 'idtype' => 'group'));
					}

				}
				$forumarr['domain'] = $domain;
				updatecreditbyaction('modifydomain');
			}

			if(($_GET['name'] && !empty($specialswitch['allowchangename'])) || ($_GET['fup'] && !empty($specialswitch['allowchangetype']))) {
				if($_G['uid'] != $_G['forum']['founderuid'] && $_G['adminid'] != 1) {
					showmessage('group_edit_only_founder');
				}
				$fup = intval($_GET['fup']);
				$parentid = intval($_GET['parentid']);

				if(isset($_GET['name'])) {
					$_GET['name'] = censor(dhtmlspecialchars(cutstr(trim($_GET['name']), 20, '')));
					if(empty($_GET['name'])) {
						showmessage('group_name_empty');
					}
					$censormod = censormod($_GET['name']);
					if($censormod) {
						showmessage('group_name_failed');
					}
				} elseif(isset($_GET['parentid']) && empty($parentid) && empty($fup)) {
					showmessage('group_category_empty');
				}
				if(!empty($_GET['name']) && $_GET['name'] != $_G['forum']['name']) {
					if(C::t('forum_forum')->fetch_fid_by_name($_GET['name'])) {
						showmessage('group_name_exist', $url);
					}
					$forumarr['name'] = $_GET['name'];
				}
				if(empty($fup)) {
					$fup = $parentid;
				}
				if(isset($_GET['parentid']) && $fup != $_G['forum']['fup']) {
					$forumarr['fup'] = $fup;
				}
			}
			if($forumarr) {
				C::t('forum_forum')->update($_G['fid'], $forumarr);
				if($forumarr['fup']) {
					C::t('forum_forumfield')->update_groupnum($forumarr['fup'], 1);
					C::t('forum_forumfield')->update_groupnum($_G['forum']['fup'], -1);
					require_once libfile('function/cache');
					updatecache('grouptype');
				}
			}

			$setarr = array();
			$deletebanner = $_GET['deletebanner'];
			$iconnew = upload_icon_banner($_G['forum'], $_FILES['iconnew'], 'icon');
			$bannernew = upload_icon_banner($_G['forum'], $_FILES['bannernew'], 'banner');
			if($iconnew) {
				$setarr['icon'] = $iconnew;
				$group_recommend = dunserialize($_G['setting']['group_recommend']);
				if($group_recommend[$_G['fid']]) {
					$group_recommend[$_G['fid']]['icon'] = get_groupimg($iconnew);
					C::t('common_setting')->update('group_recommend', $group_recommend);
					include libfile('function/cache');
					updatecache('setting');
				}
			}
			if($bannernew && empty($deletebanner)) {
				$setarr['banner'] = $bannernew;
			} elseif($deletebanner) {
				$setarr['banner'] = '';
				@unlink($_G['forum']['banner']);
			}
			require_once libfile('function/discuzcode');
			$_GET['descriptionnew'] = discuzcode(censor(trim($_GET['descriptionnew'])), 0, 0, 0, 0, 1, 1, 0, 0, 1);
			$censormod = censormod($_GET['descriptionnew']);
			if($censormod) {
				showmessage('group_description_failed');
			}
			$_GET['jointypenew'] = intval($_GET['jointypenew']);
			if($_GET['jointypenew'] == '-1' && $_G['uid'] != $_G['forum']['founderuid']) {
				showmessage('group_close_only_founder');
			}
			$_GET['gviewpermnew'] = intval($_GET['gviewpermnew']);
			$setarr['description'] = $_GET['descriptionnew'];
			$setarr['jointype'] = $_GET['jointypenew'];
			$setarr['gviewperm'] = $_GET['gviewpermnew'];
			C::t('forum_forumfield')->update($_G['fid'], $setarr);
			showmessage('group_setup_succeed', $url);
		} else {
			$firstgid = $_G['cache']['grouptype']['second'][$_G['forum']['fup']]['fup'];
			$groupselect = get_groupselect($firstgid, $_G['forum']['fup']);
			$gviewpermselect = $jointypeselect = array('','','');
			require_once libfile('function/editor');
			$_G['forum']['descriptionnew'] = html2bbcode($_G['forum']['description']);
			$jointypeselect[$_G['forum']['jointype']] = 'checked="checked"';
			$gviewpermselect[$_G['forum']['gviewperm']] = 'checked="checked"';
			if($_G['setting']['allowgroupdomain'] && !empty($_G['setting']['domain']['root']['group']) && $domainlength) {
				loadcache('creditrule');
				getuserprofile('extcredits1');
				$rule = $_G['cache']['creditrule']['modifydomain'];
				$credits = $consume = $common = '';
				for($i = 1; $i <= 8; $i++) {
					if($_G['setting']['extcredits'][$i] && $rule['extcredits'.$i]) {
						$consume .= $common.$_G['setting']['extcredits'][$i]['title'].$rule['extcredits'.$i].$_G['setting']['extcredits'][$i]['unit'];
						$credits .= $common.$_G['setting']['extcredits'][$i]['title'].$_G['member']['extcredits'.$i].$_G['setting']['extcredits'][$i]['unit'];
						$common = ',';
					}
				}
			}
		}
	} elseif($_GET['op'] == 'checkuser') {
		$checktype = 0;
		$checkusers = array();
		if(!empty($_GET['uid'])) {
			$checkusers = array($_GET['uid']);
			$checktype = intval($_GET['checktype']);
		} elseif(getgpc('checkall') == 1 || getgpc('checkall') == 2) {
			$checktype = $_GET['checkall'];
			$query = C::t('forum_groupuser')->fetch_all_by_fid($_G['fid'], 1);
			foreach($query as $row) {
				$checkusers[] = $row['uid'];
			}
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				$notification = $checktype == 1 ? 'group_member_check' : 'group_member_check_failed';
				notification_add($uid, 'group', $notification, array('fid' => $_G['fid'], 'groupname' => $_G['forum']['name'], 'url' => $_G['siteurl'].'forum.php?mod=group&fid='.$_G['fid']), 1);
			}
			if($checktype == 1) {
				C::t('forum_groupuser')->update_for_user($checkusers, $_G['fid'], null, null, 4);
				C::t('forum_forumfield')->update_membernum($_G['fid'], count($checkusers));
				
				//审核通过会员加入t178公会Moxiaoyong		2012-12-20
				$group_member_info = array( 'uid'		=> $_G['uid'],
											'username'	=> $_G['username'],
											'groupid'	=> $_G['mygroup']['groupid'] );
				C::t('my_group_member') -> insert( $group_member_info );
				
			} elseif($checktype == 2) {
				C::t('forum_groupuser')->delete_by_fid($_G['fid'], $checkusers);
			}
			if($checktype == 1) {
				showmessage('group_moderate_succeed', $url);
			} else {
				showmessage('group_moderate_failed', $url);
			}
		} else {
			$checkusers = array();
			$userlist = C::t('forum_groupuser')->groupuserlist($_G['fid'], 'joindateline', $perpage, $start, array('level' => 0));
			$checknum = C::t('forum_groupuser')->fetch_count_by_fid($_G['fid'], 1);
			$multipage = multi($checknum, $perpage, $page, $url);
			foreach($userlist as $user) {
				$user['joindateline'] = date('Y-m-d H:i', $user['joindateline']);
				$checkusers[$user['uid']] = $user;
			}
		}
	} elseif($_GET['op'] == 'manageuser') {
		$mtype = array(1 => lang('group/template', 'group_moderator'), 2 => lang('group/template', 'group_moderator_vice'), 3 => lang('group/template', 'group_star_member_title'), 4 => lang('group/misc', 'group_normal_member'), 5 => lang('group/misc', 'group_goaway'));
		if(!submitcheck('manageuser')) {
			$userlist = array();
			if(empty($_GET['srchuser'])) {
				$staruserlist = $page < 2 ? C::t('forum_groupuser')->groupuserlist($_G['fid'], '', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';
				$adminuserlist = $groupmanagers && $page < 2 ? $groupmanagers : array();
				$multipage = multi($_G['forum']['membernum'], $perpage, $page, $url);
			} else {
				$start = 0;
			}
			$userlist = C::t('forum_groupuser')->groupuserlist($_G['fid'], '', $perpage, $start, $_GET['srchuser'] ? "AND username like '".addslashes($_GET[srchuser])."%'" : "AND level='4'");
		} else {
			$muser = getgpc('muid');
			$targetlevel = $_GET['targetlevel'];
			if($muser && is_array($muser)) {
				foreach($muser as $muid => $mlevel) {
					if($_G['adminid'] != 1 && $_G['forum']['founderuid'] != $_G['uid'] && $groupmanagers[$muid] && $groupmanagers[$muid]['level'] <= $groupuser['level']) {
						showmessage('group_member_level_admin_noallowed.', $url);
					}
					if($_G['adminid'] == 1 || ($muid != $_G['uid'] && ($_G['forum']['founderuid'] == $_G['uid'] || !$groupmanagers[$muid] || $groupmanagers[$muid]['level'] > $groupuser['level']))) {
						if($targetlevel != 5) {
							C::t('forum_groupuser')->update_for_user($muid, $_G['fid'], null, null, $targetlevel);
						} else {
							if(!$groupmanagers[$muid] || count($groupmanagers) > 1) {
								C::t('forum_groupuser')->delete_by_fid($_G['fid'], $muid);
								C::t('forum_forumfield')->update_membernum($_G['fid'], -1);
							} else {
								showmessage('group_only_one_moderator', $url);
							}
						}
					}
				}
				update_groupmoderators($_G['fid']);
				showmessage('group_setup_succeed', $url.'&page='.$page);
			} else {
				showmessage('group_choose_member', $url);
			}
		}
	} elseif($_GET['op'] == 'threadtype') {
		if(empty($specialswitch['allowthreadtype'])) {
			showmessage('group_level_cannot_do');
		}
		if($_G['uid'] != $_G['forum']['founderuid'] && $_G['adminid'] != 1) {
			showmessage('group_threadtype_only_founder');
		}
		$typenumlimit = 20;
		if(!submitcheck('groupthreadtype')) {
			$threadtypes = $checkeds = array();
			if(empty($_G['forum']['threadtypes'])) {
				$checkeds['status'][0] = 'checked';
				$display = 'none';
			} else {
				$display = '';
				$_G['forum']['threadtypes']['status'] = 1;
				foreach($_G['forum']['threadtypes'] as $key => $val) {
					$val = intval($val);
					$checkeds[$key][$val] = 'checked';
				}
			}
			foreach(C::t('forum_threadclass')->fetch_all_by_fid($_G['fid']) as $type) {
				$type['enablechecked'] = isset($_G['forum']['threadtypes']['types'][$type['typeid']]) ? ' checked="checked"' : '';
				$type['name'] = dhtmlspecialchars($type['name']);
				$threadtypes[] = $type;
			}
		} else {
			$threadtypesnew = $_GET['threadtypesnew'];
			$threadtypesnew['types'] = $threadtypes['special'] = $threadtypes['show'] = array();
			if(is_array($_GET['newname']) && $_GET['newname']) {
				$newname = array_unique($_GET['newname']);
				if($newname) {
					foreach($newname as $key => $val) {
						$val = dhtmlspecialchars(censor(cutstr(trim($val), 16, '')));
						if($_GET['newenable'][$key] && $val) {
							$newtype = C::t('forum_threadclass')->fetch_by_fid_name($_G['fid'], $val);
							$newtypeid = $newtype['typeid'];
							if(!$newtypeid) {
								$typenum = C::t('forum_threadclass')->count_by_fid($_G['fid']);
								if($typenum < $typenumlimit) {
									$threadtypes_newdisplayorder = intval($_GET['newdisplayorder'][$key]);
									$newtypeid = C::t('forum_threadclass')->insert(array('fid' => $_G['fid'], 'name' => $val, 'displayorder' => $threadtypes_newdisplayorder), true);
								}
							}
							if($newtypeid) {
								$threadtypesnew['options']['name'][$newtypeid] = $val;
								$threadtypesnew['options']['displayorder'][$newtypeid] = $threadtypes_newdisplayorder;
								$threadtypesnew['options']['enable'][$newtypeid] = 1;
							}
						}
					}
				}
				$threadtypesnew['status'] = 1;
			} else {
				$newname = array();
			}
			if($threadtypesnew['status']) {
				if(is_array($threadtypesnew['options']) && $threadtypesnew['options']) {

					if(!empty($threadtypesnew['options']['enable'])) {
						$typeids = array_keys($threadtypesnew['options']['enable']);
					} else {
						$typeids = array(0);
					}
					if(!empty($threadtypesnew['options']['delete'])) {
						C::t('forum_threadclass')->delete_by_typeid_fid($threadtypesnew['options']['delete'], $_G['fid']);
					}
					foreach(C::t('forum_threadclass')->fetch_all_by_typeid_fid($typeids, $_G['fid']) as $type) {
						if($threadtypesnew['options']['name'][$type['typeid']] != $type['name'] || $threadtypesnew['options']['displayorder'][$type['typeid']] != $type['displayorder']) {
							$threadtypesnew['options']['name'][$type['typeid']] = dhtmlspecialchars(censor(cutstr(trim($threadtypesnew['options']['name'][$type['typeid']]), 16, '')));
							$threadtypesnew['options']['displayorder'][$type['typeid']] = intval($threadtypesnew['options']['displayorder'][$type['typeid']]);
							C::t('forum_threadclass')->update_by_typeid_fid($type['typeid'], $_G['fid'], array(
								'name' => $threadtypesnew['options']['name'][$type['typeid']],
								'displayorder' => $threadtypesnew['options']['displayorder'][$type['typeid']],
							));
						}
					}
				}
				if($threadtypesnew && $typeids) {
					foreach(C::t('forum_threadclass')->fetch_all_by_typeid($typeids) as $type) {
						if($threadtypesnew['options']['enable'][$type['typeid']]) {
							$threadtypesnew['types'][$type['typeid']] = $threadtypesnew['options']['name'][$type['typeid']];
						}
					}
				}
				$threadtypesnew = !empty($threadtypesnew) ? serialize($threadtypesnew) : '';
			} else {
				$threadtypesnew = '';
			}
			C::t('forum_forumfield')->update($_G['fid'], array('threadtypes' => $threadtypesnew));
			showmessage('group_threadtype_edit_succeed', $url);
		}
	} elseif($_GET['op'] == 'demise') {
		if((!empty($_G['forum']['founderuid']) && $_G['forum']['founderuid'] == $_G['uid']) || $_G['adminid'] == 1) {
			$ucresult = $allowbuildgroup = $groupnum = 0;
			if(count($groupmanagers) <= 1) {
				showmessage('group_cannot_demise');
			}

			if(submitcheck('groupdemise')) {
				$suid = intval($_GET['suid']);
				if(empty($suid)) {
					showmessage('group_demise_choose_receiver');
				}
				if(empty($_GET['grouppwd'])) {
					showmessage('group_demise_password');
				}
				loaducenter();
				$ucresult = uc_user_login($_G['uid'], $_GET['grouppwd'], 1);
				if(!is_array($ucresult) || $ucresult[0] < 1) {
					showmessage('group_demise_password_error');
				}
				$user = getuserbyuid($suid);
				loadcache('usergroup_'.$user['groupid']);
				$allowbuildgroup = $_G['cache']['usergroup_'.$user['groupid']]['allowbuildgroup'];
				if($allowbuildgroup > 0) {
					$groupnum = C::t('forum_forumfield')->fetch_groupnum_by_founderuid($suid);
				}
				if(empty($allowbuildgroup) || $allowbuildgroup - $groupnum < 1) {
					showmessage('group_demise_receiver_cannot_do');
				}
				C::t('forum_forumfield')->update($_G['fid'], array('founderuid' => $suid, 'foundername' => $user['username']));
				C::t('forum_groupuser')->update_for_user($suid, $_G['fid'], NULL, NULL, 1);
				update_groupmoderators($_G['fid']);
				sendpm($suid, lang('group/misc', 'group_demise_message_title', array('forum' => $_G['forum']['name'])), lang('group/misc', 'group_demise_message_body', array('forum' => $_G['forum']['name'], 'siteurl' => $_G['siteurl'], 'fid' => $_G['fid'])), $_G['uid']);
				showmessage('group_demise_succeed', 'forum.php?mod=group&action=manage&fid='.$_G['fid']);
			}
		} else {
			showmessage('group_demise_founder_only');
		}
	} elseif($_GET['op'] == 'notice') {				//公会公告Moxiaoyong		2012-12-19
		if((!empty($_G['forum']['founderuid']) && $_G['forum']['founderuid'] == $_G['uid']) || $_G['adminid'] == 1) {
			if(submitcheck('groupnotice')) {
				require_once libfile('function/discuzcode');
				$notice = discuzcode(dhtmlspecialchars(censor(trim($_GET['notice']))), 0, 0, 0, 0, 1, 1, 0, 0, 1);
				$censormod = censormod($notice);
				if($censormod) {
					showmessage('group_notice_failed');
				}
				C::t('my_group')->update($_G['mygroup']['groupid'], array('notice' => $notice));
				
				showmessage('group_notice_edit_succeed', 'forum.php?mod=group&action=manage&fid='.$_G['fid']);
			}
		} else {
			showmessage('group_notice_founder_only');
		}
	} else {
		showmessage('undefined_action');
	}
	include template('diy:group/my_group_manage:'.$_G['fid']);

} elseif($action == 'recommend') {
	if(!$_G['forum']['ismoderator'] || !in_array($_G['adminid'], array(1,2))) {
		showmessage('group_admin_noallowed');
	}
	if(submitcheck('grouprecommend')) {
		if($_GET['recommend'] != $_G['forum']['recommend']) {
			C::t('forum_forum')->update($_G['fid'], array('recommend' => intval($_GET['recommend'])));
			require_once libfile('function/cache');
			updatecache('forumrecommend');
		}
		showmessage('grouprecommend_succeed', '', array(), array('alert' => 'right', 'closetime' => true, 'showdialog' => 1));
	} else {
		require_once libfile('function/forumlist');
		$forumselect = forumselect(FALSE, 0, $_G['forum']['recommend']);
	}
	include template('group/group_recommend');
}
//公会会员向公会捐赠TCP Moxiaoyong		2012-12-20
elseif($action == 'contribute') {
	if( $groupuser['uid'] ){
		$tcp = intval($_GET['tcp']);
		if($tcp > $group_member['tcp']){
			showmessage('group_tcp_shortage', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		C::t('my_group_member')->contribute_tcp( $_G['mygroup']['groupid'], $_G['uid'], $tcp );
		C::t('my_group')->add_tcp( $_G['fid'], $tcp );
		require_once libfile('function/my_group');
		check_group_level($_G['fid']);
		showmessage('group_contribute_tcp_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
	}
	else{
		showmessage('group_contribute_tcp_to_other', 'forum.php?mod=group&fid='.$_G['fid']);
	}
}
//公会会员签到Moxiaoyong		2012-12-20
elseif($action == 'signing') {
	if( $groupuser['uid'] ){
		if(C::t('my_group_signing')->had_signed($_G['mygroup']['groupid'], $_G['uid'])){
			showmessage('group_had_signing', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		$group_signing_data = array( 'groupid'			=> $_G['mygroup']['groupid'],
										'uid'			=> $_G['uid'],
										'date'	=> strftime('%Y-%m-%d') );
		C::t('my_group_signing')->insert($group_signing_data);
		C::t('my_group_member')->get_tcp($_G['mygroup']['groupid'], $_G['uid'], 5);			//暂定签到获得5TCP Moxiaoyong		2012-12-21
		showmessage('group_signing_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
	}
	else{
		showmessage('group_signing_from_other', 'forum.php?mod=group&fid='.$_G['fid']);
	}
}
//公会入驻游戏Moxiaoyong		2012-12-21
elseif($action == 'group_join_game') {
	if((!empty($_G['forum']['founderuid']) && $_G['forum']['founderuid'] == $_G['uid']) || $_G['adminid'] == 1) {
		$had_joint = C::t('my_group_game')->had_joint( $_G['mygroup']['groupid'], $_GET['game_serverid'] );
		if( $had_joint ){
			showmessage('group_had_joint_game', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		else{
			$game_server_info = C::t('game_server')->get_game_server_info( $_GET['game_serverid'] );
			C::t('my_group_game')->apply_join_game( $_G['mygroup']['groupid'], $game_server_info['game_id'], $_GET['game_serverid'] );
			showmessage('group_join_game_verify', 'forum.php?mod=group&fid='.$_G['fid']);
		}
	}
	else{
		showmessage('group_join_game_founder_only', 'forum.php?mod=group&fid='.$_G['fid']);
	}
}
//公会会员入驻游戏Moxiaoyong		2012-12-21
elseif($action == 'group_member_join_game') {
	if( $groupuser['uid'] ){
		$group_game_info = C::t('my_group_game')->get_group_game_info( $_G['mygroup']['groupid'], $_GET['game_serverid'] );
		$had_joint = C::t('my_group_member_game')->had_joint( $group_game_info['group_gameid'], $_G['uid'] );
		if( $had_joint ){
			showmessage('group_member_had_joint_game', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		else{
			C::t('my_group_member_game')->apply_join_game( $_G['fid'], 
															$group_game_info['group_gameid'], 
															$group_game_info['groupid'], 
															$group_game_info['game_serverid'] );
			showmessage('group_member_join_game_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
		}
	}
	else{
		showmessage('group_member_join_game_from_other', 'forum.php?mod=group&fid='.$_G['fid']);
	}
}
//公会关系Moxiaoyong		2013-01-08
elseif($action == 'group_relation') {
	if($_GET['op'] == 'friend') {
		$friendly_group_id = intval($_GET['friendly_group_id']);
		if( $_G['mygroup']['groupid'] == $friendly_group_id ){
			showmessage('can_not_friend_self', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		$is_friend = C::t('my_group_relation')->is_friend( $_G['mygroup']['groupid'], $friendly_group_id );
		if( $is_friend ){
			showmessage('the_group_is_friend', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		else{
			C::t('my_group_relation')->nonenemy_group( $_G['mygroup']['groupid'], $friendly_group_id );
			C::t('my_group_relation')->friend_group( $_G['mygroup']['groupid'], $friendly_group_id );
			showmessage('friend_group_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
		}
	}
	else if($_GET['op'] == 'enemy') {
		$enemy_group_id = intval($_GET['enemy_group_id']);
		if( $_G['mygroup']['groupid'] == $enemy_group_id ){
			showmessage('can_not_enemy_self', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		$is_enemy = C::t('my_group_relation')->is_enemy( $_G['mygroup']['groupid'], $enemy_group_id );
		if( $is_enemy ){
			showmessage('the_group_is_enemy', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		else{
			C::t('my_group_relation')->unfriend_group( $_G['mygroup']['groupid'], $enemy_group_id );
			C::t('my_group_relation')->enemy_group( $_G['mygroup']['groupid'], $enemy_group_id );
			showmessage('enemy_group_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
		}
	}
	else if($_GET['op'] == 'unfriend') {
		$friendly_group_id = intval($_GET['friendly_group_id']);
		if( $_G['mygroup']['groupid'] == $friendly_group_id ){
			showmessage('no_unfriend_self', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		$is_friend = C::t('my_group_relation')->is_friend( $_G['mygroup']['groupid'], $friendly_group_id );
		if( $is_friend ){
			C::t('my_group_relation')->unfriend_group( $_G['mygroup']['groupid'], $friendly_group_id );
			showmessage('unfriend_group_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		else{
			showmessage('the_group_is_not_friend', 'forum.php?mod=group&fid='.$_G['fid']);
		}
	}
	else if($_GET['op'] == 'nonenemy') {
		$enemy_group_id = intval($_GET['enemy_group_id']);
		if( $_G['mygroup']['groupid'] == $enemy_group_id ){
			showmessage('no_nonenemy_self', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		$is_enemy = C::t('my_group_relation')->is_enemy( $_G['mygroup']['groupid'], $enemy_group_id );
		if( $is_enemy ){
			C::t('my_group_relation')->nonenemy_group( $_G['mygroup']['groupid'], $enemy_group_id );
			showmessage('nonenemy_group_succeed', 'forum.php?mod=group&fid='.$_G['fid']);
		}
		else{
			showmessage('the_group_is_not_enemy', 'forum.php?mod=group&fid='.$_G['fid']);
		}
	}
}
//关于t178公会检查Moxiaoyong		2012-12-20
elseif($action == 'check_group') {
	$now = time();
	$group_info_list = C::t('my_group')->get_full_group_info();
	$group_member_number = C::t('my_group_member')->get_member_number();
	
	//入驻游戏检查
	$before_7_timestamp = strtotime( '-7 day', $now );
	$group_game_member_number = C::t('my_group_member_game')->get_group_member_game_join_number( $before_7_timestamp );
	$verify_group_games = C::t('my_group_game')->get_verify_group_games();
	foreach( $verify_group_games as $verify_game ){
		$verify_group_gameid = $verify_game['group_gameid'];
		if( $now > $before_7_timestamp ){
			if( $group_game_member_number[$verify_group_gameid] > $group_member_number[$verify_game['groupid']] * 0.6 ){
				C::t('my_group_game')->pass_join_application( $verify_group_gameid );
			}
			else{
				C::t('my_group_game')->reject_join_application( $verify_group_gameid );
			}
		}
	}
	
	$before_7_date = strftime( $before_7_timestamp );
	$group_signing_number = C::t('my_group_signing')->get_signing_number( $before_7_date, strftime( '%Y-%m-%d', $now ) );
	$before_10_timestamp = strtotime( '-10 day', $now );
	$group_game_number_10 = C::t('my_group_game')->get_group_game_number( $before_10_timestamp, $now );
	$before_60_timestamp = strtotime( '-60 day', $now );
	$group_game_number_60 = C::t('my_group_game')->get_group_game_number( $before_30_timestamp, $now );
	$clear_fids = array();
	foreach ( $group_info_list as $group_info ){
		if( $group_info['status'] == 1 ){			//检查申请公会（10天满30人，并成功入驻一款游戏）
			$check_timestamp = $group_info['apply_time'] + 86400 * 10;
			if( $group_game_number_10[$group_info['groupid']] > 0 && $group_member_number[$group_info['groupid']] > 30 ){
				C::t('my_group')->pass_group_application( $group_info['fid'] );
			}
			else if( $now > $check_timestamp ){
				$clear_fids[] = $group_info['fid'];
			}
		}
		else if( $group_info['status'] == 2 ){		//检查公会合法性（7天签到20此，并60天入驻一款游戏）
			$check_signing_timestamp = $group_info['build_time'] + 86400 * 7;
			$check_join_game_timestamp = $group_info['build_time'] + 86400 * 60;
			if( ( $group_signing_number[$group_info['groupid']] < 20 && $now > $check_signing_timestamp ) ||
				( $group_game_number_30[$group_info['groupid']] < 1 && $now > $check_join_game_timestamp ) ){
				$clear_fids[] = $group_info['fid'];
			}
		}
	}
	require_once libfile('function/my_group');
	close_my_group( $clear_fids );
}
//独立管理页面Moxiaoyong		2013-01-09
elseif($action == 'my_manage') {
	include template('diy:group/my_group_manage:'.$_G['fid']);
}

?>