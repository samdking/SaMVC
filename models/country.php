<?php

class Country extends Model
{
	protected $db_table = 'destinations';
	public $filter = array('parent_id'=>0);
	
	function __construct($data = NULL)
	{
		$this->has_many('language_destinations', array(
			'foreign_key' => 'destination_id'
		));
		$this->has_many('languages', array(
			'through'=>'language_destinations'
		));
		$this->has_many('cities', array(
			'foreign_key' => 'parent_id'
		));
		unset($data['parent_id']);
		parent::__construct($data);
	}
	
	function course_types()
	{
		return Model::get('course_type')->filter(array('courses.school.city.country.id'=>$this->id))->group('id');
	}
}