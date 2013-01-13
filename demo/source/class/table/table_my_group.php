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
	 * 获取全部已建立的公会ID
	 * @access	public
	 * @return	array		已建立的公会ID列表
	 */
	public function get_full_group_info(){
		return DB::fetch_all( "SELECT groupid, fid, `status`, build_time, apply_time FROM %t", array( $this -> _table ) );
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
		$group_info = DB::fetch_first( "SELECT * FROM %t WHERE " . DB::field( 'fid', $fid ), array( $this -> _table ) );
		return empty( $group_info ) ? array() : $group_info;
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
			if( $group_fid['fid'] == $fid ) break;
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
			if( $group_fid['fid'] == $fid ) break;
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
	public function add_tcp( $fid, $tcp ){
		if( empty( $fid ) || $tcp == 0 ) return false;
		DB::query( "UPDATE %t SET tcp=tcp+%d WHERE " . DB::field( 'fid', $fid ), array( $this->_table, $tcp ) );
		return DB::affected_rows() ? true : false;
	}
	
	/**
	 * 公会删除
	 * @access	public
	 * @param	$groupids	公会ID列表
	 * @return	boolean		删除操作结果
	 */
	public function del_group( $groupids ){
		if( empty( $groupids ) ) {
			return false;
		}
		$condition = DB::field( 'groupid', $groupids );
		DB::delete( $this->_table, $condition );
		return DB::affected_rows() >= count( $groupids ) ? true : false;
	}
	
	/**
	 * 将Discuz的fids转换成t178的groupids
	 * @access	public
	 * @param	$fids	Discuz的fid列表
	 * @return	array	t178的公会ID列表
	 */
	public function fids2groupids( $fids ){
		if( empty( $fids ) ) {
			return array();
		}
		$groupids = DB::fetch_all( "SELECT groupid FROM %t WHERE " . DB::field( 'fid', $fids ), array( $this -> _table ) );
		foreach( $groupids as &$groupid ){
			$groupid = $groupid[ 'groupid' ];
		}
		return $groupids;
	}
	
	/**
	 * 将t178的groupids转换成Discuz的fids
	 * @access	public
	 * @param	$groupids	t178的公会ID列表
	 * @return	array		Discuz的fid列表
	 */
	public function groupids2fids( $groupids ){
		if( empty( $groupids ) ) {
			return array();
		}
		$fids = DB::fetch_all( "SELECT fid FROM %t WHERE " . DB::field( 'groupid', $groupids ), array( $this -> _table ) );
		foreach( $fids as &$fid ){
			$fid = $fid[ 'fid' ];
		}
		return $fids;
	}
	
	/**
	 * 通过公会申请
	 * @access	public
	 * @param	$fid		Discuz的fid
	 * @return	boolean		通过操作结果
	 */
	public function pass_group_application( $fid ){
		if( empty( $fid ) ) return false;
		$data = array( 'status' => 2,
						'build_time' => time() );
		$condition = array( 'fidd' => $fid );
		DB::update( $this -> _table, $data, $condition );
		return DB::affected_rows() ? true : false;
	}
}

?>