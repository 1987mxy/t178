<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_store.php
 * @time	2012-12-14 上午1:14:09
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_store extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_store';
		$this->_pk    = 'group_storeid';

		parent::__construct();
	}
	
	/**
	 * 获取公会商城信息
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		公会商城信息
	 */
	public function get_group_store_info( $groupid ){
		if( empty( $groupid ) ) return array();
		$group_store_info = DB::fetch_first( "SELECT * FROM %t WHERE " . DB::field( 'groupid', $groupid ), array( $this -> _table ) );
		return $group_store_info;
	}
}

?>