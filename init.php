<?php

define('SYS_PATH', dirname(__FILE__) . '/');
define('APP_PATH', getcwd() . '/mvc/');

$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', getcwd());
define('URL_PATH', '/' . str_replace($_SERVER['REQUEST_URI'], '', $path) . '/');

require SYS_PATH . 'base.php';
require SYS_PATH . 'model.php';
require SYS_PATH . 'relation.php';
require SYS_PATH . 'controller.php';
foreach(glob(SYS_PATH . 'relations/*') as $file)
	require $file;

require SYS_PATH . 'inflector.php';
require SYS_PATH . 'db.php';

$db['server'] = 'localhost';
$db['user'] = 'root';
$db['handler'] = 'mysqli';