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
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'groupid', $groupid ), array( $this -> _table ) );
	}
	
	/**
	 * 获取公会成员数量列表
	 * @access	public
	 * @param	$groupids		公会ID列表
	 * @return	array			公会成员数量列表
	 */
	public function get_group_member_number( $groupids = null ){
		if( empty( $begin_date ) ) return array();
		$condition = ( $groupids == null ? '' : DB::field( 'groupid', $groupids ) );
		$group_member_num = DB::fetch_all( "SELECT groupid, COUNT(uid) AS member_num FROM %t WHERE " . $condition . " GROUP BY groupid", array( $this->_table ) );
		$member_num = array();
		foreach( $group_member_num as $group_member ){
			$member_num[ $group_member[ 'groupid' ] ] = $group_member[ 'member_num' ];
		}
		return $member_num;
	}
	
	/**
	 * 获取会员信息
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$uid		Discuz的uid
	 * @return 	array		会员信息
	 */
	public function get_member_info( $groupid, $uid ){
		if( empty( $groupid ) || empty( $uid ) ) return array();
		return DB::fetch_first( "SELECT * FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " AND " . DB::field( 'uid', $uid ), array( $this -> _table ) );
	}
	
	/**
	 * 贡献TCP值给公会
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$uid		Discuz的uid
	 * @param	$tcp		贡献的TCP值
	 * @return	boolean		贡献TCP操作结果
	 */
	public function contribute_tcp( $groupid, $uid, $tcp ){
		if( empty( $uid ) || empty( $tcp ) ) return false;
		$condition = DB::field( 'uid', $uid ) . ' AND ' . DB::field( 'groupid', $groupid );
		$member_tcp = DB::fetch_first( "SELECT tcp FROM %t WHERE " . $condition, array( $this -> _table ) );
		if( $tcp > $member_tcp[ 'tcp' ] ) return false;
		DB::query( "UPDATE %t SET tcp=tcp-%d, contributed=contributed+%d WHERE " . $condition, array( $this->_table, $tcp, $tcp ) );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 获得TCP值奖励
	 * @access	public
	 * @param	$groupid	公会ID（因为有邀请加入的时间差存在，所以需要存入公会ID）
	 * @param	$uid		Discuz的uid
	 * @param	$tcp		贡献的TCP值
	 * @return	boolean		获得TCP操作结果
	 */
	public function get_tcp( $groupid, $uid, $tcp ){
		if( empty( $groupid ) || empty( $uid ) || empty( $tcp ) ) return false;
		DB::query( "UPDATE %t SET tcp=tcp+%d WHERE " . DB::field( 'uid', $uid ) . " AND " . DB::field( 'groupid', $groupid ), array( $this->_table, $tcp ) );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 获取公会成员TCP贡献榜单
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$number		显示数量
	 * @return	array		公会成员TCP贡献榜单
	 */
	public function get_member_contribute_list( $groupid, $number = 10 ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT uid, username, contributed FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " ORDER BY " . DB::order( 'contributed', 'DESC' ) . DB::limit( 0, $number ), array( $this -> _table ) );
	}
	
	/**
	 * 获取公会财富榜单
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$number		显示数量
	 * @return	array		公会成员财富榜单
	 */
	public function get_member_capital_list( $groupid, $number = 10 ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT uid, username, capital FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " ORDER BY " . DB::order( 'capital', 'DESC' ) . DB::limit( 0, $number ), array( $this -> _table ) );
	}
	
	/**
	 * 获取指定用户的公会ID
	 * @access	public
	 * @param	$uid	Discuz的uid
	 * @return	int		指定用户的公会ID
	 */
	public function get_user_groupid( $uid ){
		if( empty( $uid ) ) return false;
		return DB::fetch_first( "SELECT groupid FROM %t WHERE " . DB::field( 'uid', $uid ), array( $this -> _table ) );
	}
	
	/**
	 * 会员离开公会
	 * @access	public
	 * @param	$uid		Discuz的uid
	 * @return	boolean		离开公会操作结果
	 */
	public function leave_group( $uid ){
		if( empty( $uid ) ) {
			return false;
		}
		$condition = DB::field( 'uid', $uid );
		DB::delete( $this->_table, $condition );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 公会成员删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group_member( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'groupid', $groupids );
		DB::delete( $this->_table, $condition );
		return DB::affected_rows() >= count( $groupids ) ? true : false;
	}
}

?>