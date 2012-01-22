<?php

class Sql_builder
{	
	private $select;
	private $table;
	private $joins;
	private $where;
	private $group;
	private $having;
	private $order;
	private $limit;
	private $set;
		
	function order($param)
	{	
		$this->order[] = $param;
	}
	
	function group($param)
	{
		$this->group[] = $param;
	}
	
	function limit($limitoffset, $limit = NULL)
	{
		$this->limit = $limitoffset;
		if (!is_null($limit))
			$this->limit .= ', ' . $limit;
	}
	
	function from($table)
	{
		$this->table = $table;
	}
	
	function add_join($joins)
	{
		foreach($joins as $join)
			$this->joins[] = ($join['required'] === false? 'LEFT JOIN' : 'INNER JOIN') . ' ' . $join['table'] . ' ON ' . key($join['on']) . ' = ' . reset($join['on']);
	}
	
	function select($field, $alias = NULL)
	{
		$this->select[] = $field . (is_null($alias)? '' : ' AS ' . $alias);
	}
	
	function reset_fields()
	{
		$this->select = array();
	}

	function val($str)
	{
		$sql_funcs = array('CURDATE', 'NOW');
		$arr = is_array($str)? $str : array($str);
		foreach($arr as &$item) {
			if (!(is_numeric($item) OR $item instanceOf Literal OR in_array($item, $sql_funcs)))
				$item = "'" . $item . "'";
		}
		return (count($arr) > 1)? implode(',', $arr) : reset($arr);
	}

	function format_condition($field, $operator, $value)
	{
		$escaped_value = str_replace(array('%', '_'), array('\%', '\_'), $value);
		switch(strtolower($operator)) {
			case 'equals':
				return $field . ' = ' . $this->val($value);
			case 'begins with':
				return $field . ' LIKE ' . $this->val('%' . $escaped_value);
			case 'ends with':
				return $field . ' LIKE ' . $this->val($escaped_value . '%');
			case 'contains':
			 	return $field . ' LIKE ' . $this->val('%' . $escaped_value . '%');
			case '<':
			case 'less than':
			 	return $field . ' < ' . $this->val($value);
			case '>':
			case 'greater than':
			 	return $field . ' > ' . $this->val($value);
			case '>=':
			case 'greater than or equal to':
			 	return $field . ' >= ' . $this->val($value);
			case '<=':
			case 'less than or equal to':
				return $field . ' <= ' . $this->val($value);
			case 'in':
			 	return $field . ' IN (' . $this->val($value) . ')';
			case 'is':
			 	return $field . ' IS ' . $value;
			case 'between':
			 	return $field . ' BETWEEN ' . $value[0] . 'AND' . $value[1];
			case 'year':
			case 'month':
			case 'day':
				return strtoupper($operator) . '(' . $field . ') = ' . $this->val($value);
			default:
				if (is_array($value) && count($value) > 1)
				   return $this->format_condition($field, 'in', $value);
				else
				   return $this->format_condition($field, 'equals', $value);
		}
	}
	
	function conditions($arr)
	{
		foreach($arr as $rule => $value) {
			$parts = explode(' ', $rule, 2);
			$field = array_shift($parts);
			$operator = (isset($parts[0]))? $parts[0] : NULL;
			$conditions[] = $this->format_condition($field, $operator, $value);
		   if (strpos($field, '.') === false)
		      $having_conditions = $conditions;
		}
		if (isset($having_conditions))
		   $this->having[] = $conditions;
		else
		   $this->where[] = $conditions;
		return $this;
	}

	function query()
	{	
		extract(get_object_vars($this));

		$sql[] = "SELECT " . implode(', ', $select);	
		$sql[] = "FROM " . $table;
		
		if (isset($joins))
			foreach($joins as $join)
				$sql[] = $join;
		
		if (isset($where)) {
			foreach($where as $conditions)
				$where_conditions[] = '('.implode("\n  AND ", $conditions).')';
			$sql[] = "WHERE " . implode("\n  AND ", $where_conditions);
		}
		
		if (isset($group))
			$sql[] = "GROUP BY " . implode(', ', $group);
		
		if (isset($having)) {
			foreach($having as $conditions)
				$having_conditions[] = '('.implode("\n  AND ", $conditions).')';
			$sql[] = "HAVING " . implode("\n  AND ", $having_conditions);
		}
		
		if (isset($order))
			$sql[] = "ORDER BY " . implode(', ', $order);
		
		if (isset($limit))
			$sql[] = "LIMIT " . $limit;

		$sql = implode("\n", $sql); // perform this query
		
		return DB::init()->get_result($sql);
	}
	
	function reset_select()
	{
	   $this->select = NULL;
	}
	
	function count_query()
	{
		$this->select = array('count(*)');
		return $this->query();
	}
	
	function set($keyvals)
	{
	   DB::init();
		foreach($keyvals as $key => $val)
			$this->set[] = $this->format_condition($key, 'equals', mysql_real_escape_string($val));
		return $this;
	}
	
	function insert()
	{
		$sql[] = "INSERT INTO " . reset(explode(' ', $this->table));
		$sql[] = "SET " . implode(",\n    ", $this->set);
		$sql = implode("\n", $sql); // perform this query
		DB::init()->query($sql);
	}
	
	function insert_id()
	{
		return DB::init()->insert_id();
	}
	
	function update()
	{
		$sql[] = "UPDATE " . $this->table;
		$sql[] = "SET " . implode(",\n    ", $this->set);
		foreach($this->where as $conditions)
			$where_conditions[] = '('.implode("\n  AND ", $conditions).')';
		$sql[] = "WHERE " . implode("\n  AND ", $where_conditions);
		$sql = implode("\n", $sql); // perform this query
		DB::init()->query($sql);
	}
	
}

class Literal { }