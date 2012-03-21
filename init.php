<?php

define('CLI', PHP_SAPI === 'cli');

define('START_TIME', microtime(false));

define('SYS_PATH', dirname(__FILE__) . '/');
define('APP_PATH', getcwd() . '/mvc/');

$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', getcwd());
define('URL_PATH', CLI? '/' : ('/' . str_replace($_SERVER['REQUEST_URI'], '', $path) . '/'));

require SYS_PATH . 'functions.php';
require SYS_PATH . 'hook.php';
require SYS_PATH . 'base.php';
require SYS_PATH . 'router.php';
require SYS_PATH . 'model.php';
require SYS_PATH . 'relation.php';
require SYS_PATH . 'controller.php';
foreach(glob(SYS_PATH . 'relations/*.php') as $file)
	require $file;

require SYS_PATH . 'inflector.php';
require SYS_PATH . 'db.php';

$db['server'] = 'localhost';
$db['handler'] = 'mysqli';

if (DEFINED('HOOKS') && HOOKS == true)
   Hook::load_hooks();