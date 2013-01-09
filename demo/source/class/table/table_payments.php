<?php

/**
 * @author	Moxiaoyong
 * @file	table_payments.php
 * @time	2013-1-9 下午10:56:44
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_payments extends discuz_table
{
	public function __construct() {

		$this->_table = 'payments';
		$this->_pk    = 'id';

		parent::__construct();
	}

	/**
	 * 插入数据操作
	 * @access	public
	 * @param	$data	插入数据
	 * @return	int		主键ID
	 */
	public function insert( $data ){
		if( empty( $data ) ) return false;
		$payment_info = array( 'id'			=> $data['id'], 
								'uid'		=> $data['uid'], 
								'uname'		=> $data['uname'], 
								'amount'	=> $data['amount'], 
								'created'	=> $data['created'], 
								'type'		=> $data['type'], 
								'status'	=> $data['status'] );
		return DB::insert( $this -> _table, $payment_info, true );
	}
	
	/**
	 * 更具主键查询一条记录
	 * @access	public
	 * @param	$id		主键
	 * @return	array	记录
	 */
	public function fetchone( $id ){
		return $this -> fetch( $id );
	}
	
	/**
	 * 查询所有记录
	 * @access	public
	 * @return	array	记录集
	 */
	public function fetchall(){
		return DB::fetch_all( "SELECT * FROM %t", array( $this -> _table ) );
	}
	
	/**
	 * 重置数据表
	 * @access	public
	 * @return	null		空
	 */
	public function reset_table(){
		DB::query( 'DELETE FROM ' . DB::table( $this -> _table ) );
		return null;
	}
}

?>