<?php

require 'collection.php';

abstract class Relation
{
	protected $name;
	protected $model;
	protected $foreign_key;
	protected $primary_key;
	protected $required;
	protected $related_name;
	
	function __construct($name, $args = array())
	{
		$this->required = true;
		$this->name = $name;
		if (isset($args['model']))
			$this->model = $args['model'];
		if (isset($args['required']))
			$this->required = $args['required'];
		if (isset($args['related_name']))
   	   $this->related_name = $args['related_name'];
		$this->foreign_key = $args['foreign_key'];
	}
	
	function table_alias()
	{
		return $this->model()->table_alias($this->name);
	}
	
	function name()
	{
		return $this->name;
	}
	
	function required()
	{
		return (Boolean)$this->required;
	}
	
	function model($instantiate = true)
	{
		if (!$this->model)
			$this->model = Inflector::singularise($this->name);		
		return $instantiate? Model::get($this->model) : $this->model;
	}
	
	function use_plural($plural)
	{
		$this->name = $plural;
		return $this;
	}
	
	abstract function join_statement($to);
	
	function foreign_key()
	{
		return $this->foreign_key;
	}
	
	function add_result($value)
	{
		$this->result = $value;
	}
	
	function has_result()
	{
		return isset($this->result);
	}
	
	function get_result()
	{
		return $this->result;
	}
	
	function get_reverse_rel()
	{
	   $name = $this->related_name;
	   return $this->model()->find_relationship($name);
	}
	
	function related()
	{
	   return $this->related_name;
	}
	
	function primary_key()
	{
	   return $this->model()->primary_key($this->name);
	}
	
	function related_alias($obj = NULL)
	{
	   $obj = $obj? $obj : $this->model();
	   return $this->related_name . '.' . $obj->id_field;
	}
}