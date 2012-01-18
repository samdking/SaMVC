<?php

class MySQLi_handler extends DB_handler {
   
   private $handle;
   	
   function connect($db)
   {
      $this->handle = new mysqli($db['server'], $db['user'], $db['pass'], $db['name']);
      $this->handle->set_charset("utf8");
   }
   
	function query($query) 
	{
		$mysql_query = $this->handle->query($query);
		if ($mysql_query) return $mysql_query;
	}
	
	function result($result) 
	{
	   while($row = $result->fetch_assoc())
   		$data[] = $row;
   	return isset($data)? $data : array();
	}
	
	function insert_id() 
	{
	   return $this->handle->insert_id;
	}
	
	function get_error()
	{
	   return $this->handle->error;
   }
}