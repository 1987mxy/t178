<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_member_game.php
 * @time	2012-12-22 上午2:17:47
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_member_game extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_member_game';
		$this->_pk    = 'group_member_gameid';

		parent::__construct();
	}

	/**
	 * 公会申请入驻游戏
	 * @access	public
	 * @param	$uid			Discuz的uid
	 * @param	$group_gameid	公会游戏ID
	 * @param	$groupid		公会ID
	 * @param	$game_serverid	游戏服务器ID
	 * @return	boolean			申请操作结果
	 */
	public function apply_join_game( $uid, $group_gameid, $groupid, $game_serverid ){
		if( empty( $groupid ) ) return array();
		$join_game_data = array( 'uid'				=> $uid,
									'group_gameid'	=> $group_gameid,
									'groupid'		=> $groupid,
									'game_serverid'	=> $game_serverid,
									'apply_time'	=> time() );
		return DB::insert( $this -> _table, $join_game_data, true );
	}

	/**
	 * 获取公会入驻游戏会员数列表
	 * @access	public
	 * @param	$begin_time		开始时间
	 * @param	$stop_time		结束时间
	 * @param	$groupids		公会ID列表
	 * @return	array			公会入驻游戏会员数列表
	 */
	public function get_group_member_game_join_number( $begin_time, $stop_time = null, $groupids = null ){
		if( empty( $begin_time ) ) return array();
		$stop_time = $stop_time == null ? time() : $stop_time;
		$condition = DB::field( 'join_time', $begin_time, '<' ) . ' AND ' .
						DB::field( 'join_time', $stop_time, '>' ) . ' AND ' .
						( $groupids == null ? '' : DB::field( 'groupid', $groupids ) );
		$group_member_game_join_num = DB::fetch_all( "SELECT group_gameid, COUNT(uid) AS member_game_join_num FROM %t WHERE " . $condition . " GROUP BY group_gameid", array( $this->_table ) );
		$member_game_join_num = array();
		foreach( $group_member_game_join_num as $group_member_game_join ){
			$member_game_join_num[ $group_member_game_join[ 'group_gameid' ] ] = $group_member_game_join[ 'member_game_join_num' ];
		}
		return $member_game_join_num;
	}
	
	/**
	 * 查询是否已经入驻指定游戏
	 * @access	public
	 * @param	$group_gameid	公会游戏ID
	 * @param	$uid			Discuz的uid
	 * @return	boolean			是否已经入驻指定游戏
	 */
	public function had_joint( $group_gameid, $uid ){
		if( empty( $group_gameid ) || empty( $uid ) ) return false;
		$group_game_member_num = DB::fetch_first( "SELECT COUNT(uid) AS group_game_member_num FROM %t WHERE " . DB::field( 'group_gameid', $group_gameid ) . " AND " .
																												DB::field( 'uid', $uid ), array( $this->_table ) );
		return $group_game_member_num[ 'group_game_member_num' ] > 0;
	}
	
	/**
	 * 公会会员入驻游戏删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group_member_game( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'groupid', $groupids );
		DB::delete( $this->_table, $condition );
		return true;
	}
	
	/**
	 * 清除入驻游戏
	 * @access	public
	 * @param	$uid		Discuz的uid
	 * @return	boolean		清除入驻游戏操作结果
	 */
	public function clear_games( $uid ){
		if( empty( $uid ) ) {
			return false;
		}
		$condition = DB::field( 'uid', $uid );
		DB::delete( $this->_table, $condition );
		return DB::affected_rows() ? true : false;
	}
}

?>