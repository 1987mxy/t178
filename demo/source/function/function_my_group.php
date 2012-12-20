<?php

/**
 * @author	Moxiaoyong
 * @file	function_my_group.php
 * @time	2012-12-20 下午2:29:16
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 关闭t178公会
 * @param	$fids		Discuz的fid列表
 * @return	boolean		删除操作结果
 */
function close_my_group( $fids ){
	if( empty( $fids ) ){
		return false;
	}
	$fids = is_array( $fids ) ? $fids : array( $fids );
	$groupids = C::t('my_group') -> fids2groupids( $fids );
	if( C::t('my_group') -> del_group( $groupids ) == false ) return false;
	if( C::t('my_group_member') -> del_group_member( $groupids ) == false ) return false;
	if( C::t('my_group_store') -> del_group_store( $groupids ) == false ) return false;
	if( C::t('my_group_work') -> del_group_work( $groupids ) == false ) return false;
	if( C::t('my_group_friend') -> del_group_friend( $groupids ) == false ) return false;
	if( C::t('my_group_game') -> del_group_game( $groupids ) == false ) return false;
	return true;
}

/**
 * 创建t178公会
 * @param	$fid		Discuz的fid
 * @param	$group_name	公会名称
 * @param	$uid		公会创建人ID
 * @param	$username	公会创建人
 * @return	boolean		创建操作结果
 */
function create_my_group( $fid, $group_name, $uid, $username ){
	if( empty( $fid ) ){
		return false;
	}
	$groupid = C::t('my_group') -> insert( array( 'fid' => $fid), true );
	$group_member_info = array( 'uid'		=> $uid,
								'username'	=> $username,
								'groupid'	=> $groupid );
	C::t('my_group_member') -> insert( $group_member_info );
	$group_store_info = array( 'store_name'	=> $group_name.'商城',
								'groupid'	=> $groupid );
	C::t('my_group_store') -> insert( $group_store_info );
	$group_work_info = array( 'groupid'	=> $groupid );
	C::t('my_group_work') -> insert( $group_work_info );
}

/**
 * 检查公会是否升级，并修改等级
 * @param	$fid		Discuz的fid
 * @return	int			公会现有等级
 */
function check_group_level( $fid ){
	if( empty( $fid ) ){
		return false;
	}
	$group_info = C::t('my_group') -> get_group_info( $fid );
	$group_tcp = $group_info[ 'tcp' ];
	$group_level = C::t('forum_grouplevel') -> fetch_by_credits( $group_tcp );
	C::t('my_group') -> update( $group_info['groupid'], array( 'level', $group_level[ 'leveltitle' ] ) );
	return $group_level[ 'leveltitle' ];
}

?>