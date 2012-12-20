<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group_signing.php
 * @time	2012-12-21 上午1:29:57
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group_signing extends discuz_table
{
	public function __construct() {

		$this->_table = 'my_group_signing';
		$this->_pk    = 'group_signingid';

		parent::__construct();
	}
	
	/**
	 * 获取公会签到数
	 * @access	public
	 * @param	$begin_date		开始日期
	 * @param	$stop_date		结束日期
	 * @param	$groupids		公会ID列表
	 * @return	
	 */
	public function get_signing_number( $begin_date, $stop_date = null, $groupids = null ) {
		if( empty( $begin_date ) ) return array();
		$stop_date = $stop_date == null ? strftime( '%Y-%m-%d' ) : $stop_date;
		$condition = DB::field( 'signing_date', $begin_date, '<' ) . ' AND ' .
						DB::field( 'signing_date', $stop_date, '>' ) . ' AND ' .
						( $groupids == null ? '' : DB::field( 'groupid', $groupids ) );
		$group_signing_num = DB::fetch_all( "SELECT groupid, COUNT(uid) AS signing_num FROM %t WHERE " . $condition . " GROUP BY groupid", array( $this->_table ) );
		$signing_num = array();
		foreach( $group_signing_num as $group_signing ){
			$signing_num[ $group_signing[ 'groupid' ] ] = $group_signing[ 'signing_num' ];
		}
		return $signing_num;
	}
	
	/**
	 * 查询是否已经签过到
	 * @access	public
	 * @param	$groupid		公会ID
	 * @param	$uid			Discuz的uid
	 * @param	$date			查询日期
	 * @return	boolean			是否已经签过到
	 */
	public function had_signed( $groupid, $uid, $date = null ){
		if( empty( $groupid ) || empty( $uid ) ) return false;
		$date = $date == null ? strftime( '%Y-%m-%d' ) : $date;
		$signing_num = DB::fetch_first( "SELECT COUNT(group_signingid) AS signing_num FROM %t WHERE " . DB::field( 'groupid', $groupid ) . " AND " .
																										DB::field( 'uid', $uid ) . " AND " .
																										DB::field( 'date', $date ), array( $this->_table ) );
		return $signing_num[ 'signing_num' ] > 0;
	}
}

?>