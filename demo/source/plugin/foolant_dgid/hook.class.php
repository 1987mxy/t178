<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
} 

class plugin_foolant_dgid {
} 

class plugin_foolant_dgid_group extends plugin_foolant_dgid {
	function index_hook() {
		global $_G; 
		$config = getglobal('cache/plugin/foolant_dgid');
		$idtype = $config['idtype'];
		if(empty($_GET[$idtype]) && $config['enable']) {
			$_G['gp_'.$idtype] = $_GET[$idtype] = intval($config['DefaultGroupID']);
		} 
	} 
}