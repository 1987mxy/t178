<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_store.php
 * @time	2012-12-14 上午1:14:09
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_store extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_store';
		$this->_pk    = 'group_storeid';

		parent::__construct();
	}
}

?>