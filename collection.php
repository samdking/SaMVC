<?php

class Collection extends Model_set
{
   private $relation;
   private $object_id;
   
   function __construct($model, $rel)
   {
      parent::__construct($model);
      $this->relation = $rel;
   }
   
   function add($object)
   {
      if (!$object)
         return $this;
      $this->sql = new Sql_builder; 
   	$props = array(
   	   $this->relation->foreign_key() => $this->object_id,
   	   $this->relation->join_key() => $object->id
   	);
   	$this->sql->from($this->relation->join_table());
   	$this->sql->set($props);
		$this->sql->insert();
		return $this;
   }
   
   function add_many($object)
   {
      $fk = $this->relation->foreign_key();
      $object->$fk->$ob;
   }
   
   function filter($clause)
   {
      $this->object_id = reset($clause);
      return parent::filter($clause);
   }
}