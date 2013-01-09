<?php

/**
 * @author	Moxiaoyong
 * @file	test_payments.php
 * @time	2013-1-9 下午11:26:45
 */

define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');

require './source/class/class_core.php';

C::app()->init();

C::t('payments')->reset_table();

$test_data = array( 'id'		=> '1', 
					'uid'		=> $_G['uid'], 
					'uname'		=> $_G['username'], 
					'amount'	=> 0, 
					'created'	=> strftime( "%Y-%m-%d %H-%M-%S" ), 
					'type'		=> 'unknow', 
					'status'	=> 'unknow' );

for( $i = 1; $i <= 10; $i+=1 ){
	$test_data['id'] = $i;
	$test_data['amount'] = rand( 0, 100 );
	C::t('payments')->insert( $test_data );
} 

echo 'insert done';

echo '<pre>';

$fetchone_result = C::t('payments')->fetchone( rand( 1, 10 ) );

print_r( $fetchone_result );

echo '<br>=========================<br><br>';

$fetchall_result = C::t('payments')->fetchall( 1 );

print_r( $fetchall_result );

echo '</pre>';

?>