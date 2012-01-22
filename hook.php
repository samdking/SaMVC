<?php

class Hook {

   protected static $actions = array();
	protected static $filters = array();

	static function apply($method_name)
	{
	   $args = array_slice(func_get_args(), 1);
      $value = $args[0];
		if (array_key_exists($method_name, self::$filters))
			foreach(self::$filters[$method_name] as $filter)
		      $value = $args[0] = call_user_func_array($filter, $args);
			return $value;
	}

	static function register($method_name, $obj)
   {
	   $call = is_array($obj)? $obj : array($obj, $method_name);
		self::$filters[$method_name][] = $call;
   }

	static function listen($method_name, $obj)
	{
	   $call = is_array($obj)? $obj : array($obj, $method_name);
		self::$actions[$method_name][] = $call;
   }

	static function trigger($method_name)
	{
	   if (!array_key_exists($method_name, self::$actions)) return;
		$args = array_slice(func_get_args(), 1);
		foreach(self::$actions[$method_name] as $action)
		   call_user_func_array($action, $args);
   }

	static function load_hook($hook)
	{
	   if (substr(basename($hook), 0, 1) == '_')
	      return;
		include ($hook);
		$class = basename($hook, '.php');
		$hooks = new $class;
	}

	static function load_hooks()
	{
	   foreach(glob(APP_PATH . 'hooks/*_hook.php') as $hook)
		   self::load_hook($hook);
   }

}