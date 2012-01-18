<?php

class Has_many_relation extends Relation
{
	protected $through;
	
	function __construct($name, $args = array())
	{
		if (isset($args['through']))
			$this->through = $args['through'];
		parent::__construct($name, $args);
	}
	
	function join_statement($to)
	{   
	   if (!$this->through) {
	      return array(array(
   	      'table' => $this->table_alias(),
   	      'on' => array($this->name() . '.' . $this->get_reverse_rel()->foreign_key => $to->primary_key()),
   	      'required' => $this->required
   	   ));
      }
		$through_model = Model::get($this->through);
	   $rel_name = Inflector::pluralise($through_model->name());
	   $through_rel = $through_model->find_relationship($this->name);
	   var_dump($through_rel);
	   return array(
	      array(
	         'table' => $through_model->table_alias($rel_name),
	         'on' => array($rel_name . '.' . $through_rel->get_reverse_rel()->foreign_key => $to->primary_key()),
	         'required' => $this->required
	      ),
	      array(
	         'table' => $through_rel->table_alias(),
	         'on' => array($rel_name . '.' . $through_rel->foreign_key => $through_rel->primary_key()),
	         'required' => $this->required
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
	   if (is_array($values))
      	$values = array_unique($values);
   	return $this->model()->filter(array($this->related_alias() => $values))->select('*', $this->related_alias());
	}
	
	function assign_results($results)
	{
	   $arr = $this->multi_collection($results);
	   $fk = $this->through? '_' . $this->related() . '_id' : $this->get_reverse_rel()->foreign_key;
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