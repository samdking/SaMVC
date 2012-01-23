<?php

class Has_mm_relation extends Relation
{   
   protected $join_table;
   protected $related_foreign_key;
   protected $through;
   
	function __construct($name, $obj, $args = array())
	{	
   	parent::__construct($name, $obj, $args);
		if ($this->through)
		   $this->foreign_key = NULL;
	}
	
	function defaults($defaults)
	{
	   $defaults['related_name'] = $this->object->name();
	   $defaults['foreign_key'] = Inflector::singularise($this->object->name()) . '_id';
	   return $defaults;
	}
	
	function join_table()
	{
	   if (!$this->join_table) {
	      if ($this->through) {
	         $this->join_table = Model::get($this->through)->table_alias(false);
	      } else {
	         $lexical_order = array($this->name(), $this->get_reverse_rel()->name());
   		   sort($lexical_order);
   		   $this->join_table = implode('_mm_', $lexical_order);
	      }
	   }
	   return $this->join_table;
	}
   
	function foreign_key()
	{
	   if (!$this->foreign_key)
	      if ($this->through)
	         $this->foreign_key = Model::get($this->through)->find_relation_by_model(get_class($this->object))->foreign_key;
	      else
	         if (isset($this->get_reverse_rel()->foreign_key))
   	         $this->foreign_key = $this->get_reverse_rel()->foreign_key;
   	      else
   	         $this->foreign_key = Inflector::singularise($this->name()) . '_id';
   	return $this->foreign_key;
	}

	function related_foreign_key()
	{
	   if (!$this->related_foreign_key)
	      if ($this->through)
	         $this->related_foreign_key = Model::get($this->through)->find_relation_by_model($this->model(false))->foreign_key;
	      else
	         $this->related_foreign_key = $this->get_reverse_rel()->foreign_key();
	   return $this->related_foreign_key;
	}
	
	function join_statement($required = false)
	{
	   $required = $required? $required : $this->required;
      return array(
			array(
				'table' => $this->join_table(),
				'on' => array($this->join_table() . '.' . $this->foreign_key() => $this->object->primary_key()),
				'required' => $required
			),
			array(
				'table' => $this->table_alias(),
				'on' => array($this->join_table() . '.' . $this->related_foreign_key() => $this->primary_key()),
				'required' => $required
			)
		);
	}
	
	function collection($obj)
	{
	   $fk = $this->through? $this->model()->id_field : $this->foreign_key;
	   $fk = $this->model()->id_field;
	   $collection = new Collection($this->model(), $this);
	   return $collection->filter(array($this->related_alias() => $obj->$fk))->select('*', $this->related_alias());
	}

   function assign_results($results, $includes)
	{
   	$vals = $this->get_primary_keys($results);
   	$arr = $this->model()->filter(array($this->related_alias() => $vals))->select('*', $this->related_alias())->add_preload($includes);
   	$prop = $this->name();
	   $fk = '_' . $this->related() . '_id';
	   
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