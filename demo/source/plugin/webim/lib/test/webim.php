<?php

/**
 * WebIM PHP Lib Test
 *
 * Author: Hidden
 *
 * First config, and then run it.
 *
 */

$domain = "monit.cn";
$apikey = "p";
$host = "192.168.1.16";
$port = "8000";

require_once(dirname(__FILE__).'/../webim.class.php');
$test = (object)array("id" => 'test', "nick" => "Test", "show" => "available");
$susan = (object)array("id" => 'susan', "nick" => "Susan", "show" => "available");
$jack = (object)array("id" => 'jack', "nick" => "Jack", "show" => "available");

$large_buddies = array("susan", "josh");

for($i = 0; $i < 2000; $i++){
	$large_buddies[] = "test_buddy_$i";
}

$large_buddies = implode(",", $large_buddies);
echo "online_buddy_size: ".strlen($large_buddies);


$im_test = new WebIM($test, null, $domain, $apikey, $host, $port);
$im = new WebIM($susan, null, $domain, $apikey, $host, $port);
$im->online("jack,josh", "room1,room2");

$im = new WebIM($jack, null, $domain, $apikey, $host, $port);

//var_export($im);
echo "\n\n\nWebIM PHP Lib Test\n";
echo "===================================\n\n";

$count = 0;
$error = 0;
$res = $im_test->check_connect();
debug($res->success, "check_connect", $res);

$res = $im->online($large_buddies, "room1");
debug($res->success, "online", $res);

$res = $im->presence("dnd", "I'm buzy now.");
debug($res == "ok", "presence", $res);

$res = $im->message("unicast", "susan", "Hello.");
debug($res == "ok", "message", $res);

$res = $im->status("susan", "inputting...");
debug($res == "ok", "status", $res);

$res = $im->join("room2");
debug($res, "join", $res);

$res = $im->leave("room2");
debug($res == "ok", "leave", $res);

$res = $im->members("room1");
debug($res, "members", $res);

$res = $im->offline();
debug($res == "ok", "offline", $res);

$res = $im->online("", "");
debug($res->success && empty($res->rooms) && empty($res->buddies), "online with empty room and buddy", $res);

echo "===================================\n";
$succ = $count - $error;
echo "$count test, $succ pass, $error error.\n\n";

function debug($succ, $mod, $res){
	global $count, $error;
	$count++;
	echo "$mod: ";
	if(is_string($res)){
		echo $res;
	}else{
		echo json_encode($res);
	}
	echo "\n";
	if($succ){
		echo "------------------------------------\n\n";
	}else{
		$error++;
		echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n\n";
	}
}


?>
