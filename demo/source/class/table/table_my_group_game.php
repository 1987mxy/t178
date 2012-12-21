<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_game.php
 * @time	2012-12-15 上午3:27:47
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_game extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_game';
		$this->_pk    = 'group_gameid';

		parent::__construct();
	}
	
	/**
	 * 获取公会入驻游戏
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	array		公会信息
	 */
	public function get_group_games( $groupid ){
		if( empty( $groupid ) ) return array();
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'groupid', $groupid ), array( $this -> _table ) );
	}
	
	/**
	 * 获取要验证的公会入驻游戏信息
	 * @access	public
	 * @return	array	要验证的公会入驻游戏信息
	 */
	public function get_verify_group_games(){
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'status', 1 ), array( $this -> _table ) );
	}
	
	/**
	 * 获取公会入驻指定游戏信息
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$gameid		游戏ID
	 * @return	array		公会入驻游戏信息
	 */
	public function get_group_game_info( $groupid, $gameid ){
		if( empty( $groupid ) || empty( $gameid ) ) return array();
		return DB::fetch_first( "SELECT * FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " AND " .
															DB::field( 'gameid', $gameid ), array( $this -> _table ) );
	}
	
	/**
	 * 公会申请入驻游戏
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$gameid		游戏ID
	 * @return	boolean		申请操作结果
	 */
	public function apply_join_game( $groupid, $gameid ){
		if( empty( $groupid ) ) return array();
		$join_game_data = array( 'groupid'			=> $groupid,
									'gameid'		=> $gameid,
									'status'		=> 1,
									'apply_time'	=> time() );
		return DB::insert( $this -> _table, $join_game_data, true );
	}
	
	/**
	 * 获取公会入驻游戏数列表
	 * @access	public
	 * @param	$begin_time		开始时间
	 * @param	$stop_time		结束时间
	 * @param	$groupids		公会ID列表
	 * @return	array			公会成员数量列表
	 */
	public function get_group_game_number( $begin_time, $stop_time = null, $groupids = null ){
		if( empty( $begin_time ) ) return array();
		$stop_time = $stop_time == null ? time() : $stop_time;
		$condition = DB::field( 'status', 2 ) . ' AND ' .
						DB::field( 'join_time', $begin_time, '<' ) . ' AND ' .
						DB::field( 'join_time', $stop_time, '>' ) . ' AND ' .
						( $groupids == null ? '' : DB::field( 'groupid', $groupids ) );
		$group_game_num = DB::fetch_all( "SELECT groupid, COUNT(gameid) AS game_num FROM %t WHERE " . $condition . " GROUP BY groupid", array( $this->_table ) );
		$game_num = array();
		foreach( $group_game_num as $group_game ){
			$game_num[ $group_game[ 'groupid' ] ] = $group_game[ 'signing_num' ];
		}
		return $game_num;
	}
	
	/**
	 * 公会入驻游戏通过
	 * @access	public
	 * @param	$group_gameid	公会游戏ID
	 * @return	boolean			通过操作结果
	 */
	public function pass_join_application( $group_gameid ){
		if( empty( $groupid ) ) return false;
		$now = time();
		$data = array( 'status' => 2, 'join_time' => $now );
		$condition = array( 'group_gameid' => $group_gameid );
		DB::update( $this -> _table, $data, $condition );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 公会入驻游戏拒绝
	 * @access	public
	 * @param	$group_gameid	公会游戏ID
	 * @return	boolean			拒绝操作结果
	 */
	public function reject_join_application( $group_gameid ){
		if( empty( $groupid ) ) return false;
		$data = array( 'status' => 3 );
		$condition = array( 'group_gameid' => $group_gameid );
		DB::update( $this -> _table, $data, $condition );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 查询是否已经入驻指定游戏
	 * @access	public
	 * @param	$groupid		公会ID
	 * @param	$gameid			游戏ID
	 * @return	boolean			是否已经入驻指定游戏
	 */
	public function had_joint( $groupid, $gameid ){
		if( empty( $groupid ) || empty( $gameid ) ) return false;
		$group_game_num = DB::fetch_first( "SELECT COUNT(group_gameid) AS group_game_num FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " AND " .
																											DB::field( 'gameid', $gameid ), array( $this->_table ) );
		return $group_game_num[ 'group_game_num' ] > 0;
	}
	
	/**
	 * 公会入驻游戏删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group_game( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'groupid', $groupids );
		DB::delete( $this->_table, $condition );
		return true;
	}
}

?>