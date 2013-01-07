http://demo.t178.com/forum.php?mod=group&action=contribute&fid=[fid]&tcp=[tcp]
说明：会员捐献TCP请求（已测试）
参数：	fid	Discuz板块ID
	tcp	会员捐献的TCP


http://demo.t178.com/forum.php?mod=group&action=signing&fid=[fid]
说明：会员签到请求（已测试）
参数：	fid	Discuz板块ID


http://demo.t178.com/forum.php?mod=group&action=group_join_game&fid=[fid]&game_serverid=[game_serverid]
说明：公会入驻游戏请求（已测试）
参数：	fid		Discuz板块ID
	game_serverid	t178游戏服务器ID


http://demo.t178.com/forum.php?mod=group&action=group_member_join_game&fid=[fid]&game_serverid=[game_serverid]
说明：会员入驻游戏请求（未测试）
参数：	fid		Discuz板块ID
	game_serverid	t178游戏服务器ID


http://demo.t178.com/forum.php?mod=group&action=check_group
说明：公会申请审核，公会注入游戏申请审核，公会有效性审核（未测试）



http://demo.t178.com/forum.php?mod=group&action=group_relation&op=[op]&fid=[fid]&(friendly/enemy)_group_id=[groupid]
说明：操作公会关系
参数：	op	操作命令
		friend		建立友情公会(第三个参数为friendly_group_id)
		unfriend	解除友情公会(第三个参数为friendly_group_id)
		enemy		建立敌对公会(第三个参数为enemy_group_id)
		nonenemy	解除敌对公会(第三个参数为enemy_group_id)
	fid	Discuz板块ID
	groupid	建立公会关系的对象公会ID