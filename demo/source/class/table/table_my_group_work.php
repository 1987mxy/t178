<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_work.php
 * @time	2012-12-14 上午1:14:39
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_work extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_work';
		$this->_pk    = 'group_workid';

		parent::__construct();
	}
	
	/**
	 * 获取公会打工信息
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		公会打工信息
	 */
	public function get_group_work_info( $groupid ){
		if( empty( $groupid ) ) return array();
		$group_work_info = DB::fetch_first( "SELECT * FROM %t WHERE " . DB::field( 'groupid', $groupid ), array( $this -> _table ) );
		return $group_work_info;
	}
}

?>