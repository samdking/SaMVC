<?php

class Router extends Base
{
	
	static function route_url()
	{
		$cl_args = isset($GLOBALS['argv'])? $argv : array();
		$uri = CLI? implode('/', $argv) : str_replace(URL_PATH, '', $_SERVER['REQUEST_URI']);
		$uri = Hook::apply('modify_uri', $uri);
		$parts = array_filter(explode('/', $uri));
		$controller_name = empty($parts)? DEFAULT_CONTROLLER : $parts[0];
		try {
			Controller::get($controller_name)->route_method($parts);
		} catch (Exception $e) {
			Controller::get('error')->render_page('page_not_found', array($e));   
		}
	}

}