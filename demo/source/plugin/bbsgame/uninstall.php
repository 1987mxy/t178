<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
 
$sql = <<<EOF
 
DROP TABLE IF EXISTS `ng_game`;
DROP TABLE IF EXISTS `ng_game_credit`;
DROP TABLE IF EXISTS `ng_game_ip`;
DROP TABLE IF EXISTS `ng_game_shell`;
DROP TABLE IF EXISTS `ng_game_type`;
DROP TABLE IF EXISTS `ng_game_user`;
DROP TABLE IF EXISTS `ng_game_reply`;
DROP TABLE IF EXISTS `ng_game_pk`;
DROP TABLE IF EXISTS `ng_game_pker`;

EOF;
 
runquery($sql);
 
$finish = TRUE;
?>