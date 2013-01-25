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
		$condition = DB::field( 'date', $begin_date, '>=' ) . ' AND ' .
						DB::field( 'date', $stop_date, '<=' ) . ' AND ' .
						( $groupids == null ? '' : DB::field( 'groupid', $groupids ) );
		$group_signing_num = DB::fetch_all( "SELECT groupid, COUNT(uid) AS signing_num FROM %t WHERE " . $condition . " GROUP BY groupid ORDER BY " . DB::order( 'signing_num', 'DESC' ), array( $this->_table ) );
		$signing_num = array();
		if( is_array( $groupids ) || $groupids == null ){
			foreach( $group_signing_num as $group_signing ){
				$signing_num[ $group_signing[ 'groupid' ] ] = $group_signing[ 'signing_num' ];
			}
		}else{
			$signing_num = $group_signing_num[ 0 ][ 'signing_num' ] ? $group_signing_num[ 0 ][ 'signing_num' ] : 0;
		}
		return $signing_num;
	}
	
	/**
	 * 获取该公会在指定日期的签到排名
	 * @access	public
	 * @param	$groupid	公会ID
	 * @param	$date		签到日期
	 * @return	int			该日期的签到排名
	 */
	public function get_signing_rank( $groupid, $date = null ){
		if( empty( $groupid ) ) return false;
		$date = $date == null ? strftime( "%Y-%m-%d" ) : $date;
		$signing_number = $this -> get_signing_number( $date, $date, $groupid );
		if( $signing_number == 0 ) return false;
		$condition = DB::field( 'date', $date );
		$signing_group_list = DB::fetch_all( "SELECT groupid, 
														COUNT(uid) AS signing_num 
												FROM %t 
												WHERE " . $condition . " 
												GROUP BY groupid 
												ORDER BY " . DB::order( 'groupid' ), array( $this->_table ) );
		$signing_rank = 1;
		foreach( $signing_group_list as $signing_group ){
			if( $signing_group[ 'groupid' ] == $groupid ){
				return $signing_rank;
			}
			$signing_rank += 1;
		}
		return false;
	}
	
	/**
	 * 获取指定日期的公会签到排名列表
	 * @access	public
	 * @param	$date		签到日期
	 * @param	$number		获取排名条目数
	 * @return	array		签到排名列表
	 */
	public function get_signing_group_rank_list( $date = null, $number = 0 ){
		$date = $date == null ? strftime( "%Y-%m-%d" ) : $date;
		$condition = DB::field( 'date', $date );
		$sql = "SELECT forum.name AS name,
						my_group.groupid AS groupid,
						my_group.fid AS fid,
						forumfield.icon AS icon,
						forumfield.description AS description,
						COUNT(my_group_signing.uid) AS signing_num 
				FROM %t AS my_group_signing 
				LEFT JOIN " . DB::table( 'my_group' ) . " AS my_group ON my_group_signing.groupid=my_group.groupid 
				LEFT JOIN " . DB::table( 'forum_forum' ) . " AS forum ON my_group.fid=forum.fid 
				LEFT JOIN " . DB::table( 'forum_forumfield' ) . " AS forumfield ON my_group.fid=forumfield.fid 
				WHERE " . $condition . " 
				GROUP BY groupid 
				ORDER BY " . DB::order( 'signing_num', 'DESC' ) . ", " . DB::order( 'groupid' ) . 
				DB::limit( 0, $number );
		$signing_group_rank_list = DB::fetch_all( $sql, array( $this->_table ) );
		return $signing_group_rank_list;
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