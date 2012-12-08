<?php
/*
* 
* Jul 20, 2012
* GBK
* 1:45:47 PM
* AgudaZaric
* indexController.php
*/

class IndexController extends PkController {
	
	function IndexController() {
		global $db_bbsname,$pkgame_name;
		$this->anchor = array('db_bbsname'=>$db_bbsname, 'pkgame_name'=>$pkgame_name);		
	}
	
	
	function actionIndex() {
		global $pk_ctype,$mode,$db_charset;
		
		$hotPlayedCount = 6;
		$jfpkGageCount = 16;
		$query = DB::query('SELECT G.name,G.gid,G.img,COUNT(*) AS count FROM ng_game_ip AS I LEFT JOIN ng_game AS G ON I.gid = G.gid GROUP BY I.gid ORDER BY count DESC LIMIT '.$hotPlayedCount);
		
		while($result = DB::fetch($query)){
			if (empty($result['img']) || empty($result['name'])) continue;
			$result['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $result['img']);
			$hotPlayedLists[] = $result;
		}
		

		include(H_P.'/data/jljf_config.php');

		$J_cnf = implode(",", $J_cnf);
		!$J_cnf && $J_cnf = 0;
		$games = getGameByWhere("WHERE `gid` NOT IN($J_cnf) ORDER BY `gid` DESC LIMIT $jfpkGageCount");
		foreach($games as $i=>$game) {
			$games[$i][img] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $game['img']);
		}
		$mode['JFPK'][0]['games'] = $games;
		
		foreach( $mode['JLJF'] as $k=>$v ) {
			empty($v[gids]) && $v[gids] = 0;
			empty($v[tids]) && $v[tids] = 0;
			$sta = "`gid` IN ($v[gids]) OR `type` IN ($v[tids])";
			$games = getGameByWhere("WHERE $sta order by `gid` desc LIMIT 7");
			foreach($games as $i=>$game) {
				$games[$i][img] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $game['img']);
			}
			$mode['JLJF'][$k]['games'] = $games;
		}
		$rankingGame = getRankingGame("LIMIT 10");
		
		$userActive = getUserActive("LIMIT 10");
		
		try{
			$challenge = new challenge();
			$table_pk   = 'ng_game_pk';
   		    $table_pker = 'ng_game_pker';
			$table_game = 'ng_game';
			$strSQL = "SELECT pk.pkname,pk.status,pk.gamename,pk.starttime,pk.endtime,pk.id FROM {$table_pk} AS pk 
					WHERE pk.gid = ".intval($pkgame_active_gameid)." AND pk.type = ".challenge::PK_SITENAL." ORDER BY pk.id DESC";
			$siteActive = DB::fetch($strSQL);
				
			if(!empty($siteActive)){
				$siteActive['activetime'] = date("Y年m月d日", $siteActive['starttime'])."-".date("m月d日", $siteActive['endtime']);
				if ($siteActive['status'] == challenge::PK_STATUS_ING) {
					$siteActive['status'] = '进行中。。';
				}
				if ($siteActive['endtime'] < time()) {
					$siteActive['status'] = '已过期';
				}
				$strSQL = "SELECT * FROM {$table_pker} WHERE act_score > 0 AND pkid = $siteActive[id] ORDER BY act_score desc LIMIT 4";
				while( $result = DB::fetch($strSQL) ) {
					$arr[] = $result;
				}
				$partakeUsers = $arr;
			}
			unset($challenge);
		}
		catch (challengeException $e){
			$e->log();
		}
		if($db_charset == 'gbk')
			//$ggjs = 'http://www.97sng.com/public/bbs/bbsgamegg_gbk.js';
			$i=0;
		else
			//$ggjs = 'http://www.97sng.com/public/bbs/bbsgamegg_utf.js';
			$i=0;
		if(!empty($_G['uid'])) {
			$strSQL = "SELECT `lose_num`,`win_num` FROM ng_game_user WHERE uid = $_G[uid]";
			$registerInfo = DB::fetch($strSQL);
		} else 
			$_G['uid'] = 0;
		preg_match("/\/.*\//", $_SERVER['REQUEST_URI'], $matches);
		$coopUrl = 'http://'.$_SERVER[HTTP_HOST].$matches[0];

		include template('bbsgame:index');
		exit;
	}
	
	function actionGame() {
		S::gp("mode");
		S::gp(array('pk_id','gid'),'G',2);
		global $winddb, $mode, $pk_id, $gid, $pkgame_isdownload,$pk_ctype,$credit,$newPknoticeNum;
		
		$uid = $winddb['uid'];
		empty($uid) && $uid = '0';
		
		@include_once(R_P.'require/credit.php');
		ng_baseLoader::loadClass('game','require');
		ng_baseLoader::loadClass('challenge','require');
		
		if ( $winddb[uid] === 1 || in_array($winddb['groupid'],array(3,4,5)) ) $admintoken = 1;
		
		$game = new game($gid);
		$gameInfo = $game->getAll();
		!$gameInfo['installed'] && Showmsg('游戏未安装或已删除。');
		$gameInfo['shellUrl'] = preg_replace('/.swf/', '_v2.swf',$gameInfo['shellUrl']);
		if ($pkgame_isdownload == 'on' && file_exists(H_P."data/".$gameInfo['shellUrl']) && file_exists(H_P."data/".$gameInfo['swf'])) {
			$host = 'hack/bbsgame/data/';
		}
		else {
			$host = SWF_HOST;
		}
		
		$gameInfo['shellUrl'] = $host.'/'.$gameInfo['shellUrl']."?t=".time();
		$gameInfo['swf'] = $host.'/'.$gameInfo['swf'];
		$gameInfo['img'] = PIC_HOST.'/'.$gameInfo['img'];
		$gameInfo[ctype] = $credit->cType[$gameInfo[ctype]];
		$recommendGames = $game->select("g.*,s.url as shell")->from(game::$table_game." g,".game::$table_shell." s")
						->where("g.shellId = s.id AND g.type = ".$game->getTypeid()." AND g.gid != ".$game->getId())
						->limit(7)
						->get_rows();
		foreach($recommendGames as $k=>$v) {
			$recommendGames[$k]['img'] = PIC_HOST.'/'.preg_replace('/_120.jpg/', '.jpg', $v['img']);
		}
		$pkRankingUser = getPkRankingUser($gid, "LIMIT 10");
	
		$challenge = new challenge();
		$challenge->select("*")->from(challenge::$table_pk)->where("gid = $gid AND type= ".challenge::PK_NATIONAL." AND endtime < ".time())->query();
		if ($challenge->query_number_rows)
			$nationalActive = 9;//活动标记
		else
			$nationalActive = '0';
		
		$page = isset($_GET['p']) && !empty($_GET['p']) ?intval($_GET['p']):1;
		
		$pageSize = 10;
		$scope = 3;
		$url = $_SERVER["SCRIPT_NAME"]."?gid=$gid&p=";
		$count = getReplyCount($gid);
		$replylist = getReplyList($gid, "LIMIT ".($page-1)*$pageSize.", $pageSize");
		
		@include_once(H_P."require/pageCss.class.php");
		$pageCss = new PageCss($pageSize, $scope, $url, "#list");
		$pageCss->setCounts($count);
		$pageCss->setCurrentpage($page);
		$pageCss = $pageCss->getHTML(false);
		
		if (!empty($winddb['uid'])) 
		{
			//mode:pk normal challenge
			$modetypes = array("pk"=>"挑战","challenge"=>"擂台赛");
			if (!empty($mode))
				Cookie('bbsgame_mode',$mode);
			else
				Cookie('bbsgame_mode','');
			if ($mode == 'pk') 
			{
				try {
					if (!empty($pk_id) ) 
					{
						$info = $challenge->getPkInfo($pk_id);
						$pkerWithUid = $challenge->select("*")->from(challenge::$table_pker)
												->where("pkid=$pk_id")
												->limit(1)->get_row();
						//def_user
						if ($info['uid'] == $winddb['uid'] )
						{
							if ($info['status'] == challenge::PK_STATUS_MAKESURE) 
							{
								$challenge->update(challenge::$table_pk, array("status"=>challenge::PK_STATUS_ING))
										->where("id=$pk_id")
										->query();
								$credit->set($info['uid'], $info['case_ctype'], -1*$info['case'], true);//扣除防御者积分
							}
							$fightUser = $challenge->select("act_username AS username,act_score AS score,act_uid AS uid")
													->from(challenge::$table_pker)
													->where("pkid = $pk_id")
													->get_row();
							$pkerWithUid['score'] = $pkerWithUid['def_score'];
						}//act_user
						else 
						{
							$fightUser = array("username"=>$info['username'],"uid"=>$info['uid'],"score"=>$info['score']);
							$pkerWithUid['score'] = $pkerWithUid['act_score'];
						}
					}
					unset($challenge);
				}catch (challengeException $e) {
					$e->log();
				}
				$mode_type = $modetypes[$mode];
			}
			elseif ($mode == 'challenge') 
			{
				try{
					$challenge = new challenge();
					if (!empty($pk_id) ) 
					{
						$info  =  $challenge->select("pk.*, m.icon")
									->from(challenge::$table_pk, "pk")
									->left("pw_members", "m")
									->on("pk.uid = m.uid")
									->where("pk.id = $pk_id")
									->limit(1)->get_row();
						$pkerWithUid = $challenge->select("act_score as score")->from(challenge::$table_pker)
									->where("pkid=$pk_id AND act_uid = $winddb[uid]")
									->limit(1)->get_row();
						//用户尚未加入有效擂台
						if (empty($pkerWithUid) && $info['uid'] != $winddb['uid'] && $info['status'] != challenge::PK_STATUS_OK && $info['endtime'] > time()) 
						{
							$act_user = array("uid"=>$winddb['uid'], "username"=>S::sqlEscape($winddb['username']),"usericon"=>S::sqlEscape($winddb['icon']));
							$def_user = array("uid"=>$info['uid'], "username"=>S::sqlEscape($info['username']),"usericon"=>S::sqlEscape($info['icon']));
							if ($challenge->join($info['id'], $act_user, $def_user, $info['gid'])) 
								$credit->set($act_user['uid'], $info['case_ctype'], -1*$info['case'], true);//扣除积分
						}
						empty($pkerWithUid['score']) && $pkerWithUid['score'] = 0;
						$fightUser = array("username"=>$info['username'],"uid"=>$info['uid'],"score"=>$info['score']);
					}
					unset($challenge);
				}
				catch (challengeException $e) {
					$e->log();
				}
				$mode_type = $modetypes[$mode];
			}
			$playedStatistics = $game->get_row("SELECT IFNULL(MAX(score),0) AS max, IFNULL(MIN(score),0) AS min FROM ng_game_credit WHERE uid = $winddb[uid] AND gid = $gid");
		}
		$game_select = "ctrl_selected";
		$theurl = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$anchor = $gameInfo['name'];
		include_once PrintHack('content');
// 		$this->render('content' ,get_defined_vars());
	}
	
	public function actionChallenge()
	{
		S::gp(array("AJAX","r_c_t",'page'),'G');
		global $AJAX,$page,$r_c_t, $winddb,$windid,$newPknoticeNum;
		ng_baseLoader::loadClass('challenge','require');
		$challenge = new challenge();
		$gid = $challenge->select("gid")->from(challenge::$table_pk)->where("type = ".challenge::PK_NATIONAL)->order('starttime DESC')->get_value();
		
		empty($page) && $page = 1;
		$request_challenge_type = $r_c_t;
		$scope = C_SCOPE;
		$pageSize = C_PS;
		$start = ($page-1)*$pageSize;
		$url = $_SERVER[SCRIPT_NAME]."?action=challenge&page=";
			
		// 擂台
		try{
			$challenge->select("pk.*")->from(challenge::$table_pk." AS pk")->where("pk.type <> ".challenge::PK_PK)->query();
			$count = $challenge->query_number_rows();
	
			if ($AJAX)
			{
				ob_clean();
				if ($request_challenge_type == 'personal')
				{
					$personalPkLists = $challenge->select("pk.id,pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate, g.img,g.gid,g.name")
												->from(challenge::$table_pk, "pk")
												->left(challenge::$table_game, "g")
												->on("pk.gid = g.gid")
												->where("pk.type = ".challenge::PK_PERSONAL)
												->order('pk.ctime DESC')
												->limit("$start, $pageSize")
												->get_rows();
			
					challengeAjaxOutput($personalPkLists);
			
				}
				elseif ($request_challenge_type == 'sitenal') {
			
					$sitenalPkLists = $challenge->select("pk.id,pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate, g.img,g.gid,g.name")
												->from(challenge::$table_pk, "pk")
												->left(challenge::$table_game, "g")
												->on("pk.gid = g.gid")
												->where("pk.type = ".challenge::PK_SITENAL)
												->order('pk.ctime DESC')
												->limit("$start, $pageSize")
												->get_rows();
					challengeAjaxOutput($sitenalPkLists);
				}
				elseif ($request_challenge_type == 'national') {
					$nationalPkLists = $challenge->select("pk.id,pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate,g.img,g.name,g.gid")
											->from(challenge::$table_pk." AS pk")
											->left(challenge::$table_game, "g")->on("pk.gid = g.gid")
											->where("pk.type = ".challenge::PK_NATIONAL)
											->order('pk.ctime DESC')
											->limit("$start, $pageSize")
											->get_rows();
					challengeAjaxOutput($nationalPkLists);
				}
				elseif ($request_challenge_type == 'mine') {
					if (!empty($winddb[uid])) {
						$minePkLists = $challenge->select("pk.id, pk.case AS casenum,pk.number,pk.case_ctype,pk.desc,pk.pkname,pk.endtime,pk.ctime,pk.username,pk.uid,pk.rate,g.img,g.name,g.gid")
								->from(challenge::$table_pk, "pk")
								->left(challenge::$table_game, "g")
								->on("pk.gid = g.gid")
								->where("pk.uid = $winddb[uid]  AND pk.type =".challenge::PK_PERSONAL)
								->order('pk.ctime DESC')
								->limit("$start, $pageSize")
								->get_rows();
					}
					else{
						$minePkLists = null;
					}
					challengeAjaxOutput($minePkLists);
				}
			}
			
			
			@include_once(H_P."require/pageCss.class.php");
			$pageCss = new PageCss($pageSize, $scope, $url);
				
			@include_once H_P.'require/game.class.php';
			$game = new game(intval($_GET['gid']));
			$gameInfo = $game->getAll();
			$pkGame = $game->getPKAll();
			!empty($gameInfo['img']) && $gameInfo['img'] = PIC_HOST.'/'.$gameInfo['img'];
			
			$allPkLists = $challenge->select("pk.*,g.name, g.gid, g.img")
								->from(challenge::$table_pk, "pk")
								->left(challenge::$table_game, "g")
								->on("pk.gid = g.gid")
								->where("pk.type <> ".challenge::PK_PK)
								->order('pk.ctime DESC')
								->limit("$start, $pageSize")
								->get_rows();
	
			$pageCss->setCounts($count);
			$pageCss->setCurrentpage($page);
			$pageCss = $pageCss->getHTML(false);
	
			if( !empty($allPkLists) ) {
				foreach( $allPkLists as $key=>$pk ) {
					if (empty($pk['gid'])) {
						unset($allPkLists[$key]);
						continue;
					}
					$allPkLists[$key]['endtime'] = $pk['endtime'] < time() ? "已结束" : date("Y-m-d", $pk['endtime']);
					$allPkLists[$key]['ctime'] = date("Y-m-d ", $pk['ctime']);
					$allPkLists[$key]['img'] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $pk['img']);
				}
			}

			//partake
			if ( !empty($winddb[uid])){
				$status = array('1'=>"火爆进行中...","2"=>"未知","3"=>"已结束");
				$partakePkLists = $challenge->select("pker.*,pk.*")
						->from(challenge::$table_pk, "pk")
						->left(challenge::$table_pker, "pker")
						->on("pker.pkid = pk.id ")
						->where("pker.act_uid = $winddb[uid] OR pk.uid = $winddb[uid] AND pk.type <>".challenge::PK_PK)
						->group("pk.id")
						->order("pk.ctime DESC")
						->get_rows();
				if( !empty($partakePkLists) ) {
					foreach( $partakePkLists as $key=>$pk ) {
						$partakePkLists[$key]['endtime'] = date("Y-m-d ", $pk['endtime']);
						$partakePkLists[$key]['status'] = $status[$pk[status]];
						$partakePkLists[$key]['totalreward'] = $pk['number']*$pk['case'];
					}
				}
			}
	
			unset($challenge);
		}
		catch (challengeException $e){
			$e->log();
		}
		global $pkgame_challenge_case_ctype,$pkgame_challenge_algorithm_personal_pkerPercent,$challenge_pkerCreditPercent,$pkgame_challenge_case_num;
		// configure of challenge ,by admin 
		$challenge_ctype = $credit->cType[$pkgame_challenge_case_ctype];
		$challenge_pkerCreditPercent = $pkgame_challenge_algorithm_personal_pkerPercent;
		$challenge_pkerCreditNum = count(explode(":", $challenge_pkerCreditPercent));
		$leitai_select = "ctrl_selected";
		$take_select = isset($_GET['act']) && trim($_GET['act']) == 'take' ? "leitai_selected":'';
		empty($take_select) && $all_select = 'leitai_selected';
		$anchor = "擂台赛";
		include_once PrintHack('content');
	}
	
	
	function actionPk() {
		S::gp(array("AJAX","r_p_t",'page','act'));
		global $AJAX,$r_p_t,$page,$winddb,$pk_ctype,$newPknoticeNum;
		require_once (R_P . 'require/showimg.php');
		@include_once(H_P."require/pageCss.class.php");
		ng_baseLoader::loadClass('challenge','require');
			
		$request_pk_type = $r_p_t;
		empty($page) && $page = 1;
			
		$pageSize = P_PS;
		$scope = P_SCOPE;
		$start = ($page-1)*$pageSize;
		$url = $_SERVER[SCRIPT_NAME]."?action=pk&page=";
		
		try {
			if (empty($winddb[uid]))
				Showmsg('not_login');
			$challenge = new challenge();
			$gid = $challenge->select("gid")->from(challenge::$table_pk)->where("type = ".challenge::PK_NATIONAL)->order('starttime DESC')->get_value();
			if ($AJAX) 
			{
				ob_clean();
				if ($request_pk_type == 'act') 
				{
					$pklistsPkHistoryAsAct = $challenge->select("pk.pkname as pk_name,pk.endtime, g.img, g.name, pker.*, pk.status AS status, IF( pker.def_score > pker.act_score, 1, 0 ) AS lose, IF( pker.def_score = pker.act_score, 1, 0 ) AS draw")
							->from(challenge::$table_pker, "pker")
							->left(challenge::$table_pk, "pk")
							->on("pk.id = pker.pkid")
							->left(challenge::$table_game,"g")
							->on("pk.gid = g.gid")
							->where("pker.act_uid = $winddb[uid] AND pk.type = ".challenge::PK_PK)
							->order('pk.ctime DESC')
							->limit("$start,$pageSize")
							->get_rows();
					pkListAjaxOutput($pklistsPkHistoryAsAct);
				}
				elseif ($request_pk_type == 'def') 
				{
					$pklistsPkHistoryAsDef = $challenge->select("pk.pkname as pk_name,pk.endtime, g.img, g.name, pker.*, pk.status AS status, IF( pker.def_score > pker.act_score, 1, 0 ) AS lose, IF( pker.def_score = pker.act_score, 1, 0 ) AS draw")
							->from(challenge::$table_pker, "pker")
							->left(challenge::$table_pk, "pk")
							->on("pk.id = pker.pkid")
							->left(challenge::$table_game,"g")
							->on("pk.gid = g.gid")
							->where("pker.def_uid = $winddb[uid] AND pk.status <> ".challenge::PK_STATUS_MAKESURE." AND pk.type = ".challenge::PK_PK)
							->limit("$start,$pageSize")
							->get_rows();
					pkListAjaxOutput($pklistsPkHistoryAsDef);
				}
				elseif ($request_pk_type == 'pkme')
				{
					$pklistsPkMe = $challenge->select("pk.pkname as pk_name,pk.endtime,pk.case,pk.endtime,pk.case_ctype,pk.case as casenum, g.img, g.name, pk.status, pker.*")
								->from(challenge::$table_pker, "pker")
								->left(challenge::$table_pk, "pk")
								->on("pk.id = pker.pkid")
								->left(challenge::$table_game,"g")
								->on("pk.gid = g.gid")
								->where("pker.def_uid = $winddb[uid] AND pk.type = ".challenge::PK_PK." AND pk.status =".challenge::PK_STATUS_MAKESURE)
								->order('pk.ctime DESC')
								->limit("$start,$pageSize")
								->get_rows();
					$message_count = $challenge->query_number_rows();
					pkListAjaxOutput($pklistsPkMe);
				}
			}
			
			$count = $challenge->select("COUNT(*) as count")->from(challenge::$table_pk." AS pk")
					->left(challenge::$table_pker, "pker")
					->on("pk.id = pker.pkid")
					->where("(pker.act_uid = $winddb[uid] OR pker.def_uid = $winddb[uid]) AND pk.type = ".challenge::PK_PK)
					->get_value();
			$pklistsPkall = $challenge->select("pk.pkname as pk_name,pk.id as pkid,pk.case,pk.endtime,pk.case_ctype, g.img, g.name, pker.*,pk.status status, IF( pker.def_score > pker.act_score, 1, 0 ) AS lose, IF( pker.def_score = pker.act_score, 1, 0 ) AS draw")
					->from(challenge::$table_pker, "pker")
					->left(challenge::$table_pk, "pk")
					->on("pk.id = pker.pkid")
					->left(challenge::$table_game,"g")
					->on("pk.gid = g.gid")
					->where("(pker.act_uid = $winddb[uid] OR pker.def_uid = $winddb[uid]) AND pk.type = ".challenge::PK_PK)
					->order('pk.ctime DESC')
					->limit("$start, $pageSize")
					->get_rows();
			$pklistsPkall = pkListOutput($pklistsPkall);
			$pageCss = new PageCss($pageSize, $scope, $url);
			$pageCss->setCounts($count);
			$pageCss->setCurrentpage($page);
			$pageCss = $pageCss->getHTML(false);
			
			$gameList = $challenge->select("gid,name")->from(challenge::$table_game)->where("`installed` = 1")->get_rows();
			$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
			$friendsList = $friendsService->getFriendsByUid($winddb['uid']);
			unset($challenge);
		}
		catch (challengeException $e){
			$e->log();
		}
		
		$zhanshu_select = "ctrl_selected";
		$anchor = "战书";
		include_once PrintHack('content');
	}
	
	function actionIplayed() 
	{
		global $winddb, $db;
		ng_baseLoader::loadClass("challenge",'require');
		$whoIs = array('me' => '我', 'other' => "TA");
		if($_GET['uid']) 
		{
			$uid = $_GET['uid'];
			$who = 'other';
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($uid);
		}
		else 
		{
			$who = 'me';
			$uid = $winddb['uid'];
			$username = $winddb['username'];
		}
		if($uid) 
		{
			try{
				$challenge = new challenge();
				$gid = $challenge->select("gid")->from(challenge::$table_pk)->where("type = ".challenge::PK_NATIONAL)->order('starttime DESC')->get_value();
				$timeLimit = $time - 2592000; //1 month
				$iplayedpk = 'IPLAYED_PK';
				$iplayednormal = 'IPLAYED_NORMAL';
				//个人玩
				$challenge->select("g.name AS gamename, g.gid ,c.addTime,COUNT(c.gid) AS count, MAX(score) AS max_score")->from("ng_game_credit AS c")
					->left("ng_game", "g")->on("c.gid = g.gid")
					->where("c.uid = $uid AND c.status = 1 AND c.addTime > $timeLimit")
					->group("c.gid")->order("c.addTime desc")->limit(10);
				$challenge->query();
		
				while($result = $challenge->db->fetch_array($challenge->query) ){
					$result['addTime'] = date("Y-m-d H:i", $result['addTime']);
					$result['iplayedtype'] = $iplayednormal;
					$iPlayedLists[$result['addTime']] = $result;
				}
				//pk赛或擂台赛
				$challenge->select("pker.*, g.name AS gamename")->from("ng_game_pker AS pker")
						->left("ng_game", "g")->on("pker.gid = g.gid")
						->where("pker.act_uid = $uid OR pker.def_uid = $uid  AND pker.ctime > $timeLimit")
						->order("pker.ctime desc")->limit(10);
				$challenge->query();
				while($result = $challenge->db->fetch_array($challenge->query) ){
					$result['ctime'] = date("Y-m-d H:i", $result['ctime']);
					$result['iplayedtype'] = $iplayedpk;
					$iPlayedLists[$result['ctime']] = $result;
				}
				unset($challenge);
			}
			catch (challengeException $e) {
				$e->log();
			}
			$registerInfo = $db->get_one("SELECT * FROM ng_game_user WHERE uid = $uid");
		}
		else {
			$none = true;
			//cookies
		}
		$iplayed_select = "ctrl_selected";
		$anchor = "我玩过";
		include_once PrintHack('content');
	}
	
	function actionActive() {
		try
		{
			$siteActive = $challenge->select("pk.pkname,pk.gamename,pk.username,pk.starttime,pk.endtime,pk.id")->from(challenge::$table_pk." AS pk")
			->where("pk.gid = ".intval($pkgame_active_gameid)." AND pk.type = ".challenge::PK_SITENAL)
			->get_row();
			$siteAdminReward = $pkgame_active_reward_price.$pkgame_active_reward_ctype;
			$siteActive['activetime'] = date("Y年m月d日", $siteActive['starttime'])."-".date("m月d日", $siteActive['endtime']);
						}
			catch (challengeException $e){
			$e->log();
		}
		$anchor = '站内活动详情';
		$active_pkerCreditNum = count(explode(":", $pkgame_challenge_algorithm_sitenal_pkerPercent));
		$this->render('active');
	}
	
	function actionCheck() {
		global $db_charset;
		$pid = S::getGP('pid','G',2);
		$c_r = S::getGP('c_r','G',2);
		$p   = S::getGP('p','G',2);
		$s   = S::getGP('s','G',2);
		$AJAX= S::getGP('AJAX','G',2);
		
		if ($AJAX) {
			ob_clean();
			try {
				//challenge_request
				if ($c_r) {
					ng_baseLoader::loadClass("challenge",'require');
					$challenge = new challenge();
					$ranking = $challenge->select("pker.act_username,pker.act_uid,pker.act_score")
							->from(challenge::$table_pker." AS pker")
							->where("pker.pkid = $pid")
							->order("act_score DESC")
							->limit("$s,$p")
							->get_rows();
					if ($db_charset == 'gbk' && !empty($ranking)) {
						foreach ($ranking as $k=>$v) {
							$ranking[$k]['act_username'] = mb_convert_encoding($v['act_username'], 'UTF-8', 'GB2312');
						}
					}
					msg("",array("ret"=>1,"list"=>$ranking),0,true);
		
				}//pk_request
				else{
		
				}
			}
			catch (challengeException $e){
				$e->log();
			}
		}
		else {
			exit('forbidden');
		}
	}
	
	
	function actionList() {
		global $mode;
		$list = !empty($_GET['list'])?trim($_GET['list']):'pk';
		$page = intval($_GET['p']);
		$pageSize = intval($_GET['s']);
		
		if ($list == 'xyx') {

		}
		else {
		
			switch($list) {
				case 'jl':
					$key = !empty($_GET['k'])?trim($_GET['k']):'J_subm1';
					$temp = $mode['JLJF'];
					$titleImg = 'hack/bbsgame/images/1/title_03.jpg';
					break;
				case 'pk':
					$key = !empty($_GET['k'])?trim($_GET['k']):'P_subm1';
					$temp = $mode['JFPK'];
					$titleImg = 'hack/bbsgame/images/1/title_05.jpg';
					break;
				default:
					exit("404");
				break;
			}
			if(isset($temp[$key])) {
				empty($temp[$key][gids]) && $temp[$key][gids] = 0;
				$sta = "`gid` IN (".$temp[$key][gids].") ";
				!empty($temp[$key][tids]) && $sta .= "OR `type` IN (".$temp[$key][tids].")";
				$games = getGameByWhere("WHERE $sta LIMIT 27");
				foreach($games as $i=>$game) {
					$games[$i][img] = PIC_HOST."/".preg_replace('/_120.jpg/', '.jpg', $game['img']);
				}
			}
		
			foreach($temp as $k=>$v) {
				$catergories[$k] = $v['name'];
			}
		}
		
		include_once PrintHack('listpage');
	}
	
	function actionCooperation(){
		
		date_default_timezone_set("Asia/Hong_Kong");
		$account = 255;	// 接入渠道号
		$key = "EB84A3E9-095B-4678-A49A-F56582B8770E";	// 密钥
		$gameId['mj'] = 23081;		// 达人麻将游戏号
		$gameId['pw'] = 25011;		// 德州扑克游戏号
		$gameId['wgh'] = 25010;		// 天天斗地主游戏号
		$gameId['fish'] = 23091;		// 愤怒的渔夫游戏号
		$time = date("YmdHis");	// 注册需要的时间戳格式
		
		$number = $_GET['coopid'];
				$g_id = $gameId[$number];		// 选择的游戏
		$sign = md5(strtoupper($account."".$g_id."".$time."".$key));	// 加密字段
		// 颁发Ticket服务
		$TickerUrl = "http://wgh.lianzhong.com/Services/RequestTicket.ashx?ChannelID={$account}&GameID={$g_id}&Timestamp={$time}&Sign={$sign}";
		$xml = file_get_contents($TickerUrl);
		$xml_object = simplexml_load_string($xml);
		$stats = (int)$xml_object->Stats;		//	获得状态
		if ($stats === 0){
			$TiketKey = (string)$xml_object->Data;	// 获得游戏密钥
			
			$Ntime = date("YmdHis");	// 访问游戏需要的时间戳
			$num = rand(1,10000);
			$UserID = "1594_".$winddb[uid];
			$Nsign = md5(strtoupper($account."".$g_id."".$UserID."0".$Ntime."".$key));	// 加密字段
			// Iframe 嵌套用的游戏 URL
			$gameUrl = "http://wgh.lianzhong.com/Services/RequestGame.ashx?ChannelID={$account}&GameID={$g_id}&UserID={$UserID}&CMStatus=0&Timestamp={$Ntime}&Ticket={$TiketKey}&Version=1&Charset=UTF8&Sign={$Nsign}";
		}
		
		$outDivConfig = array("width"=>"960px","height"=>"710px");
		switch ($_GET['coopid']) {
					case 'pw' :
						$iFrameConfig = array("width"=>'960px',"height"=>'640px',"top"=>'0px',"src"=>$gameUrl);
						break;
					case 'fish' :
						$outDivConfig["height"] = "930px";
						$iFrameConfig = array("width"=>'960px',"height"=>'930px',"top"=>'-75px',"src"=>$gameUrl);
						break;
					case 'wgh' :
					$iFrameConfig = array("width"=>'960px',"height"=>'640px',"top"=>'0px',"src"=>$gameUrl);
						break;
					case 'mj' :
						$iFrameConfig = array("width"=>'960px',"height"=>'930px',"top"=>'0px',"src"=>$gameUrl);
						break;
					case 'jj':
						$iFrameConfig = array("width"=>'960px',"height"=>'700px',"src"=>'http://webgame.jj.cn/oem/index.php?fromid=13014');
						break;
					default:
						$iFrameConfig = array("width"=>'960px',"height"=>'700px');
		break;
		}
		include_once PrintHack('cooperation');
	}
}