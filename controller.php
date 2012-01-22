<?php

class Controller extends Base
{
   protected $view_file = NULL;
   protected $view_vars = array();
   
   static function load($name)
   {
      $file = 'controllers/' . $name;
      include self::find_file($file);
      $class_name = $name . 'Controller';
      return new $class_name;
   }

   function name()
   {
      return str_replace('controller', '', strtolower(get_class($this)));
   }
   
   function run()
   {
      $uri = str_replace(URL_PATH, '', $_SERVER['REQUEST_URI']);
      $uri = Hook::apply('modify_uri', $uri);
      $parts = explode('/', $uri);
      $page = array_shift($parts);
      $method = $page? str_replace('-', '_', $page) : 'index';
      try {
         $this->render_page($method, $parts);
      } catch (Exception $e) {
         Controller::load('error')->render_page('index', array($method, $e));   
      }
   }
   
   function render_page($method, $parts)
   {
      $header_file = 'header';
      $footer_file = 'footer';
      $view_file = $this->name() . '/' . $method;
      if (!method_exists($this, $method) OR !public_method($this, $method))
         throw new Exception('No ' . $method . ' method exists in ' . get_class($this));
      call_user_func_array(array($this, $method), (array)$parts);
      extract($this->view_vars);
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