<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_friend.php
 * @time	2012-12-14 上午1:15:32
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_friend extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_friend';
		$this->_pk    = 'group_friendid';

		parent::__construct();
	}
	
	/**
	 * 获取友情公会列表
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		友情公会列表
	 */
	public function get_friend_group( $groupid ){
		if( empty( $groupid ) ) {
			return array();
		}
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'group_id_a', $groupid ) . " AND " . DB::field( 'del_flag', 0 ), array( $this -> _table ) );
	}
	
	/**
	 * 建立友情公会关系
	 * @access	public
	 * @param	$group_id_a		建立友情A公会ID
	 * @param	$group_id_b		建立友情B公会ID
	 * @return	boolean			建立友情公会结果
	 */
	public function friend_group( $group_id_a, $group_id_b ){
		$data = array( 'group_id_a'	=> $group_id_a,
						'group_id_b'	=> $group_id_b );
		$this -> insert( $data );
		$data = array( 'group_id_a'	=> $group_id_b,
						'group_id_b'	=> $group_id_a );
		$this -> insert( $data );
		return true;
	}
	
	/**
	 * 解除友情公会关系
	 * @access	public
	 * @param	$group_id_a		解除友情A公会ID
	 * @param	$group_id_b		解除友情B公会ID
	 * @return	boolean			解除友情公会结果
	 */
	public function unfriend_group( $group_id_a, $group_id_b ){
		if( empty( $group_id_a ) || empty( $group_id_b ) ){
			return false;
		}
		DB::query( "UPDATE %t SET del_flag=1 WHERE " . DB::field( 'group_id_a', $group_id_a ) . " AND " . DB::field( 'group_id_b', $group_id_b ), array( $this -> _table ) );
		DB::query( "UPDATE %t SET del_flag=1 WHERE " . DB::field( 'group_id_a', $group_id_b ) . " AND " . DB::field( 'group_id_b', $group_id_a ), array( $this -> _table ) );
		return true;
	}
	
	/**
	 * 友情公会删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group_friend( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'group_id_a', $groupids ) . ' OR ' . DB::field( 'group_id_b', $groupids );
		DB::delete( $this->_table, $condition );
		return true;
	}
}

?>