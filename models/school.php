<?php

class School extends Model
{
	function __construct($data = NULL)
	{
		$this->has_many('courses');
		$this->belongs_to('city', array('foreign_key'=>'destination_id'));
		parent::__construct($data);
	}
}