<?php

class Belongs_to_relation extends Relation
{
	
	function defaults($defaults)
	{
	   $defaults['related_name'] = $this->object->name();
	   $defaults['foreign_key'] = $this->name() . '_id';
	   return $defaults;
	}
	
	function join_statement($required = false)
	{
	   $required = $required? $required : $this->required;
		return array(
			array(
				'table' => $this->table_alias(),
				'on' => array($this->object->name() . '.' . $this->foreign_key => $this->primary_key()),
				'required' => $required
			)
		);
	}
	
	function collection($obj)
	{
	   $fk = $this->foreign_key();
	   return $this->model()->find($obj->$fk);
	}
	
	function assign_results($results, $includes)
	{ 
	   $vals = $this->get_primary_keys($results, $this->foreign_key());
	   $arr = $this->model()->add_preload($includes)->find($vals);
	   $prop = $this->name();
	   $fk = $this->foreign_key;
	   foreach($arr as $item)
	      $result[$item->id()] = $item;
	      
	   foreach($results as $obj)
         if (isset($result[$obj->$fk]))
            $obj->$prop = $result[$obj->$fk];
	   
	   return $results;
	}
	
}