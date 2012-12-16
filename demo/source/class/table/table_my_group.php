<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group.php
 * @time	2012-12-14 上午1:11:53
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group extends discuz_table
{
	public function __construct() {

		$this -> _table = 'my_group';
		$this -> _pk = 'groupid';

		parent::__construct();
	}
	
	/**
	 * 获取公会信息
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		公会信息
	 */
	public function get_group_info( $groupid ){
		return $this -> fetch( $groupid );
	}
	
	/**
	 * 获取公会公告
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	string		公会公告
	 */
	public function get_group_notice( $groupid ){
		if( empty( $groupid ) ) {
			return '';
		}
		$notice = DB::fetch_first( "SELECT notice FROM %t WHERE groupid=%d", array( $this -> _table, $groupid ) );
		return empty( $notice ) ? '' : $notice[ 'notice' ];
	}
}

?>