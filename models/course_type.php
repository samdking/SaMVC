<?php

class Course_type extends Model
{
	function __construct($data = NULL)
	{
		$this->has_many('courses');
		$this->belongs_to('super_course_type');
		parent::__construct($data);
	}
}