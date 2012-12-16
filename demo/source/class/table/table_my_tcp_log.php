<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_tcp_log.php
 * @time	2012-12-15 上午3:23:19
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_tcp_log extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_tcp_log';
		$this->_pk    = 'tcp_logid';

		parent::__construct();
	}
}

?>