<?php

Model::requires('course_type');

class Super_course_type extends Course_type
{
	function __construct($data = NULL)
	{
		$this->has_many('course_types');
		parent::__construct($data);
	}
}