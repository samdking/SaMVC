<?php

require 'collection.php';

abstract class Relation
{
	protected $name;
	protected $model;
	protected $foreign_key;
	protected $required;
	protected $related_name;
	protected $object;
	
	function __construct($name, $obj, $args = array())
	{
	   $defaults = array(
	     'model' => NULL,
	     'required' => false,
	     'related_name' => NULL
	   );
	   $this->object = $obj;
		$this->name = $name;
   	$args = array_merge($this->defaults($defaults), $args);
		$props = array_keys(get_class_vars(get_class($this)));
		foreach($args as $key => $val)
		   if (in_array($key, $props))
		      $this->$key = $val;
	}
	
	function defaults($defaults)
	{
	   return $defaults;
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
	
	abstract function join_statement();
	
	function foreign_key()
	{
		return $this->foreign_key;
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

   function get_primary_keys($vals = array(), $field = NULL)
	{
	   foreach($vals as $val)
	      $values[] = $field? $val->$field : $val->id();
	   if (is_array($values))
      	$values = array_values(array_unique($values));
   	return $values;
	}

}