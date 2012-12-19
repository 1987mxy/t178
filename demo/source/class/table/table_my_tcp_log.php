<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_tcp_log.php
 * @time	2012-12-15 上午3:23:19
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_tcp_log extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_tcp_log';
		$this->_pk    = 'tcp_logid';

		parent::__construct();
	}
	
	/**
	 * 获取公会TCP日志
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		公会TCP日志
	 */
	public function get_group_tcp_log( $groupid ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'loggerid', $groupid ) . " AND " . DB::field( 'logger_type', 'group' ), array( $this -> _table ) );
	}
	
	/**
	 * 获取用户TCP日志
	 * @access	public
	 * @param	$uid		用户ID
	 * @return	array		用户TCP日志
	 */
	public function get_member_tcp_log( $uid ){
		if( empty( $uid ) ) return array();
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'loggerid', $uid ) . " AND " . DB::field( 'logger_type', 'member' ), array( $this -> _table ) );
	}
}

?>