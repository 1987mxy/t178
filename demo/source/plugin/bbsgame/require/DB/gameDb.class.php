<?php

/*
 * gameDb.class.php Created on 2012-2-16
 * TODO
 * author:AgudaZaric
 * QQ:384318815
 * msn:coderzl@hotmail.com
 *
 */

class gameDb {

	var $_db;

	public static $table_game = 'ng_game',
		   $table_ip = 'ng_game_ip',
		   $table_credit = 'ng_game_credit',
		   $table_type = 'ng_game_type',
		   $table_shell = "ng_game_shell";

    function gameDb() {
    	global $db;

    	$this->_db = &$db;

    }

    function query($sql) {
    	return $this->_db->query($sql);
    }

    function addType($type) {
    	$this->_db->query("INSERT INTO ".self::$table_type."(`name`) VALUES('$type')");
    }

    function addShell($shell) {
    	$this->_db->query("INSERT INTO ".self::$table_shell."(`url`) VALUES('$shell')");
    }

    function getAllResultFromSql() {

    }

    function get_one($sql) {
    	$result = $this->_db->get_one($sql);
    	if( !empty($result) ){
    		foreach( $result as $k=>$v ) {
    			return $result[$k];
    		}
    	}
    	return false;
    }

    function getRow($query) {
    	$result = $this->_db->fetch_array($query);
    	return $result;
    }

    function getRows($query){
		while($result = $this->_db->fetch_array($query)) {
			$arr[] = $result;
		}
		return $arr;
    }

    function getTypeIdWithTypename($typeName) {

		$result = $this->_db->get_one("SELECT `id` FROM ".self::$table_type." WHERE name = '$typeName'");
		if(empty($result['id'])) {
			return null;

		} else {
			return $result['id'];
		}
    }

    function getshellIdWithShellurl($shellUrl) {

		$result = $this->_db->get_one("SELECT `id` FROM ".self::$table_shell." WHERE url = ".S::sqlEscape($shellUrl));
		if(empty($result['id']))
			return null;
		else
			return $result['id'];
    }

    function getAllTypeFiledsWithGameId($id) {

		if($result = $this->_db->get_one("select * from ".self::$table_shell." where id =( SELECT shellid FROM ".self::$table_game." WHERE gid = $id) limit 1"))
			return $result;
		else
			return null;
    }

    function getAllGameFiledsWithGameId($id) {

    	if($result = $this->_db->get_one("SELECT * FROM ".self::$table_game." WHERE gid = '$id'"))
    		return $result;
    	else
    		return false;

    }

    function getAllFiledsWithGameId($id){
		$sql = "SELECT g.*,s.url as shellUrl FROM ".self::$table_game." g" .
			" LEFT JOIN " .self::$table_shell . " s ON g.shellid = s.id" .
			" WHERE gid = $id LIMIT 1";
		$query = $this->_db->query($sql);
		return $this->_db->fetch_array($query);

    }

}
?>