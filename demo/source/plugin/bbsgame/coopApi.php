<?php
	header("Content-type:text/html;Charset=gbk");
	set_time_limit(0);
	define('R_P',substr(dirname(__FILE__), 0, strpos( dirname(__FILE__), DIRECTORY_SEPARATOR.'hack')).DIRECTORY_SEPARATOR);
	define('H_P',R_P.'hack/bbsgame/');
	include_once(R_P.'global.php');
	include_once(H_P.'require/functions.php');
	include_once H_P.'data/api_config.php';
	error_reporting(E_ERROR);
	ob_start();
	if(!isset($_GET['name']) || !isset($_GET['num']))
		exit('forbidden');
	echo '������...<br>';
	ob_flush();
	flush();
	$name = trim($_GET['name']);
	$num = intval($_GET['num']);
	$data = ng_get_contents(API_HOST.'/coop/php/'.$name.'.txt');
	if(empty($data) || !preg_match("/<?php.*/", $data))
		exit('����:�շ��ػ������޺����ǩ');
	if(writeover(H_P.'coop/'.$name.'.php', $data, 'wb+'))
	{
		$dst = H_P.'coop/num.php';
		if(writeover($dst, "<?php\r\nreturn $num;", 'wb+'))
			echo '��ɣ�';
		else if (!pwWritable($dst) && !chmod($dst, 0777))
			echo "��ȷ��$dst Ϊ��дȨ��";
	}else 
		echo 'д���ļ�ʧ��:coop/$name';