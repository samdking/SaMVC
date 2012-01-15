<?php

class MySQL_handler extends DB_handler
{
   private $error;

   function connect($db)
   {
      mysql_connect($db['server'], $db['user'], $db['pass']);
   	mysql_query("SET NAMES 'utf8'");
   	mysql_select_db($db['name']);
   }
   
   function query($query)
	{
		$mysql_query = mysql_query($query);
		$this->error = mysql_error();
		if ($mysql_query) return $mysql_query;
	}
	
	function get_error()
	{
	   $error = $this->error;
	   unset($this->error);
	   return $error;
	}

   function result($result)
	{
		while($row = mysql_fetch_assoc($result))
			$data[] = $row;
		return isset($data)? $data : array();
	}
	
	function insert_id()
	{
		return mysql_insert_id();
	}
}