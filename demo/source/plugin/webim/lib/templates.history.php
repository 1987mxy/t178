<?php

$date = isset( $_POST['date'] ) ? $_POST['date'] : date( 'Y-m-d' );
$client_time = (int) webim_gp("time");
$server_time = webim_microtime_float() * 1000;

function fix_time( $time ) {
	global $client_time, $server_time;
	if ( $time && $client_time ) {
		$time = $time - $server_time + $client_time;
	}
	return $time ? date( 'Y-m-d H:i', (float)$time/1000 ) : "";
}
header('Content-Disposition: attachment; filename="histories-'.$date.'.html"');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cn" lang="cn">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="index, follow" />
		<title>WebIM聊天记录</title>
		<style type="text/css">
			body{
				font-size: 12px;
				font-family: Arial,sans-serif;
				margin:0;
				color: #333;
			}
			#header{
				background: #EEE;
				border-bottom: 1px solid #DDD;
				padding: 1em;

			}
			#header h3{
				font-size: 1.8em;
				margin:0;
				margin-right: .5em;
				padding: 0;
				display: inline;
			}
			#header h3 a{
				color: #3F9FFF;
				text-decoration: none;
			}
			#header h2{
				display: inline;
				font-size: 1.25em;
				margin-left: .5em;
				font-weight: normal;
				color: #555;
				letter-spacing: 0.05em;
			}

			#content{
				padding:0 1em;
			}

			#log{
				overflow:auto;
			}

			#loglist{
				margin: 0;
				list-style:none;
				padding: 0;
			}

			.log-item{
				font-size: 14px;
				margin: 0.5em 0;
				color: #000;
			}

			.log-item .time{
				font-size: 12px;
				color: gray;
				padding-left: 1em;
			}

		</style>
	</head>
	<body>
			<div id="header">
				<h3>WebIM</h3>
				<h2>聊天记录 <?php echo $date ?></h2>
			</div>
			<div id="content">
				<div id="log">
					<ul id="loglist">
<?php 
foreach( $histories as $k => $log ):
?>
	<li class="log-item"><span class="name"><?php echo $log->nick ?>：</span><span style="<?php echo $log->style ?>"><?php echo $log->body ?></span><span class="time"><?php echo fix_time( $log->timestamp );?></span></li>
<?php
endforeach;
?>
					</ul>
				</div>
			</div>
	</body>
</html>
