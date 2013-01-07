<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_relation.php
 * @time	2012-12-14 上午1:15:32
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_relation extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_relation';
		$this->_pk    = 'group_relationid';

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
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'group_id_a', $groupid ) . " AND " . 
															DB::field( 'del_flag', 0 ) . " AND " . 
															DB::field( 'relation', 1 ), 
								array( $this -> _table ) );
	}
	
	/**
	 * 检查该俩公会间是否存在友情公会关系
	 * @access	public
	 * @param	$group_id_a		友情A公会ID
	 * @param	$group_id_b		友情B公会ID
	 * @return	boolean			是否存在友情公会关系
	 */
	public function is_friend( $group_id_a, $group_id_b ){
		if( empty( $group_id_a ) || empty( $group_id_b ) || $group_id_a == $group_id_b ){
			return false;
		}
		$group_friendid = DB::fetch_first( "SELECT group_relationid FROM %t WHERE " . DB::field( 'del_flag', 0 ) . " AND " . 
																					DB::field( 'relation', 1 ) . " AND " . 
																					" ( ( " . DB::field( 'group_id_a', $group_id_a ) . " AND " . DB::field( 'group_id_b', $group_id_b ) . " ) OR " . 
																					" ( " . DB::field( 'group_id_a', $group_id_b ) . " AND " . DB::field( 'group_id_b', $group_id_a ) . " ) ) ", 
											array( $this -> _table ) );
		if( !empty( $group_friendid ) ) return true;
		return false;
	}
	
	/**
	 * 建立友情公会关系
	 * @access	public
	 * @param	$group_id_a		建立友情A公会ID
	 * @param	$group_id_b		建立友情B公会ID
	 * @return	boolean			建立友情公会结果
	 */
	public function friend_group( $group_id_a, $group_id_b ){
		if( empty( $group_id_a ) || empty( $group_id_b ) || $group_id_a == $group_id_b ){
			return false;
		}
		$data = array( 'group_id_a'		=> $group_id_a,
						'group_id_b'	=> $group_id_b, 
						'relation'		=> 1 );
		$this -> insert( $data );
		$data = array( 'group_id_a'		=> $group_id_b,
						'group_id_b'	=> $group_id_a,
						'relation'		=> 1 );
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
		if( empty( $group_id_a ) || empty( $group_id_b ) || $group_id_a == $group_id_b ){
			return false;
		}
		DB::query( "UPDATE %t SET del_flag=1 WHERE " . DB::field( 'del_flag', 0 ) . " AND " . 
														DB::field( 'relation', 1 ) . " AND " . 
														" ( ( " . DB::field( 'group_id_a', $group_id_a ) . " AND " . DB::field( 'group_id_b', $group_id_b ) . " ) OR " . 
														" ( " . DB::field( 'group_id_a', $group_id_b ) . " AND " . DB::field( 'group_id_b', $group_id_a ) . " ) ) ", 
					array( $this -> _table ) );
		return true;
	}
	
	/**
	 * 获取敌对公会列表
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		敌对公会列表
	 */
	public function get_enemy_group( $groupid ){
		if( empty( $groupid ) ) {
			return array();
		}
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'group_id_a', $groupid ) . " AND " . 
															DB::field( 'del_flag', 0 ) . " AND " . 
															DB::field( 'relation', 2 ), 
								array( $this -> _table ) );
	}
	
	/**
	 * 检查该俩公会间是否存在敌对公会关系
	 * @access	public
	 * @param	$group_id_a		友情A公会ID
	 * @param	$group_id_b		友情B公会ID
	 * @return	boolean			是否存在友情公会关系
	 */
	public function is_enemy( $group_id_a, $group_id_b ){
		if( empty( $group_id_a ) || empty( $group_id_b ) || $group_id_a == $group_id_b ){
			return false;
		}
		$group_friendid = DB::fetch_first( "SELECT group_relationid FROM %t WHERE " . DB::field( 'del_flag', 0 ) . " AND " . 
																					DB::field( 'relation', 2 ) . " AND " . 
																					" ( ( " . DB::field( 'group_id_a', $group_id_a ) . " AND " . DB::field( 'group_id_b', $group_id_b ) . " ) OR " . 
																					" ( " . DB::field( 'group_id_a', $group_id_b ) . " AND " . DB::field( 'group_id_b', $group_id_a ) . " ) ) ", 
											array( $this -> _table ) );
		if( !empty( $group_friendid ) ) return true;
		return false;
	}
	
	/**
	 * 建立敌对公会关系
	 * @access	public
	 * @param	$group_id_a		建立敌对A公会ID
	 * @param	$group_id_b		建立敌对B公会ID
	 * @return	boolean			建立敌对公会结果
	 */
	public function enemy_group( $group_id_a, $group_id_b ){
		if( empty( $group_id_a ) || empty( $group_id_b ) || $group_id_a == $group_id_b ){
			return false;
		}
		$data = array( 'group_id_a'		=> $group_id_a,
						'group_id_b'	=> $group_id_b,
						'relation'		=> 2 );
		$this -> insert( $data );
		$data = array( 'group_id_a'		=> $group_id_b,
						'group_id_b'	=> $group_id_a,
						'relation'		=> 2 );
		$this -> insert( $data );
		return true;
	}
	
	/**
	 * 解除敌对公会关系
	 * @access	public
	 * @param	$group_id_a		解除敌对A公会ID
	 * @param	$group_id_b		解除敌对B公会ID
	 * @return	boolean			解除敌对公会结果
	 */
	public function nonenemy_group( $group_id_a, $group_id_b ){
		if( empty( $group_id_a ) || empty( $group_id_b ) || $group_id_a == $group_id_b ){
			return false;
		}
		DB::query( "UPDATE %t SET del_flag=1 WHERE " . DB::field( 'del_flag', 0 ) . " AND " . 
														DB::field( 'relation', 2 ) . " AND " . 
														" ( ( " . DB::field( 'group_id_a', $group_id_a ) . " AND " . DB::field( 'group_id_b', $group_id_b ) . " ) OR " . 
														" ( " . DB::field( 'group_id_a', $group_id_b ) . " AND " . DB::field( 'group_id_b', $group_id_a ) . " ) ) ", 
					array( $this -> _table ) );
		return true;
	}
	
	/**
	 * 公会关系删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group_relation( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'group_id_a', $groupids ) . ' OR ' . DB::field( 'group_id_b', $groupids );
		DB::delete( $this->_table, $condition );
		return true;
	}
}

?>