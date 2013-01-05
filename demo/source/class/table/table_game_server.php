<?php

/**
 * @author	Moxiaoyong
 * @file	table_game_server.php
 * @time	2013-1-5 下午10:21:21
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_game_server extends discuz_table
{
	public function __construct() {

		$this->_table = 'game_server';
		$this->_pk    = 'server_id';

		parent::__construct();
	}
	
	/**
	 * 获取指定游戏服务器信息
	 * @access	public
	 * @param	$server_id	t178游戏服务器ID
	 * @return	array		t178游戏服务器信息
	 */
	public function get_game_server_info( $server_id ){
		if( empty( $server_id ) ) {
			return array();
		}
		$game_server_info = DB::fetch_first( "SELECT server_id,
														game_id,
														server_no,
														server_name,
														server_logo,
														server_line,
														server_state,
														server_is_best,
														server_is_pay
												FROM %t WHERE " . DB::field( 'server_id', $server_id ), array( $this -> _table ) );
		return empty( $game_server_info ) ? array() : $game_server_info;
	}
}

?>