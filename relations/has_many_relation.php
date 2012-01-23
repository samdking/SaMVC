<?php

class Has_many_relation extends Relation
{	
   
	function defaults($defaults)
	{
	   $defaults['related_name'] = $this->object->name();
	 //  $defaults['foreign_key'] = $this->name() . '_id';
	   return $defaults;
	}
	
	function foreign_key()
	{
	   if (!$this->foreign_key)
	      if ($this->get_reverse_rel()->foreign_key)
	         $this->foreign_key = $this->get_reverse_rel()->foreign_key;
	      else
	         $this->foreign_key = $this->name() . '_id';
	   return $this->foreign_key;
	}
	
	function join_statement($required = false)
	{   
	   $required = $required? $required : $this->required;
	   return array(array(
   	   'table' => $this->table_alias(),
   	   'on' => array($this->name() . '.' . $this->foreign_key() => $this->object->primary_key()),
   	   'required' => $required
   	));
	}
	
	function collection($obj)
	{
	   $fk = $this->foreign_key();
	   $collection = new Collection($this->model(), $this);
	   return $collection->filter(array($this->related_alias() => $obj->$fk));
	}
	
	function assign_results($results, $includes)
	{
	   $vals = $this->get_primary_keys($results);
	   $arr = $this->model()->filter(array($this->related_alias() => $vals))->add_preload($includes);
	   $prop = $this->name();
	   $fk = $this->get_reverse_rel()->foreign_key;
	   
      foreach($arr as $item)
	      $result[$item->$fk][] = $item;
	
	   foreach($results as $obj) {
	      $collection = new Collection($this->model(), $this);
         if (isset($result[$obj->id()]))
            $obj->$prop = $collection->add_result($result[$obj->id()]);
         else
            $obj->$prop = $collection->add_result(array());
	   }
	   return $results;
	}

}