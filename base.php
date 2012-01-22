<?php

class Base {

	function find_file($filename, $required = true)
	{		
		//$file_locations = Hooks::apply('file_locations', array($filename));
		$file_locations = array($filename);
		
		// check system folder first
		
		foreach($file_locations as $file) {
			$file .= '.php';
			if (file_exists(APP_PATH . $file))
				return APP_PATH . $file;
		}
		
		// then check app folder
		
		foreach($file_locations as $file) {
			$file .= '.php';
			if (file_exists(SYS_PATH . $file))
				return SYS_PATH . $file;
		}
		
		if ($required)
			throw new Exception("File ($file) does not exist");
		return false;
	}

}