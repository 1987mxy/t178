<?php
/*
* 
* Jul 10, 2012
* GBK
* 3:36:07 PM
* AgudaZaric
* signIn.class.php
*/
include_once H_P.'/require/baseException.class.php';
class SignIn {
	
	public static $table_user = 'ng_game_user';
	public $signin_num = 0,
		   $signin_updatetime = 0;
	
	private $signin_max_num,
			$interval,
			$rewardMin,
			$rewardMax,
			$rewardCtype;
	
	function __construct($uid ,$interval = 0, $number = 0, $rewardMin = 0, $rewardMax = 0, $rewardCtype = 'money') {
		global $_G;
		$credit_ctype = array();
		foreach($_G['setting']['extcredits'] as $key => $value){
			$ext = trim('extcredits'.$key);
			getuserprofile($ext);
			$credit_ctype[] = $ext;
		}
		
		if (!in_array($rewardCtype, $credit_ctype)) throw new SignInException("error: illegal rewardCtype");
		$this->SignIn($uid, $interval, $number, $rewardMin, $rewardMax, $rewardCtype);
	}

	function SignIn($uid, $interval, $number, $rewardMin, $rewardMax, $rewardCtype){
		global $_G;
		$this->signin_max_num = intval($number);
		$this->interval = intval($interval) * 3600;
		$this->rewardMin = intval($rewardMin);
		$this->rewardMax = intval($rewardMax);
		$this->rewardCtype = $rewardCtype;
		$this->init($uid);
		$this->initTime = time();
	}
	
	function judgeSignInTime($uid, $time){
		global $_G;
		if (empty($uid)) throw new SignInException("error: args[0] \$uid is empty");
		if (empty($this->signin_num) || empty($this->signin_updatetime)) {
			$this->init($uid);
		}
		
		if ($this->signin_updatetime < strtotime(date("Y-m-d",$time))) {
			$this->reset($uid, $time);
		}

		if ($this->signin_updatetime > $time - $this->interval) {
			$GLOBALS['sign_block_timelimit'] = intval(($this->interval - ($time - $this->signin_updatetime))/60);
			return "sign_interval_limit";
		}
		else if ($this->signin_num > $this->signin_max_num){
			$GLOBALS['signin_num'] = $this->signin_num;
			return "sign_number_limit";
		}
		else {
			$strSQL = "UPDATE ".self::$table_user." SET `signin_num`=`signin_num`+1, signin_updatetime='{$time}' WHERE `uid` = ".intval($uid);
			$query = DB::query($strSQL);

			$point = rand($this->rewardMin, $this->rewardMax);
			if (empty($this->rewardCtype)) throw new SignInException("Error: empty obj's property rewardCtype.");

			$dataarr = array($this->rewardCtype => $point);
			updatemembercount($uid, $dataarr, 1, 'TRC', $uid);

			foreach($_G['setting']['extcredits'] as $key => $value){
				$ext = trim('extcredits'.$key);
				getuserprofile($ext);
				if((string)trim($this->rewardCtype) === (string)trim($ext)) {
					$creditTitle = $value['title'];
					break;
				}
			}
			$GLOBALS['signin_reward_score'] = $point;
			$GLOBALS['sign_reward_ctype'] = $creditTitle;
			$this->signin_num += 1;
			$this->signin_updatetime = $time;
			return 'sign_success';
		}
	}
	/**
	 * @param unknown_type $uid
	 */
	function init($uid) {
		$strSQL = "SELECT `signin_num`,`signin_updatetime` FROM ng_game_user WHERE `uid` = ".intval($uid);
		$query = DB::query($strSQL);
		$userSignInfo = DB::fetch($query);
		$this->signin_num = intval($userSignInfo['signin_num']);
		$this->signin_updatetime = intval($userSignInfo['signin_updatetime']);
	}

	/**
	 * @param unknown_type $uid
	 * @param unknown_type $time
	 * @param unknown_type $num
	 */
	function reset($uid, $time, $num = 0){
		if (empty($time)) $time = time();
		$strSQL = "UPDATE ".self::$table_user." SET signin_updatetime='{$time}',signin_num='0' WHERE `uid` = ".intval($uid);
		$query = DB::query($strSQL);

		$this->signin_num = $num;
		$this->signin_updatetime = $time - $this->interval; 
	}
}

class SignInException extends BaseException {}