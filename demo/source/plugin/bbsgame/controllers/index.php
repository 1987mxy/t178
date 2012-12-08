<?php
/*
* 
* Jul 20, 2012
* GBK
* 1:54:37 PM
* AgudaZaric
* index.php
*/
include_once BASE_PATH.'/lib/PkController.php';
include_once BASE_PATH.'/controllers/indexController.php';
S::gp("action");
empty($action) && $action = 'index';
$c = new IndexController();
$c->run($action);
?>