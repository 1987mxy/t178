<?php
ini_set("display_errors", 1);
define('R_P', dirname(__FILE__));
define('H_P', R_P);

$ng_path = substr(H_P, 0, (strlen(H_P)-22));
include_once($ng_path."/source/class/class_core.php");
$discuz = & discuz_core::instance();
$discuz->init();

date_default_timezone_set('Asia/shanghai');

require_once H_P.'/require/baseException.class.php';
@include_once(H_P.'/require/functions.php');
@include_once(H_P.'/data/basic_config.php');
@include_once(H_P.'/require/security.php');

$ng_path = substr(H_P, 0, (strlen(H_P)-22));
include_once($ng_path."/source/class/class_core.php");
define('DEBUG', $pkgame_debug);    

$GLOBALS['pkgame_challenge_algorithm'] = $pkgame_challenge_algorithm;
$GLOBALS['creidt'] = $credit;

$uid = $_G['uid'];
$username = $_G['username'];
$icon = $_G['icon'];
$time = time();

S::gp(array('gid'), null, 2);
S::gp(array('action'));

$table_game = 'ng_game';
$table_ip = 'ng_game_ip';
$table_credit = 'ng_game_credit';
$table_pk   = 'ng_game_pk';
$table_pker = 'ng_game_pker';
$table_shell = 'ng_game_shell';
$table_reply = 'ng_game_reply';

//reply
if( $action == 'reply' ) {
	require H_P.'/actions/reply.act.php';
}//challenge
else if( $action == 'challenge') {
	require H_P.'/actions/challenge.act.php';
}//pk
else if( $action == 'pk' ) {
	require H_P.'/actions/pk.act.php';
}

































