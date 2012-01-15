<?php

abstract class Db_handler {
   
	protected $queries = array();
	
	function __construct($db)
	{
	   $this->connect($db);
	}

   function queries()
	{
		return $this->queries;
	}
	
	function execute_query($query)
	{
   	array_push($this->queries, htmlspecialchars($query));
	   if (!$result = $this->query($query))
	      throw new DbException("Couldn't excute the query: " . $query . " because: " . $this->get_error());
	   else
	      return $result;
	}
	
	function get_result($sql)
	{
	   return $this->result($this->execute_query($sql));
	}

   abstract function connect($db);
	abstract function query($sql);
	abstract function result($result);
	abstract function get_error();
	abstract function insert_id();
	
}