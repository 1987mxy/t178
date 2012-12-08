<?php
$outDivConfig = array("width"=>"960px","height"=>"710px");
switch ($_GET['coopid']) {
	case 'jj':
		$iFrameConfig = array("width"=>'960px',"height"=>'700px',"src"=>'http://webgame.jj.cn/oem/index.php?fromid=13014');
		break;
	default:
		$iFrameConfig = array("width"=>'960px',"height"=>'700px');
		break;
}
?>