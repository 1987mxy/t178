<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_member.php
 * @time	2012-12-15 上午3:26:09
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_member extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_member';
		$this->_pk    = 'group_memberid';

		parent::__construct();
	}
	
	/**
	 * 获取公会成员
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		公会成员信息
	 */
	public function get_group_member( $groupid ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT * FROM %t WHERE groupid=%d", array( $this -> _table, $groupid ) );
	}
	
	/**
	 * 贡献TCP值给公会
	 * @access	public
	 * @param	$uid		用户ID
	 * @param	$tcp		贡献的TCP值
	 * @return	boolean		贡献TCP操作结果
	 */
	public function contribute_tcp( $uid, $tcp ){
		if( empty( $uid ) || $tcp == 0 ) return false;
		DB::query( "UPDATE %t SET tcp=tcp-%d, contributed=contributed+%d WHERE " . DB::field( 'uid', $uid ), array( $this->_table, $tcp, $tcp ) );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 获得TCP值奖励
	 * @access	public
	 * @param	$uid		用户ID
	 * @param	$tcp		贡献的TCP值
	 * @return	boolean		获得TCP操作结果
	 */
	public function get_tcp( $uid, $tcp ){
		if( empty( $uid ) || $tcp == 0 ) return false;
		DB::query( "UPDATE %t SET tcp=tcp+%d WHERE " . DB::field( 'uid', $uid ), array( $this->_table, $tcp ) );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 获取公会成员TCP贡献榜单
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$number		显示数量
	 * @return	array		公会成员TCP贡献榜单
	 */
	public function get_group_contribute_list( $groupid, $number = 10 ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT uid, username, contributed FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " ORDER BY " . DB::order( 'contributed', 'DESC' ) . DB::limit( 0, $number ), array( $this -> _table, $number ) );
	}
	
	/**
	 * 获取公会财富榜单
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$number		显示数量
	 * @return	array		公会成员财富榜单
	 */
	public function get_group_capital_list( $groupid, $number = 10 ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT uid, username, capital FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " ORDER BY " . DB::order( 'capital', 'DESC' ) . DB::limit( 0, $number ), array( $this -> _table, $number ) );
	}
	
}

?>