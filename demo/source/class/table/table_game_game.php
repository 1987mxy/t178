<?php

/**
 * @author	Moxiaoyong
 * @file	table_game_game.php
 * @time	2012-12-28 下午11:07:56
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_game_game extends discuz_table
{
	public function __construct() {
	
		$this->_table = 'game_game';
		$this->_pk    = 'game_id';
	
		parent::__construct();
	}
	

	/**
	 * 获取所有t178游戏信息
	 * @access	public
	 * @return	array		t178游戏信息
	 */
	public function get_all_game_info(){
		$game_info = DB::fetch_all( "SELECT game_id, 
											game_type, 
											game_no, 
											game_name, 
											game_logo, 
											game_depict, 
											game_website 
										FROM %t", array( $this -> _table ) );
		$cleanup_game_info = array(); 
		foreach( $game_info as $game ){
			$cleanup_game_info[ $game[ 'game_id' ] ] = $game;
		}
		return $cleanup_game_info;
	}
}