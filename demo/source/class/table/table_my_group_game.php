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
		return $this -> fetch_all( $groupid );
	}
}

?>