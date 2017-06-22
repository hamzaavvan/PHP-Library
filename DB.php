<?php

namespace App\Providers;

use App;
use PDO;

class DB {
    public $query, $error, $results, $whereArr;

    private $handle, $table, $where, $orderBy, $groupBy, $limit, $column;

    protected $count = 0;

	public function __construct() 
	{
		$this->table = Wingwah\User::$table;  	

		$host = '@host';
		$db = '@db';
		$username = '@username';
		$password = '@password';
			
		try {
			$this->handle = new PDO("mysql:host=$host;dbname=$db;", 
				$username, $password, [
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
				]);

			$this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		$this->handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch (Exception $e) {
		  dd('Oops ! Something goes wrong. Error Code: '.$e->getCode());
		}
	}

	public function sql($sql, $params = []) 
	{
		$this->error = false;
		$this->results = '';

		$sql = $this->sqlfilter($sql);

		$sql .= $this->appendClause();

		$this->sql = $sql;

		$this->query = $this->handle->prepare($sql);

		if (is_array($params)&&count($params)>0) {
			$this->bind($params);
		}else if (!empty($this->whereArr)) {
			$params = $this->whereArr;

			$this->bind($params);
		}

		return $this;
	}

	public function bind($params)
	{
		$sql = $this->sql;

		if (is_array($params)&&count($params)) {
			if (preg_match('/:[a-zA-Z]/', $sql) == false) {
				$x = 1;
				foreach ($params as $value) {
					$this->query->bindValue($x, $value);
					$x++;
				}
			}else if (preg_match('/:[a-zA-Z]/', $sql) == true) {
				foreach ($params as $param => $value) {
					$param = ":$param";
					$this->query->bindValue($param, $value);
				}
			}
		}

		return $this;
	}

	public function execsql()
	{
		if ($this->query->execute()) {
			$this->count = $this->query->rowCount();
		}else {
			$this->error = true;
		}

		return $this;		
	}

	public function query($sql = '', $params = []) 
	{
		$this->error = false;

		$sql = $this->sqlfilter($sql);

		$this->query = $this->handle->prepare($sql);

		if (is_array($params)&&count($params)) {
			if (preg_match('/:[a-zA-Z]/', $sql) == false) {
				$x = 1;
				foreach ($params as $value) {
					$this->query->bindValue($x, $value);
					$x++;
				}
			}else if (preg_match('/:[a-zA-Z]/', $sql) == true) {
				foreach ($params as $param => $value) {
					$param = ":$param";
					$this->query->bindValue($param, $value);
				}
			}
		}


		if ($this->query->execute()) {
			$this->count = $this->query->rowCount();
			$this->truncateClause();
		}else {
			$this->error = true;
		}
		return $this;
	}

	public function sqlfilter($sql)
	{
		if (strpos($this->table, ' ')!=false) {
			$this->table = trim(str_replace('`', '', $this->table));
			$sql = str_replace("`$this->table`", $this->table, $sql);
			$pieces = explode(' ', $this->table);

			$tclone = $this->table;
			$this->table = "`$pieces[0]`";
			

			array_shift($pieces);
			$sub = implode(' ', $pieces);

			if (empty($this->as))
				$this->table .= " $sub";

			$sql = str_replace($tclone, $this->table, $sql);
		}
		
		$sql = str_replace('@table', "$this->table", $sql);

		return $sql;
	}

	public function action($action, $where = []) 
	{
		$table = $this->table;
		if (count($where) === 3) {
			$operators = array('=', '!=', '>', '<', '>=', '<=');

			$field 		= $where[0];
			$operator 	= $where[1];
			$value 		= $where[2];

			if (in_array($operator, $operators)) {
				$sql = "{$action} {$table} WHERE {$field} {$operator} ?";
				
				if (!$this->query($sql, array($value))->error()) {
					return $this;
				}
			}
		}
		return false;
	}

	public function AndGet($where = []) 
	{
		return $this->get($where, '&&');
	}

	public function OrGet($where = []) 
	{
		return $this->get($where, '||');
	}

	public function get($where = [], $seperator = '') 
	{
		$table = $this->table;
		$table = preg_match('/\./', $table) ? $table : "`$table`"; // knocking ambiguities
		$sql = "SELECT * FROM $table";

		$add = '';
		if (!empty($this->add)) $add .= ", $this->add";
		
		$sql = "SELECT * $add FROM $table";

		if (empty($this->column)) {
			if (is_array($where)&&array_key_exists('cols', $where)) {
				$cols = implode(', ', $where['cols']);

				$sql = "SELECT $cols FROM $table";
			}else if (is_string($where)) {
				$cols = $where;

				$sql = "SELECT $cols FROM $table";
			}
		}else {
			$cols = $this->column;
			
			$sql = "SELECT $cols FROM $table";
		}

		if (!empty($this->as))
			$sql .= " $this->as";

		if (!empty($this->leftJoin))
			$sql .= " $this->leftJoin";
		if (!empty($this->on))
			$sql .= " $this->on";

		$sql .= $this->appendClause();

		if (empty($this->where)) {
			if (is_array($where)&&((!array_key_exists('cols', $where)&&count($where) > 0) || (array_key_exists('cols', $where)&&count($where=$where['where'])>0))) {
				$sql .= " WHERE ";
				$x = 1;
				foreach ($where as $key => $value) {
					$sql .= "`$key` = :$key";
					if ($x < count($where)) {
						$sql .= " $seperator ";
					}
					$x++;
				}
			}
		}else {
			$where = $this->whereArr;
		}

		// echo $sql;
		return $this->query($sql, $where);
	}

	public function show($cols)
	{
		$table = $this->table;
		
		if (!empty($cols)) {

			if (is_array($cols)&&array_key_exists('cols', $cols)) {
				$cols = implode(', ', $cols['cols']);

				$sql = "SHOW $cols FROM $table";
			}else if (is_string($cols)) {
				$col = $cols;

				$sql = "SHOW $col FROM $table";
			}

			if (!empty($this->where)) {
				$sql .= " $this->where";
			}

			if (count($this->whereArr)) {
				$where = $this->whereArr;
			}
			
			return $this->query($sql, $where);
		}
		
		return $this;
	}

	public function columns($colOrArray)
	{
		if (is_array($colOrArray)) {
			$colArray = $colOrArray;
			$cols = implode(', ', $where['cols']);

			$this->column = $cols;
		}else if (is_string($colOrArray)) {
			$col = $colOrArray;
			
			$this->column = $col;
		}
		
		return $this;
	}

	private function appendClause() {
		$clauses = [$this->where, $this->orderBy, $this->groupBy, $this->limit];

		$sql = '';
		foreach ($clauses as $clause) {
			if (!empty($clause))
				$sql .= " $clause ";
		}

		return $sql;
	}

	private function truncateClause() 
	{
		$clauses = ['where', 'orderBy', 'groupBy', 'limit', 'limit', 'results'];

		foreach ($this as $prop => $val) {
			if (in_array($prop, $clauses))
				$this->$prop = '';
		}
	}

	public function insert($params) 
	{
		$table = $this->table;
		$fields = array_keys($params);
		$values = array_values($params);

		$sql = "INSERT INTO `$table` (`".implode('`, `', $fields)."`)";

		if (count($values)>0) {
			$sql .= " VALUES (";
			$x = 1;
			
			foreach ($values as $value) {
				$sql .= "?";

				if ($x < count($values)) {
					$sql .= ", ";
				}

				$x++;
			}
			
			$sql .= ")";
		}

		return $this->query($sql, $values);
	}

	public function update($params, $where = []) 
	{
		$table = $this->table;
		$fields = array_keys($params);
		$values = array_values($params);

		$sql = "UPDATE `$table` SET `".implode('` = ?, `', $fields)."` = ?";
		
		if (empty($this->where)) {
			if (count($where) > 0) {
				$sql .= " WHERE ";
				$x = 1;
				foreach ($where as $key => $value) {
					$sql .= "`$key` = ?";

					if ($x < count($where)) {
						$sql .= ", ";
					}

					array_push($values, $value);
					$x++;
				}
			}
		}else {
			$where = $this->whereArr;
		}

		$sql .= $this->appendClause();

		// echo $sql;
		return $this->query($sql, $values);
	}

	public function delete() {}

	public function error() 
	{
		return $this->error;
	}

	public function add($add)
	{
		if (!empty($add)) $this->add = $add;

		return $this;
	}

	public function count($order = '') 
	{
		if ($this->count>0) {
			if ($order=='->')
				return $this;

			return $this->count;
		}

		return false;
	}

	public function fetchMode($type)
	{
		switch ($type) {
			case '{}':
				$this->query->setFetchMode(PDO::FETCH_OBJ);
			break;

			case '[]':
				$this->query->setFetchMode(PDO::FETCH_ASSOC);
			break;
		}
	}

	public function record($type = '{}') 
	{		
		$this->fetchMode($type);

		if ($this->count>0&&empty($this->results)) {
			$this->results = $this->query->fetchAll();
		}

		return $this->results;
	}

	public function stash($type = '{}')
	{
		$this->fetchMode($type);

		if ($this->count>0&&empty($this->results)) {
			$this->results = $this->query->fetchAll();
		}

		$checker = ['[]', '{}'];
		if (!in_array($type, $checker) && (is_string($type) || is_numeric($type)))
			return $this->results[$type];

		return $this->results[0];
	}

	public function table($table) 
	{
		Wingwah\User::$table = $table;

		if (isset($this)) {
			$this->table = $table;
			
			return $this;
		}else {
			$obj = new self;

			$obj->table = $table;

			return $obj;
		}
	}

	public function whereAnd($col) 
	{
		return $this->where($col, '&&');
	}

	public function whereOr($col) 
	{
		return $this->where($col, '||');
	}

	public function whereIn($col, $valOrSep = '&&') 
	{
		if (!empty($col)&&!empty($valOrSep)) {
			if (is_array($col)) {
				$where = " WHERE ";
				$x = 1;
				$sep = empty($valOrSep) ? '&&' : $valOrSep;
				$this->whereArr = $col;

				foreach ($col as $key => $value) {
					if ($x==1)
						$where .= "`$key` IN (";

					$where .= ":$key";
					if ($x < count($col)) {
						$where .= ", ";
					}

					$x++;
				}
				$where .= ")";
			}else {
				$where = "WHERE $col IN ( $valOrSep )";
			}
			
			$this->where = $where;
		}

		return $this;
	}

	public function whereLike($col, $valOrSep = '&&') 
	{
		if (!empty($col)&&!empty($valOrSep)) {
			if (is_array($col)) {
				$where = " WHERE ";
				$x = 1;
				$sep = empty($valOrSep) ? '&&' : $valOrSep;
				$this->whereArr = $col;

				foreach ($col as $key => $value) {
					$where .= "`$key` LIKE ";

					$where .= ":$key";
					if ($x < count($col)) {
						$where .= " $sep ";
					}

					$x++;
				}
			}else {
				$where = "WHERE $col LIKE '$valOrSep'";
			}
			
			$this->where = $where;
		}

		return $this;
	}

	public function where($colOrQuery, $valOrSep = '') 
	{
		if (!empty($colOrQuery)) {
			$where = " WHERE ";
			if (is_array($colOrQuery)) {
				$x = 1;
				$sep = empty($valOrSep) ? '&&' : $valOrSep;
				$this->whereArr = $colOrQuery;

				foreach ($colOrQuery as $key => $value) {
					$where .= "`$key` = :$key";
					if ($x < count($colOrQuery)) {
						$where .= " $sep ";
					}
					$x++;
				}
			}else {				
				if (preg_match('/\?|:[a-zA-Z0-9-_.]*/', $colOrQuery)&&is_array($valOrSep)) {
					$where .= $colOrQuery;
					$cols = $valOrSep;

					$this->whereArr = $cols;
				}else {
					$val = strval($valOrSep);
					$where .= "$colOrQuery = '$val'";
				}
			}

			$this->where = $where;
		}

		// if (is_static()) {
		// 	$obj = new self;

		// 	$obj->where = $where;

		// 	return $obj;
		// }else {
		// 	$this->where = $where;
			
		// 	return $this;
		// }
		
		return $this;
	}

	public function orderBy($order, $sort = 'asc') 
	{
		if (!empty($order)) {
			$this->orderBy = "ORDER BY $order $sort";
		}

		return $this;
	}

	public function groupBy($group) 
	{
		if (!empty($group))
			$this->groupBy = "GROUP BY $group";

		return $this;
	}

	public function limit($limit) 
	{
		if (!empty($limit))
			$this->limit = "LIMIT $limit";

		return $this;
	}

	/**
	 * 
	 */
	public function as($alias)
	{
		if (!empty($alias))
			$this->as = "AS `".trim($alias)."`";

		return $this;
	}

	/**
	 * 
	 */
	public function leftJoin($table)
	{
		if (!empty($table))
			$this->leftJoin = "LEFT JOIN `".trim($table)."`";

		return $this;
	}

	/**
	 * 
	 */
	public function on($con, $val = '')
	{
		if (!empty($con)) {
			if (!empty($val))
				$this->on = "ON ".trim($con)." = ".trim($val);
			else
				$this->on = "ON ".trim($con);
		}

		return $this;
	}
	
	public function lastInsertId()
	{
		if ($this->count()>0)
			return $this->handle->lastInsertId();
	}
}
