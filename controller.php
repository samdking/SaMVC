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
   
   function hooks($uri)
   {
      include self::find_file('hooks/sample_hook');
      $hook = new Sample_hook;
      $uri = $hook->edit($uri);
   }
   
   function run()
   {
      $uri = str_replace(URL_PATH, '', $_SERVER['REQUEST_URI']);
      $this->hooks($uri);
      $parts = explode('/', $uri);
      $page = array_shift($parts);
      $method = $page? str_replace('-', '_', $page) : 'index';
      $this->render('header');
      if (method_exists($this, $method)) {
         try {
            call_user_func_array(array($this, $method), (array)$parts);
            $this->render($this->view_file? $this->view_file : $method);
         } catch (Exception $e) {
            $this->error($method, $e);
         }
      } else {
         $this->error($method);
      }
      $count = count(DB::init()->queries());
      $this->render('footer', array(
         'render_time' => round((microtime(false) - START_TIME), 5),
         'query_count' => $count . ' ' . ($count == 1? 'query' : 'queries')
      ));
   }
   
   function error($method, Exception $e = NULL)
   {
      $this->render('error', array('method'=>$method, 'exception'=>$e));
   }
   
   function render($name, $vars = array())
   {
      extract($vars);
      extract($this->view_vars);
      try {
         include self::find_file('views/' . $name);
      } catch (Exception $e) {
         
      }
   }
   
   function __set($property, $value)
   {
      $this->view_vars[$property] = $value;
   }
   
}