<?php

class City extends Model
{
	protected $db_table = 'destinations';
	public $filter = array('parent_id >' => 0);
	
	function __construct($data = NULL)
	{
		$this->has_many('schools', array('foreign_key'=>'destination_id'));
		$this->has_many('language_destinations');
		$this->has_many('languages', array(
			'through' => 'language_destinations'
		));
		$this->belongs_to('country', array(
			'foreign_key' => 'parent_id'
		));
		$this->belongs_to('parent', array(
			'model' => 'city',
			'foreign_key' => 'parent_id',
			'required' => false
		));
		$this->has_many('sub_cities', array(
			'model' => 'city',
			'required' => false
		));
		parent::__construct($data);
	}
	
}