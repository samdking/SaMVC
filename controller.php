<?php

class Controller extends Base
{
   protected $view_vars = array(
   	'no_view_file' => false,
		'page_title' => '',
		'header_file' => 'header',
		'footer_file' => 'footer'
   );

   static function get($name)
   {   
      $class_name = ucfirst($name) . 'Controller';
      if (!class_exists($class_name)) {
         try {
            include self::find_file('controllers/' . $name); // look for a controller that matches
         } catch (Exception $e) {
            return Controller::get(DEFAULT_CONTROLLER); // if not, serve the default
         }
      }
      return new $class_name;
   }

   function name()
   {
      return str_replace('controller', '', strtolower(get_class($this)));
   }
   
   function route_method($parts)
   {   
      if (reset($parts) == $this->name())
         array_shift($parts);
      $page = reset($parts);
      $method = $page? str_replace('-', '_', $page) : 'index';
      if (!method_exists($this, $method) OR !public_method($this, $method)) {
         if (!method_exists($this, 'catchall')) {
            throw new Exception('No ' . $method . ' method exists in ' . get_class($this));
         } else {
            array_unshift($parts, 'catchall');
            $method = 'catchall';
         }
      }
      array_shift($parts);
      $this->render_page($method, $parts);
   }
   
   function render_page($method, $parts)
   {
		call_user_func_array(array($this, $method), (array)$parts);
		$view_file = $this->name() . '/' . $method;
      extract($this->view_vars);
		if ($no_view_file) return;
		$this->header_file = self::find_file('views/' . $header_file);
      $this->content_file = self::find_file('views/' . $view_file);
      $this->footer_file = self::find_file('views/' . $footer_file);
      $this->render_output();
   }
   
   function render_output()
   {
      extract($this->view_vars);
      include $header_file;
      include $content_file;
      $count = count(DB::init()->queries());
      $render_time = round((microtime(false) - START_TIME), 5);
      $query_count = $count . ' ' . ($count == 1? 'query' : 'queries');
      include $footer_file;
   }
   
   function __set($property, $value)
   {
      $this->view_vars[$property] = $value;
   }
   
}