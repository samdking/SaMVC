<?php

class Controller extends Base
{
   protected $view_file = NULL;
   protected $view_vars = array();
   
   static function select()
   {
      $uri = str_replace(URL_PATH, '', $_SERVER['REQUEST_URI']);
      $uri = Hook::apply('modify_uri', $uri);
      $parts = array_filter(explode('/', $uri));
      $controller_name = empty($parts)? DEFAULT_CONTROLLER : $parts[0];
      try {
         Controller::route_controller($controller_name)->route_method($parts);
      } catch (Exception $e) {
         Controller::route_controller('error')->render_page('index', array($e));   
      }      
   }
   
   static function route_controller($name)
   {   
      $class_name = strtoupper($name) . 'Controller';
      if (!class_exists($class_name)) {
         try {
            include self::find_file('controllers/' . $name);
         } catch (Exception $e) {
            return self::route_controller(DEFAULT_CONTROLLER);
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
      $header_file = 'header';
      $footer_file = 'footer';
      $this->view_file = $this->name() . '/' . $method;
      call_user_func_array(array($this, $method), (array)$parts);
      extract($this->view_vars);
      $this->header_file = self::find_file('views/' . $header_file);
      $this->content_file = self::find_file('views/' . $this->view_file);
      $this->footer_file = self::find_file('views/' . $footer_file);
      $this->render_output();
   }
   
   function render_output()
   {      
      $page_title = '';
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