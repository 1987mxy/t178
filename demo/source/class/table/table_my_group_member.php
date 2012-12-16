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
	
	
}

?>