<?php

class Course extends Model
{
	function __construct($data = NULL)
	{
		$this->belongs_to('school');
		$this->belongs_to('course_type');
		$this->belongs_to('language');
		parent::__construct($data);
	}
}