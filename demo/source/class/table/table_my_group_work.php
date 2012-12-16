<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_work.php
 * @time	2012-12-14 上午1:14:39
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_work extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_work';
		$this->_pk    = 'group_workid';

		parent::__construct();
	}
}

?>