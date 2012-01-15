<?php

class Language extends Model
{
	function __construct($data = NULL)
	{
		$this->has_many('courses');
		$this->has_many('language_destinations');
		$this->has_many('cities', array(
			'through'=>'language_destinations',
			'foreign_key' => 'destination_id'
		));
		$this->has_many('countries', array(
			'through'=>'language_destinations',
			'foreign_key' => 'destination_id'
		));
		parent::__construct($data);
	}
}