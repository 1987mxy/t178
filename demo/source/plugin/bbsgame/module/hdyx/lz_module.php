<?php
$account = 255;	// ����������
	$key = "EB84A3E9-095B-4678-A49A-F56582B8770E";	// ��Կ
	$gameId['mj'] = 23081;		// �����齫��Ϸ��
	$gameId['pw'] = 25011;		// �����˿���Ϸ��
	$gameId['wgh'] = 25010;		// ���춷������Ϸ��
	$gameId['fish'] = 23091;		// ��ŭ�������Ϸ��
	$time = date("YmdHis");	// ע����Ҫ��ʱ�����ʽ
		
	$number = $_GET['coopid'];
	$g_id = $gameId[$number];		// ѡ�����Ϸ
	$sign = md5(strtoupper($account."".$g_id."".$time."".$key));	// �����ֶ�
	// �䷢Ticket����
	$TickerUrl = "http://wgh.lianzhong.com/Services/RequestTicket.ashx?ChannelID={$account}&GameID={$g_id}&Timestamp={$time}&Sign={$sign}";
	$xml = file_get_contents($TickerUrl);
	$xml_object = simplexml_load_string($xml);
	$stats = (int)$xml_object->Stats;		//	���״̬
	if ($stats === 0){
		$TiketKey = (string)$xml_object->Data;	// �����Ϸ��Կ
			
		$Ntime = date("YmdHis");	// ������Ϸ��Ҫ��ʱ���
		$num = rand(1,10000);
		$UserID = $pid."_".$_G[uid];
		$Nsign = md5(strtoupper($account."".$g_id."".$UserID."0".$Ntime."".$key));	// �����ֶ�
		// Iframe Ƕ���õ���Ϸ URL
		$gameUrl = "http://wgh.lianzhong.com/Services/RequestGame.ashx?ChannelID={$account}&GameID={$g_id}&UserID={$UserID}&CMStatus=0&Timestamp={$Ntime}&Ticket={$TiketKey}&Version=1&Charset=UTF8&Sign={$Nsign}";
	}
?>