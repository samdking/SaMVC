<?php
function highlight($word)
{
   echo '<b style="color: red; font-size: 20px">' . $word . '</b>';
}
require 'sql_builder.php';

class RecordNotFoundException extends Exception {}
   
class MultipleRecordsFoundException extends Exception {}

class Model_set implements ArrayAccess, Iterator, Countable
{	
	protected $result = array();
	protected $sql;
	protected $has_result = false;
	private $includes = array();
	private $relations = array();
	private $annotations = NULL;
	private $of_model;
	private $single_result = false;
	private $additional_models = array();
	
	function __construct($model)
	{
		$this->of_model = $model;
		$this->setup_sql();
		$this->add_default_conditions($model->filter, $model->name());
	}
	
	function save()
	{
		if ($this->of_model->id())
			 // object already existed, update
			$this->update();
		else 
			// create new object and return it
			$this->insert();
			
		return $this->of_model;
	}
	
	private function update()
	{
		$props = $this->of_model->prepared_props();
		$this->sql->set($props)->conditions(array($this->of_model->id_field => $this->of_model->id()))->update();
	}
	
	private function insert()
	{
		$props = $this->of_model->prepared_props();
		$this->sql->set($props)->insert();
		$new_id = $this->sql->insert_id();
		$this->of_model->id($new_id);
	}
	
	function last()
	{
		$this->single_result = true;
		$this->sql->limit(1);
		$this->sql->order($this->of_model->id_field . ' DESC');
		return $this->retrieve_result();
	}

	function __toString()
	{
		return 'Model Set';
	}
	
	function find($pk)
	{   
		$params = func_get_args();
		if (is_array($pk) && count($pk) > 1 && is_int(key($pk)))
			return $this->find_many_by_pk($pk);
		
		if (count($params) > 1)
			return $this->find_many_by_pk($params);
		return $this->find_one_by_pk($pk);
	}
	
	function find_one_by_pk($pk)
	{	
		$this->single_result = true;
		if (is_array($pk))
			$this->filter($pk);
		else
			$this->sql->conditions(array($this->of_model->primary_key() => $pk));		
		$result = $this->retrieve_result();
		if (!$result)
			throw new RecordNotFoundException();
		if (count($results) > 1)
		   throw new MultipleRecordsFoundException();
		return $result;
	}
	
	function find_many_by_pk(Array $pks)
	{   
		$this->sql->conditions(array($this->of_model->primary_key() . ' in' => $pks));
		
		$results = $this->retrieve_result();
      
		if (count($results) < count($pks))
			throw new RecordNotFoundException();
		return $results;
	}
	
	function setup_sql()
	{	
		$this->sql = new Sql_builder;
		$this->sql->from($this->of_model->table_alias());
		$this->sql->select($this->of_model->name() . '.*');
	}
	
	function find_rels($arr)
	{
		$current = $this->of_model;
		foreach($arr as $name) {
			$rel = $current->model()->find_relationship($name);
			if (!$rel)
			   throw new Exception("No relationship called " . $name . " in " . get_class($current->model()) . " model.");
			$model = $rel->model();
			$key = get_class($model);
			if (isset($this->relations[$key]))
				continue;
			if ($rel->required())
				$this->add_default_conditions($model->filter, $rel->name());
			$this->relations[$key] = $rel;
			$this->additional_models[$name] = $rel;
			$this->sql->add_join($rel->join_statement($current));
			$current = $rel;
			}
		return $name;
	}
	
	function add_default_conditions($conditions, $pre)
	{
		foreach($conditions as $field => $value)
			$new_conditions[$pre . '.' . $field] = $value;
		if (isset($new_conditions))
			$this->sql->conditions($new_conditions);	
	}
	
	function format_field($field)
	{
		$parts = explode('.', $field);
		$field_name = array_pop($parts);
		$rels = $parts;
		if (is_array($this->annotations) && in_array(reset(explode(' ', $field_name)), $this->annotations) || strpos($field_name, '(') !== false)
		   return $field_name;
		elseif (empty($rels))
		   return $this->of_model->name() . '.' . $field_name;
		else
		   return $this->find_rels($rels) . '.' . $field_name;
	}
	
	function foreign_key_alias($field)
	{
		if (strpos($field, '.') !== false)
			return '_' . str_replace('.', '_', $field);
	}
	
	function one($conditions)
	{
		$this->single_result = true;
		$this->filter($conditions);
		return $this->retrieve_result();
	}
		
	function filter(Array $conditions)
	{
		foreach ($conditions as $field => $value)
		   $formatted_conditions[$this->format_field($field)] = $value;
		if (isset($formatted_conditions))
			$this->sql->conditions($formatted_conditions);
		return $this;
	}
	
	function exclude(Array $conditions)
	{
		foreach ($conditions as $field => $value)
			$formatted_conditions[$this->format_field($field)] = $value;
		if (isset($formatted_conditions))
			$this->sql->unconditions($formatted_conditions);
		return $this;
	}
	
	function select($fields)
	{	
		$this->sql->reset_fields();
		$fields = (!is_array($fields))? func_get_args() : $fields;
		foreach ($fields as $field)
			$this->sql->select($this->format_field($field), $this->foreign_key_alias($field));
		return $this;
	}
	
	function sort($fields)
	{
		$fields = (!is_array($fields))? func_get_args() : $fields;
		foreach ($fields as $field)
			$this->sql->order($this->format_field($field));
		return $this;
	}
	
	function group($fields)
	{		
		$fields = (!is_array($fields))? func_get_args() : $fields;
		foreach ($fields as $field)
			$this->sql->group($this->format_field($field));
		return $this;
	}
	
	function limit($num_offset, $num = NULL)
	{
	   $this->sql->limit($num_offset, $num);
	   return $this;
	}
	
	function annotate($func, $field)
	{
	   $field = $this->format_field($field);
	   $field_name = array_shift(explode('.', $field));
	   $this->annotations[] = $field_name . '_' . $func; 
	   $this->sql->select($func . '(' . $field . ')', $field_name . '_' . $func);
	   $this->sql->group($this->of_model->primary_key());
	   return $this;
	}
	
	function includes($rel_name)
	{
	   $this->includes[] = $this->of_model->find_relationship($rel_name);
	   return $this;
	}
	
	function retrieve_result()
	{	   
	   if ($this->has_result) 
			return $this->result;
		try {
			$results = $this->sql->query();
		} catch (DbException $e) {
			echo '<pre class="error">' . $e->getMessage() . '</pre>';
			return;
		}	
		$this->has_result = true;
		
	   $base_model = get_class($this->of_model);
		foreach($results as $i=>$row_data) {
			$base_obj = new $base_model($row_data);
			/*foreach($this->additional_models as $name => $rel) {
				$assoc_model = $rel->model();
				foreach($row_data as $field => $val) {
					if (strpos($field, '_' . $name . '_') !== 0)
						continue;
					$real_field_name = substr($field, strlen('_' . $name . '_'));
					$assoc_model->$real_field_name = $val;
					unset($base_obj->$field);
					if (isset($base_obj->{$rel->foreign_key()})) {
						$fk = $rel->foreign_key();
						$foreign_key_val = $base_obj->$fk;
						unset($base_obj->$fk);
						$assoc_model->id($foreign_key_val);
					}
				}
				if ($assoc_model->id())
				   $base_obj->$name = $assoc_model;
			}*/
			$this->result[$i] = $base_obj;
		}
		
      foreach($this->includes as $rel) {
         $rel->assign_results($this->result);
		}
		
   	if ($this->single_result)
   		$this->result = reset($this->result);
		return $this->result;
	}
	
	function add_result($arr)
	{  
      $this->has_result = true;
	   $this->result = $arr;
	   return $this;
	}
	
	function results()
	{
	   return $this->result;
	}
	
	/* Array Access */
	
	function offsetGet($key)
	{
		$result = $this->retrieve_result();
		if (isset($result[$key]))
			return $result[$key];
		return false;
	}
	
	function offsetSet($key, $val)
	{
		$this->retrieve_result();
		$this->result[$key] = $val;
	}
	
	function offsetUnset($key)
	{
		$this->retrieve_result();
		if (isset($this->result[$key]))
			unset($this->result[$key]);
	}
	
	function offsetExists($key)
	{
		$this->retrieve_result();
		if (isset($this->result[$key]))
			return true;
	} 
	
	/* Iterator */
	
	function rewind() 
	{
		$this->retrieve_result();
		reset($this->result);
    }

    function current() 
	{
		$this->retrieve_result();
        return current($this->result);
    }

    function key() 
	{
		$this->retrieve_result();
        return key($this->result);
    }

    function next() 
	{
		$this->retrieve_result();
         return next($this->result);
    }

    function valid() 
	{
		$this->retrieve_result();
        return false !== current($this->result);
    }
	
	/* Countable */
	
	function count()
	{
		if ($this->has_result) 
			return count($this->result);
		$results = $this->sql->count_query();
		return reset(reset($results));
	}
}