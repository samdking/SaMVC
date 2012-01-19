<?php

require 'model_set.php';

class Model extends Base
{
	protected $db_table;
	protected $model_name;
	protected $relationships;
	protected $properties;
	public $filter = array();
	public $id_field;
	
	function __construct($data = array())
	{
		$this->properties = $data;
		$this->get_db_table_name();
		if (!isset($this->id_field))
			$this->id_field = 'id';
	}
	
	function properties()
	{
		return $this->properties;
	}
	
	// used to get value of ID:
	// e.g. $obj->id();
	// also used to set value of ID:
	// e.g. $obj->id(5);
	function id($setter = NULL)
	{
		$id_field = $this->id_field;
		if (!is_null($setter))
			$this->$id_field = $setter;
		if (isset($this->$id_field))
		   return $this->$id_field;
	}
	
	function __call($method, $args)
	{
		$model_set = $this->get_model_set();
		if (method_exists($model_set, $method))
			return call_user_func_array(array($model_set, $method), $args);
	}
	
	function __isset($prop)
	{
		return (isset($this->properties[$prop]));
	}
	
	function __unset($prop)
	{
		unset($this->properties[$prop]);
	}
	
	function __set($prop, $val)
	{
		if (isset($this->relationships[$prop])) {
			$foreign_key = $this->relationships[$prop]->foreign_key();
			unset($this->$foreign_key);
		}
		$this->properties[$prop] = $val;
	}
	
	function get_rel_model_set($method)
	{
		$rel = $this->relationships[$method];
		$model_set = $rel->collection($this);
		$this->$method = $model_set;
		return $model_set;
	}
	
	function __get($prop)
	{
	   if (isset($this->properties[$prop]))
			return $this->properties[$prop];
		if (isset($this->relationships[$prop]))
			return $this->get_rel_model_set($prop);
		$debug = debug_backtrace();
		trigger_error('No '.$prop.' attribute on ' . get_class($this) . ' (' . basename($debug[0]['file']) . ':' . $debug[0]['line'] . ')');
	}
	
	static function get($model_name)
	{
	   if (!$model_name)
	      return false;
		if (!class_exists($model_name))
			include self::find_file('models/' . strtolower($model_name));
		return new $model_name;
	}
	
	function all()
	{
		return $this->get_model_set();
	}
	
	function get_model_set()
	{
		return new Model_set($this);
	}
	
	function prevent_naming_clashes($name)
	{
		$methods = get_class_methods('Model') + get_class_methods('Model_set');
		if (in_array($name, $methods))
			throw new Exception('Cannot make relationship <b>' . $name . '</b>');
	}
	
	function has_mm($name, $args = array())
	{
	   $this->prevent_naming_clashes($name);
		if (!isset($args['join_table'])) {
		   $lexical_order = array($name, Inflector::pluralise($this->name()));
		   sort($lexical_order);
		   $args['join_table'] = implode('_mm_', $lexical_order);
   	}
   	if (!isset($args['related_name']))
   	   $args['related_name'] = Inflector::pluralise($this->name());
   	if (!isset($args['foreign_key']))
   		$args['foreign_key'] = $this->name() . '_id';
   	if (!isset($args['related_foreign_key']))
   	   $args['related_foreign_key'] = Inflector::singularise($name) . '_id';
	   $this->relationships[$name] = new Has_mm_relation($name, $args);
	}
	
	function has_many($name, $args = array())
	{
		$this->prevent_naming_clashes($name);
   	if (!isset($args['related_name']))
   	   if (isset($args['through']))
   	      $args['related_name'] = Inflector::pluralise($this->name());
		   else
		      $args['related_name'] = $this->name();
		if (!isset($args['foreign_key']))
			$args['foreign_key'] = $this->name() . '_id';
		if (isset($args['through']))
			if (!Model::get($args['through']))
				throw new Exception("Couldn't find model '".$args['through']."'");
		$this->relationships[$name] = new Has_many_relation($name, $args);
	}
	
	function belongs_to($name, $args = array())
	{	
		$this->prevent_naming_clashes($name);
		$this->relationships[$name] = new Belongs_to_relation($name, $args);
	}
	
	function find_relationship($name)
	{
		if (isset($this->relationships[$name]))
			return $this->relationships[$name];
		$singular_name = Inflector::singularise($name);
		if (isset($this->relationships[$singular_name]))
			return $this->relationships[$singular_name];
		return false;
	}
	
	function find_relation_by_model($model_name)
	{
	   foreach($this->relationships as $name => $rel)
	      if ($rel->model(false) == strtolower($model_name))
	         return $rel;
	}
	
	function name()
	{
		if (!$this->model_name) 
			$this->model_name = strtolower(get_class($this));
		return $this->model_name;
	}
	
	function table_alias($alias = NULL)
	{
		$alias = is_null($alias)? $this->name() : $alias;
		if ($this->db_table == $alias)
			return $this->db_table;
		return $this->db_table . ' AS `' . $alias . '`';
	}
	
	private function get_db_table_name()
	{
		if (isset($this->db_table))
			return;
		$this->db_table = Inflector::pluralise(strtolower(get_class($this)));
	}
	
	function model()
	{
	   return $this;
	}
	
	function primary_key($alias = NULL)
	{
	   $alias = is_null($alias)? $this->name() : $alias;
		return $alias . '.' . $this->id_field;
	}
	
	function __toString()
	{
		$text = "\n".'<div class="obj">'."\n";
		$text .= '<u>'.get_class($this).'</u>';
		foreach((array)$this->properties as $key=>$val) 
			$text .= "<br />\n" . $key . ' => ' . $val;
		$text .= "</div>\n";
		return $text;
	}
	
	function add_result_to_relation($rel_name, $value)
	{
		if (isset($this->relationships[$rel_name]))
			$this->relationships[$rel_name]->add_result($value);
	}
	
	function inherits($model_name)
	{
		Model::get($model_name);
	}
	
	function prepared_props()
	{
		$props = $this->properties;
		foreach($props as $key => $val) {
			if (is_object($val)) {
				if (!$val->id())
					$props[$key] = $val->save()->id();
				else
					$props[$key] = $val->id();
				$real_key = $this->relationships[$key]->foreign_key();
				$props[$real_key] = $props[$key];
				unset($props[$key]);
			}
		}
		return $props;
	}

}