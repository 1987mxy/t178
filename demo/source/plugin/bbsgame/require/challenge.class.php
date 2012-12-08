<?php
/**
 * 
 * Author : AgudaZaric 
 * Email  : coderzl@hotmail.com
 * QQ	  : 277490280
 * 
 */

//!isset($GLOBALS['db']) && exit('forbidden');
!defined('H_P') && exit('forbidden');
require_once H_P.'/require/baseException.class.php';
require_once H_P.'/require/SimpleDAO.php';
class challenge extends SimpleDAO {
	
	public static $table_pk   = 'ng_game_pk',
				  $table_pker = 'ng_game_pker',
				  $table_game = 'ng_game';
	
	public $id,$uid,$username,$pkname,$score,$number,$desc,$status,$starttime,$endtime,$ctime,$gamename;
	public $price = array('num'=>0,'ctype'=>"''",'ext'=>"''"),
		   $case = array('num'=>0,'ctype'=>"''"),
		   $type = array('national'=>1,'sitenal'=>2,'personal'=>3);
	
	const PK_PK = 4, PK_NATIONAL = 1, PK_SITENAL = 2, PK_PERSONAL = 3;
	const PK_STATUS_MAKESURE = 0, PK_STATUS_ING = 1, PK_STATUS_SITE =2, PK_STATUS_OK = 3, PK_STATUS_REFUSE = 4; 
	
	public function __construct() {
		global $db;
		
		$this->db = &$db;
	}
	
	function add($user, $gid, $type){
		
		if( empty($user[uid]) || empty($gid) || empty($this->case['ctype']) || empty($this->endtime))
			throw new challengeException(" empty avariable:\$user[uid] = $user[uid] ,\$gid = $gid, \$this->case[ctype] = $this->case[ctype], \$this->endtime = $this->endtime; ");
		
		$strSQL = "INSERT INTO `ng_game_pk`(`uid`,`username`,`gid`,`gamename`,`pkname`,`case`,`case_ctype`,`desc`,`endtime`,`starttime`,`ctime`,`number`,`type`,`status`,`price`,`price_ctype`,`price_ext`)
				VALUES($user[uid], $user[username], $gid, '{$this->gamename}', $this->pkname, ".$this->case['num'].",".$this->case['ctype'].",$this->desc,$this->endtime, $this->starttime,
						$this->ctime, 1, $type, $this->status, ".$this->price['num'].",".$this->price['ctype'].",".$this->price['ext'].")";
		$query = DB::query($strSQL);
		return $query;
	}
	
	function join($pk_id,$act_user, $pk_user, $gid) {

		$strSQL = "INSERT INTO `".self::$table_pker."` (`pkid`,`gid`,`act_uid`,`act_username`,`act_usericon`,`def_uid`,`def_username`,`def_usericon`,`ctime`) 
				VALUES('".$pk_id."',$gid ,$act_user[uid],".$act_user[username].",".$act_user['usericon'].",".$pk_user['uid'].",".$pk_user['username'].",".$pk_user['usericon'].",".time().")";
		$query = DB::query($strSQL);
		return DB::query("UPDATE ".self::$table_pk." SET number = number+1 WHERE id = $pk_id");
		
	}
	
	function acceptPk($pk_id, $act_id) {
		$strSQL = "SELECT pker.* FROM {$table_pker} pker
				WHERE pker.pkid = $pk_id";
		$query = DB::query($strSQL);
		while( $result = DB::fetch($query) ) {
			$arr[] = $result;
		}
		$num_rows = count($arr);
		if( $num_rows ) {
			$strSQL = "UPDATE `".self::$table_pk."` SET status = self::PK_STATUS_ING WHERE id={$pk_id}";
			$query = DB::query($strSQL);
			if( $query ){
				return true;
			}
			else{
				return false;
			}
		}
	}
	
	function refusePk($pk_id) {
		
		$strSQL = "UPDATE ".self::$table_pk." SET status = ".self::PK_STATUS_REFUSE." WHERE id = $pk_id";
		$query = DB::query($strSQL);
		if (DB::affected_rows()){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	//记录最高得分
	function pkRecord( $pk_id, $score ,$userType = 'ACT', $uid = 0)
	{
		if( $userType == 'ACT') {
			if (empty($uid)) throw new challengeException("\$uid = $uid; is empty");
			$this->update(self::$table_pker, array("act_score"=>$score))->where("pkid = $pk_id AND act_uid = $uid AND act_score < $score")->query();
		}
		else{
			$this->update(self::$table_pker, array("def_score"=>$score))->where("pkid = $pk_id AND def_score < $score")->query();
		}
		return true;
	}
	
	/**
	 * 计入战绩
	 * @param int $uid
	 * @param int $operate ('+','-')=>('win','lose')
	 * @return boolean
	 */
	function addOutcome($uid, $operate = '+')
	{
		if ($operate == '+')
			$outcomeFiled = 'win_num';
		else 
			$outcomeFiled = 'lose_num';
		$query = DB::query("UPDATE ng_game_user SET $outcomeFiled = $outcomeFiled + 1 WHERE uid = $uid");
		if ($query)
			return true;
		else 
			return false;
	}
	
	function getPkInfo($pk_id) {
		$result = $this->db->get_one("SELECT * FROM ".self::$table_pk." WHERE id = $pk_id LIMIT 1");
		return $result;
	}
	
	function getUid() {
		$this->uid = $this->db->get_value("SELECT `uid` FROM ".self::$table_pk." WHERE id = $this->id LIMIT 1");
		return $this->uid;
	}
	
	function getInsertId($pk_user, $gid) {
		$strSQL = "SELECT id FROM ng_game_pk WHERE uid = {$pk_user} AND gid = {$gid} ORDER BY id DESC LIMIT 1";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);

		return $result['id'];
	}
	
	
	//结算
	function closeExpirechallenge(){
		global $_G, $pkgame_challenge_algorithm_personal_pkerPercent,$pkgame_challenge_algorithm_sitenal_pkerPercent;//保存在配置文件，由站长配置
		
		static $reward_pker_percent = 0.7, //参加擂台的用户获取总共奖励比率
			   $reward_pk_percent = 0.1, //擂台主总奖励比率
			   $reward_rate_percent = 0.2, //税率
			   $pkers = null;
		if ($pkers !== null) {
			return;
		}	
		
		$strSQL = "SELECT * FROM ".self::$table_pk." WHERE status <> ".self::PK_STATUS_OK." AND endtime < ".time() ." LIMIT 1";
		$query = DB::query($strSQL);
		$result = DB::fetch($query);
		$expired = $result;

		//处理到期的pk、challenge
		if (!empty($expired)) 
		{
			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = 'extcredits'.$key;
				getuserprofile($ext);
				if((string)trim($expired['case_ctype']) === (string)trim($ext)) {
					$exttitle = $value;
					break;
				}
			}
			//结算pk
			if ($expired['type'] == self::PK_PK) 
			{
				$strSQL = "SELECT pker.* FROM ".self::$table_pker." AS pker WHERE pker.pkid = ".$expired[id]." LIMIT 1";
				$query = DB::query($strSQL);
				$result = DB::fetch($query);
				$pker = $result;

				//对方未确认，返还挑战者押金
				if ($expired['status'] == self::PK_STATUS_MAKESURE) {
					$reward_total = $expired['case'];
	
					$dataarr = array($ext => $reward_total);
					updatemembercount($pker['act_uid'], $dataarr, 1, 'TRC', $pker['act_uid']);
						
					$affect_describe = $pker[act_username]."挑战$pker[def_username] 未得到对方回应，返还押金".$reward_total.$exttitle;
					/*$sql = "INSERT INTO pw_creditlog (uid,username,ctype,affect,adddate,logtype,descrip)" .
							" VALUES (".$pker[act_uid].",'".$pker[act_username]."','".$expired[case_ctype]."', $reward_total,'".time()."','".pk_affect."','$affect_describe')";
					$this->db->query($sql);*/
				}
				else{
					$reward_total = $expired['case']*2; //双倍返还赢家
					
					if ($pker['def_score'] > $pker['act_score']) 
					{
						$winner = array('username'=>$pker['def_username'],"uid"=>$pker['def_uid']);
						$loser = array("username"=>$pker['act_username'],'uid'=>$pker['act_uid']);
					}
					else{
						$winner = array('username'=>$pker['act_username'],"uid"=>$pker['act_uid']);
						$loser = array("username"=>$pker['def_username'],"uid"=>$pker['def_uid']);
					}
					$dataarr = array($ext => $reward_total);
					updatemembercount($winner['uid'], $dataarr, 1, 'TRC', $pker['act_uid']);

					$this->addOutcome($winner['uid']);
					$this->addOutcome($loser['uid'],'-');
					$affect_describe = $winner[username]."在对战$loser[username] 中获得".$reward_total.$exttitle;
					/*$sql = "INSERT INTO pw_creditlog (uid,username,ctype,affect,adddate,logtype,descrip)" .
													" VALUES (".$winner[uid].",'".$winner[username]."','".$expired[case_ctype]."', $reward_total,'".time()."','".pk_affect."','$affect_describe')";
					$this->db->query($sql);*/
				}
			}//结算擂台
			else {
				
				if ($expired['type'] == self::PK_SITENAL) 
				{
					$reward_percent = explode(":", $pkgame_challenge_algorithm_sitenal_pkerPercent);
				}
				else{//个人擂台
					$reward_percent = explode(":", $pkgame_challenge_algorithm_personal_pkerPercent);
				}
				$reward_count = count($reward_percent);
				$reward_total = $expired['number']*$expired['case']; //总奖励
				
				//获取参赛人数
				$strSQL = "SELECT number FROM ".self::$table_pk." WHERE id = {$expired[id]}";
				$query = DB::query($strSQL);
				while( $result = DB::fetch($query) ) {
					$arr[] = $result;
				}
				$partake_numbers = count($arr);
				unset($arr);
				
				//挑战人数不足。按照规则一处理
				if ($partake_numbers < $reward_count) 
				{
					
					$strSQL = "SELECT act_uid,act_username FROM ".self::$table_pker." WHERE pkid = {$expired[id]}";
					$query = DB::query($strSQL);
					while( $result = DB::fetch($query) ) {
						$arr[] = $result;
					}
					$back_pkers_count = count($arr);
					$back_pkers = $arr;
					unset($arr);
					for( $i = 0 ; $i < $back_pkers_count; $i++ ) {
						$dataarr = array($ext => $expired['case']);
						updatemembercount($back_pkers[$i]['act_uid'], $dataarr, 1, 'TRC', $back_pkers[$i]['act_uid']);

						$affect_describe = "由于擂台<$expired[pkname]>未达到指定人数，返还". $back_pkers[$i][act_username].$expired['case'].$exttitle;
						$sql = "INSERT INTO {$_G['config']['db'][1]['tablepre']}home_notification (uid,type,note,dateline)" .
								" VALUES (".$back_pkers[$i]['act_uid'].",'系统消息', '{$affect_describe}','".time()."')";
						DB::query($sql);
					}
					
				}//达到奖励人数，按规则二处理
				else {
					//前几名信息
					$sql = "SELECT * FROM ".self::$table_pker." WHERE pkid = $expired[id] AND act_score > def_score ORDER BY act_score desc LIMIT $reward_count";
					$query = DB::query($sql);
					while( $result = DB::fetch($query) ) {
						$pkers[] = $result;
					}
					if (!empty($pkers)) 
					{
						$reward_pkser_total = $reward_total * $reward_pker_percent; //打擂获奖者总奖励
						$count = count($pkers);
						for( $i = 0 ; $i < $count; $i++ ) {
							$affect = $reward_pkser_total*$reward_percent[$i]; //按照比例分给获奖者
							$dataarr = array($ext => $affect);
							updatemembercount($pkers[$i]['act_uid'], $dataarr, 1, 'TRC', $pkers[$i]['act_uid']);
							$affect_describe = $pkers[$i][act_username]."在擂台 <$expired[pkname]> 中获得".$affect.$exttitle;
							$sql = "INSERT INTO {$_G['config']['db'][1]['tablepre']}home_notification (uid,type,note,dateline)" .
								" VALUES (".$back_pkers[$i][act_uid].",'系统消息', '{$affect_describe}','".time().")";
							
							DB::query($sql);
						}
					}
					if($expired[uid]) $expired[uid] = $expired[uid]; else $expired[uid] = 0;
					//奖励擂台主
					$reward_pk_total = $reward_total * $reward_pk_percent;
					$dataarr = array($ext => $reward_pk_total);
					updatemembercount($expired['uid'], $dataarr, 1, 'TRC', $expired['uid']);
					$affect_describe = "$_G[username]作为擂台主，在擂台 <$expired[pkname]>结束后， 按$reward_pk_percent比率 获得".$reward_pk_total.$exttitle;
					$sql = "INSERT INTO {$_G['config']['db'][1]['tablepre']}home_notification (uid,type,note,dateline)" .
								" VALUES (".$expired[uid].",'系统消息', '{$affect_describe}','".time().")";
					
					DB::query($sql);
				}
			}
			
			//标记为已处理
			$sql = "UPDATE ".self::$table_pk." SET status=".self::PK_STATUS_OK." WHERE id = {$expired[id]}";
			DB::query($sql);
		}
	}
	
	function __destruct() {
		$this->closeExpireChallenge();
	}
}

class challengeException extends BaseException {
	
}
