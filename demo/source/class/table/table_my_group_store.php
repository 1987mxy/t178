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
	
	/**
	 * 公会商城删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group_store( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'groupid', $groupids );
		DB::delete( $this->_table, $condition );
		return DB::affected_rows() >= count( $groupids ) ? true : false;
	}
}

?>