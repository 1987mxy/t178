<?php
/*
* 
* Jul 9, 2012
* GBK
* 11:16:48 AM
* AgudaZaric
* SimpleDAO.php
*/

class SimpleDAO {

	public $query_number_rows,$query,$sql,$db;

	function __construct($db) {
		$this->db = $db;
	}
	
	/**
	* 绑定sql中的变量,并返回绑定后结果
	*
	* @return obj
	*/
	protected function _combinSql() {
		$args = func_get_args();
		$this->sql = call_user_func_array('sprintf', $args);
		return $this;
	}
	
	/**
	* 组装单条 key=value 形式的SQL查询语句值 insert/update
	*
	* @param array $array
	* @return string
	* @see AbstractWindPdoAdapter
	*/
	public function sqlSingle($array) {
		if (!is_array($array)) return '';
		$str = array();
		foreach ($array as $key => $val) {
			$str[] = $this->fieldMeta($key) . '=' . $this->quote($val);
		}
		return $str ? implode(',', $str) : '';
	}
	
	/**
	* 过滤SQL元数据，数据库对象(如表名字，字段等)
	*
	* @param array $data
	* @return string
	*/
	public function fieldMeta($data) {
		$data = str_replace(array('`', ' '), '', $data);
		return ' `' . $data . '` ';
	}
	
	public function fieldIncrease($var) {
		return "`$var` = `$var` + 1";
	}
	
	public function quote($val) {
		return $this->sqlEscape($val);
	}
	
	/**
	* 通用多类型混合转义函数
	* @param $var
	* @param $strip
	* @param $isArray
	* @return mixture
	*/
	function sqlEscape($var, $strip = true, $isArray = false) {
		if (is_array($var)) {
			if (!$isArray) return " '' ";
			foreach ($var as $key => $value) {
				$var[$key] = trim(SimpleDAO::sqlEscape($value, $strip));
			}
			return $var;
		} elseif (is_numeric($var)) {
			return " '" . $var . "' ";
		} else {
			return " '" . addslashes($strip ? stripslashes($var) : $var) . "' ";
		}
	}
	
	/**
	* 过滤数组并将数组变量转换为sql字符串
	*
	* 对数组中的值进行安全过滤,并转化为mysql支持的values的格式,如下例子:
	* <code>
	* $variable = array('a','b','c');
	* quoteArray($variable);
	* //return string: ('a','b','c')
	* </code>
	*
	* @see AbstractWindPdoAdapter::quoteArray()
	*/
	public function quoteArray($variable) {
		if (empty($variable) || !is_array($variable)) return '';
		$_returns = array();
		foreach ($variable as $value) {
			$_returns[] = $this->quote($value);
		}
		return '(' . implode(', ', $_returns) . ')';
	}
	
	/**
	 * 过滤二维数组将数组变量转换为多组的sql字符串
	 *
	 * <code>
	 * $var = array(array('a1','b1','c1'),array('a2','b2','c2'));
	 * quoteMultiArrray($var);
	 * //return string: ('a1','b1','c1'),('a2','b2','c2')
	 * </code>
	 *
	 * @see AbstractWindPdoAdapter::quoteMultiArray()
	 */
	public function quoteMultiArray($var) {
		if (empty($var) || !is_array($var)) return '';
		$_returns = array();
		foreach ($var as $val) {
			if (!empty($val) && is_array($val)) {
				$_returns[] = $this->quoteArray($val);
			}
		}
		return implode(', ', $_returns);
	}
	
	function limit($intVal) {
		$this->sql .= " LIMIT $intVal ";
		return $this;
	}

	function order($var) {
		$this->sql .= " ORDER BY $var ";
		return $this;
	}

	function group($var) {
		$this->sql .= " GROUP BY $var ";
		return $this;
	}

	function where($var) {
		$this->sql .= " WHERE $var ";
		return $this;
	}

	function equal($var) {
		$this->sql .= " = var ";
		return $this;
	}

	function on($opera) {
		$this->sql .= " ON $opera ";
		return $this;
	}

	function left($table ,$alias ) {
		if( empty($table) ) throw new challengeException("invliad args[0], empty table union in SQL syntax ");
		$this->sql .= " LEFT JOIN $table AS $alias ";
		return $this;
	}

	function from($table, $as = "") {

		$this->sql .= " FROM $table";
		!empty($as) && $this->sql .= " AS $as";

		return $this;
	}

	function select($fileds) {
		if( !is_array($fileds) ) {
			if( empty($fileds) ) throw new challengeException("invliad args[0], empty fileds in SQL syntax ");
			$this->sql = "SELECT $fileds ";
		}
		else{
			$this->sql = "SELECT ".implode(",", $fileds);
		}

		return $this;
	}

	function update($table, $updates) {
		if( !empty($updates) ) {
			$this->sql = "UPDATE $table ";
			if(!is_array($updates)) throw new challengeException("invliad args[1]. \$updates:$updates should be Array in SQL syntax ");
			foreach($updates as $key=>$value) {
				$this->sql .= " SET $key = $value ";
			}
		}
		else if( empty($updates) ) {
			throw new challengeException("invliad args[1]. empty update values in SQL syntax ");
		}
		return $this;
	}

	function getSql() {
		return $this->sql;
	}

	function query($sql = '') {
		!empty($sql) && $this->sql = $sql;
		if(empty($this->sql)) return false;
		$this->query  = $this->db->query($this->sql);
		$this->query_number_rows = $this->db->num_rows($this->query);
	}
	
	function insert_id(){
		return $this->db->insert_id();
	}

	function query_number_rows() {
		return $this->query_number_rows;
	}
	
	function affected_rows() {
		return $this->db->affected_rows();
	}

	function commit($sql = '') {
		!empty($sql) && $this->sql = $sql;
		if(empty($this->sql)) return false;
		$query = $this->db->query($this->sql);
		$this->query_number_rows = $this->db->num_rows($query);
		if($this->query_number_rows > 1){
			while( $result = $this->db->fetch_array($query) ) {
				$arr[] = $result;
			}
		}
		else{
			$arr = $result;
		}
		return $arr;
	}

	function get_row($sql = '') {
		!empty($sql) && $this->sql = $sql;
		if(empty($this->sql)) return false;
		$query = $this->db->query($this->sql);
		$this->query_number_rows = $this->db->num_rows($query);
		if($result = $this->db->fetch_array($query))
			return $result;
		else
			return false;
	}

	function get_rows($sql = '') {
		!empty($sql) && $this->sql = $sql;
		if(empty($this->sql)) return false;
		$query = $this->db->query($this->sql);
		$this->query_number_rows = $this->db->num_rows($query);
		while( $result = $this->db->fetch_array($query) ) {
			$arr[] = $result;
		}
		return $arr;
	}

	function get_value($sql = '') {
		!empty($sql) && $this->sql = $sql;
		if(empty($this->sql)) return false;
		if($result = $this->db->get_value($this->sql))
			return $result;
		else
			return false;
	}
}

class SimpleDAOException extends BaseException {}