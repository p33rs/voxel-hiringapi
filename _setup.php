<?php

  // Tell the autoloader where to find files
  define('API_REMOTE_HOST', 'hiringapi.dev.voxel.net');
  define('API_REMOTE_PATH', '/');
  define('API_INCLUDES_PATH', __DIR__.'/include');
  
  /**
   * Thrown exceptions are returned with status 000.
   * @todo Find a better way to return this information?
   */
  set_exception_handler(function($e) {
    echo 'error 000 '.$e->getMessage();
  });
  
  /**
   * Autoload classes. Assumes this application is in namespace 'api'.
   * Classes are in the "include" directory. Sub-namespaces are
   *   in subdirectories.
   * @param string $class The requested class
   */
  function __autoload($class) {
    $path = API_INCLUDES_PATH;
    $path .= str_replace('\\', '/', preg_replace('/^api/', '', $class));
    $path .= '.php';
    if (!file_exists($path)) throw new Exception('A required file could not be located. ('.$path.')');
    require_once($path);
    if (!class_exists($class)) throw new Exception('A required class could not be loaded. ('.$class.')');
  }
  
