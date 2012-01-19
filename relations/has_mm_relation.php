<?php

class Has_mm_relation extends Relation
{   
   protected $join_table;
   protected $related_foreign_key;
   protected $related_name;
   
	function __construct($name, $args = array())
	{
		$this->join_table = $args['join_table'];
	   $this->related_foreign_key = isset($args['related_foreign_key'])? 
	      $args['related_foreign_key'] : 
	      Inflector::singularise($name) . '_id';
		parent::__construct($name, $args);
	}
	
	function related_foreign_key()
	{
	   return $this->related_foreign_key;
	}
	
	function join_table()
	{
	   return $this->join_table;
	}
	
	function join_statement($to)
	{
      return array(
			array(
				'table' => $this->join_table,
				'on' => array($this->join_table . '.' . $this->foreign_key => $to->primary_key()),
				'required' => $this->required
			),
			array(
				'table' => $this->table_alias(),
				'on' => array($this->join_table . '.' . $this->get_reverse_rel()->foreign_key => $this->primary_key()),
				'required' => true
			)
		);
	}
	
	function collection($obj)
	{
	   $fk = $this->foreign_key();
	   $collection = new Collection($this->model(), $this);
	   return $collection->filter(array($this->related_alias() => $obj->$fk));
	}

   function multi_collection($vals = NULL)
	{
	   foreach($vals as $val)
	      $values[] = $val->id();
	   if (isset($values))
      	$values = array_unique($values);
   	return $this->model()->filter(array($this->related_alias() => $values))->select('*', $this->related_alias());
	}

   function assign_results($results)
	{
   	$arr = $this->multi_collection($results);
	   $fk = '_' . $this->related() . '_id';
   	$prop = $this->name();
	   
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