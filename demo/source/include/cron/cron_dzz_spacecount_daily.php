<?php

/**
 *      [Dzz!] (C)2010-2012 Dzz.cc
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todayviews_daily.php 26812 2011-12-23 08:21:29Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

	$buysize=0;
	$uidarr=array();
	$query=DB::query("select * from ".DB::table('dzz_space_record')." where 1");
	while($value=DB::fetch($query)){
		if($value['endtime']<$_G['timestamp']){
			$setarr=$value;
			unset($setarr['rid']);
			if(DB::insert('dzz_space_record_log',daddslashes($setarr),1)){
				DB::delete('dzz_space_record',"rid ='{$value[rid]}'");
			}
		}else{
			if(!$uidarr[$value['uid']]) $uidarr[$value['uid']]=0;
			$uidarr[$value['uid']]+=$value['spacesize']*$value['num'];
		}
	}
	foreach($uidarr as $uid =>$value){
		if(DB::result_first("select COUNT(*) from ".DB::table('dzz_userconfig_field')." where uid='{$uid}'")){
			DB::update('dzz_userconfig_field',array('buysize'=>$value),"uid='{$uid}'"); 
		}else{
			DB::insert('dzz_userconfig_field',array('buysize'=>$value,'uid'=>$uid),1,1); 
		}
	}
?>