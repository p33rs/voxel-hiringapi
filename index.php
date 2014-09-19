<?php

  namespace api;

  // define constants. init autoloader.
  require_once('_setup.php');
  
  // input is provided through stdin.
  //$filename = trim(fgets(STDIN));
   $filename = 'test';
  // was a valid file given? if so, read it
  if (!file_exists($filename)) {
    throw new \Exception('The input file could not be found.');
  }
  $file = fopen($filename, 'r');
  $input = fread($file, (filesize($filename)?:1));
  $input = explode("\n", $input);
  fclose($file);
  
  // detect API version based on the auth line
  $client = preg_match('/^auth /', reset($input)) ? new client\HiringV2() : new client\HiringV1();
  
  // attempt to run each line through the client. 
  $results = array();
  foreach ($input as $line) {
    // get an array. trim the elements.
    $params = explode(' ', $line);
    $params = array_map(function($value) {
      return trim($value);
    }, $params);
    // pull the action off the end
    $action = trim(array_shift($params));
    // call the client method, with the appropriate number of args.
    // we're assuming no method has >3 args, which is currently true.
    $result = false;
    switch (count($params)) {
      case 0: $result = $client->$action(); break;
      case 1: $result = $client->$action($params[0]); break;
      case 2: $result = $client->$action($params[0], $params[1]); break;
      case 3: $result = $client->$action($params[0], $params[1], $params[2]); break;
    }
    // results are pushed to an array.
    $results[] = $result;
    // if an error occured, halt.
    if (substr($result, 0, '5') == 'error') break;
  }
  // display the results
  echo implode("\n", $results);