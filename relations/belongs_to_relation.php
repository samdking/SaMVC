<?php

class Belongs_to_relation extends Relation
{
	
	function __construct($name, $args = array())
	{
		$defaults = array('foreign_key' => $name . '_id');
		parent::__construct($name, array_merge($defaults, $args));
	}
	
	function join_statement($to)
	{
		return array(
			array(
				'table' => $this->table_alias(),
				'on' => array($to->name() . '.' . $this->foreign_key => $this->primary_key()),
				'required' => $this->required
			)
		);
	}
	
	function collection($obj)
	{      
      $fk = $this->foreign_key();
	   return $this->model()->find($obj->$fk);
	}
	
   function multi_collection($vals = NULL)
	{
	   $fk = $this->foreign_key();
	   foreach($vals as $val)
	      $values[] = $val->$fk;
	   if (isset($values))
      	$values = array_unique($values);
      return $this->model()->find($values);
	}
	
	function assign_results($results)
	{   
	   $arr = $this->multi_collection($results);
	   $prop = $this->name();
	   $fk = $this->foreign_key;
	   
	   foreach($arr as $item)
	      $result[$item->id()] = $item;
	      
	   foreach($results as $obj) {
         if (isset($result[$obj->$fk]))
            $obj->$prop = $result[$obj->$fk];
	   }
	   
	   return $results;
	}
	
}