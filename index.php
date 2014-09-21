<?php
namespace p33rs\HiringApi;
require 'vendor/autoload.php';

$endpoint = 'http://hiringapi.dev.voxel.net/';
$runner = new Runner();
echo $runner(trim(fgets(STDIN)) ? : 'test', $endpoint);