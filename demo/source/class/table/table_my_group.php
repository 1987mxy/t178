<?php

/**
 * @author	Moxiaoyong
 * @file	table_my_group.php
 * @time	2012-12-14 上午1:11:53
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_my_group extends discuz_table
{
	public function __construct() {

		$this -> _table = 'my_group';
		$this -> _pk = 'groupid';

		parent::__construct();
	}
	
	/**
	 * 获取公会信息
	 * @access	public
	 * @param	$fid		Discuz的fid
	 * @return	array		公会信息
	 */
	public function get_group_info( $fid ){
		if( empty( $fid ) ) {
			return array();
		}
		return DB::fetch_all( "SELECT * FROM %t WHERE " . DB::field( 'fid', $fid ), array( $this -> _table ) );
	}
	
	/**
	 * 获取公会公告
	 * @access	public
	 * @param	$fid		Discuz的fid
	 * @return	string		公会公告
	 */
	public function get_group_notice( $fid ){
		if( empty( $fid ) ) {
			return '';
		}
		$notice = DB::fetch_first( "SELECT notice FROM %t WHERE " . DB::field( 'fid', $fid ), array( $this -> _table ) );
		return empty( $notice ) ? '' : $notice[ 'notice' ];
	}
	
	/**
	 * 获取公会TCP排名
	 * @access	public
	 * @param	$fid		Discuz的fid
	 * @return	int			公会TCP排名
	 */
	public function get_tcp_rank( $fid ){
		if( empty( $fid ) ) {
			return 0;
		}
		$group_fids = DB::fetch_all( "SELECT fid FROM %t ORDER BY " . DB::order( 'tcp', 'DESC' ), array( $this -> _table ) );
		$rank = 0;
		foreach( $group_fids as $group_fid ){
			$rank += 1;
			if( $group_fid == $fid ) break;
		}
		return count( $group_fids ) < $rank ? 0 : $rank;
	}
	
	/**
	 * 获取公会财富值排名
	 * @access	public
	 * @param	$fid		Discuz的fid
	 * @return	int			公会财富值排名
	 */
	public function get_capital_rank( $fid ){
		if( empty( $fid ) ) {
			return 0;
		}
		$group_fids = DB::fetch_all( "SELECT fid FROM %t ORDER BY " . DB::order( 'capital', 'DESC' ), array( $this -> _table ) );
		$rank = 0;
		foreach( $group_fids as $group_fid ){
			$rank += 1;
			if( $group_fid == $fid ) break;
		}
		return count( $group_fids ) < $rank ? 0 : $rank;
	}
	
	/**
	 * 公会获得TCP值
	 * @access	public
	 * @param	$fid		Discuz的fid
	 * @param	$tcp		获得TCP值
	 * @return	boolean		公会TCP值获得操作结果
	 */
	public function get_tcp( $fid, $tcp ){
		if( empty( $fid ) || $tcp == 0 ) return false;
		DB::query( "UPDATE %t SET tcp=tcp+%d WHERE " . DB::field( 'fid', $fid ), array( $this->_table, $tcp ) );
		return DB::affected_rows() ? true : false;
	}
}

?>