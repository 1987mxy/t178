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
		return DB::fetch_all( "SELECT * FROM %t WHERE groupid=%d", array( $this -> _table, $groupid ) );
	}
	
	/**
	 * 公会入驻游戏通过
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	boolean		通过操作结果
	 */
	public function pass_join_application( $groupid ){
		if( empty( $groupid ) ) return false;
		$now = time();
		$data = array( 'status' => 2, 'join_time' => $now );
		$condition = array( 'groupid' => $groupid );
		DB::update( $this -> _table, $data, $condition );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 公会入驻游戏拒绝
	 * @access	public
	 * @param	$groupid	公会ID
	 * @return	boolean		拒绝操作结果
	 */
	public function reject_join_application( $groupid ){
		if( empty( $groupid ) ) return false;
		$data = array( 'status' => 2 );
		$condition = array( 'groupid' => $groupid );
		DB::update( $this -> _table, $data, $condition );
		return DB::affected_rows() ? true : false;
	}
}

?>