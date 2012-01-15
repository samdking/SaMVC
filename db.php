<?php

class DbException extends Exception {}

class Db
{
	private static $instance;
	private $queries;
	
	function __construct()
	{
		global $db;
		$this->queries = array();
		mysql_connect($db['server'], $db['user'], $db['pass']);
		mysql_query("SET NAMES 'utf8'");
		mysql_select_db($db['name']);
	}

	function init()
	{
		self::$instance = self::$instance? self::$instance : new self;
		return self::$instance;
	}
	
	function query($query)
	{
		array_push($this->queries, htmlspecialchars($query));
		$mysql_query = mysql_query($query);
		$error = mysql_error();
		if ($mysql_query) return $mysql_query;
		throw new DbException("Couldn't excute the query: " . $query . " because: " . $error);
	}
	
	function insert_id()
	{
		return mysql_insert_id();
	}
	
	function result($sql)
	{
		$result = $this->query($sql);
		while($row = mysql_fetch_assoc($result))
			$data[] = $row;
		return isset($data)? $data : array();
	}
	
	function queries()
	{
		return $this->queries;
	}

}