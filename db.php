<?php

include 'db_handler.php';

class DbException extends Exception {}

class Db
{
	private static $instance;
	private $handler;
	
	private function __construct()
	{
		global $db;
		$this->select_handler($db);
	}
	
	function select_handler($db)
	{
	   $name = $db['handler'];
	   $location = SYS_PATH . 'db_handlers/' . strtolower($name) . '.php';
	   if (!file_exists($location))
	      throw new DBException('No handler selected');
	   include $location;
	   $class_name = strtolower($name) . '_handler';
	   $this->handler = new $class_name($db); 
	}

	function init()
	{
		self::$instance = self::$instance? self::$instance : new self;
		return self::$instance->handler;
	}

}