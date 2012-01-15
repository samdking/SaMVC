<?php

class Language_destination extends Model
{
	protected $db_table = 'languages_to_destinations';
	function __construct($data = NULL)
	{
		$this->has_many('languages');
		$this->has_many('countries', array(
			'foreign_key' => 'destination_id'
		));
		$this->has_many('cities', array(
			'foreign_key' => 'destination_id'
		));
		parent::__construct($data);
	}
}