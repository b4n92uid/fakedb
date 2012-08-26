<?php

require_once('Generator.class.php');

$filename = null;
$count = 1;

if(isset($argv[1]))
    $filename = $argv[1];

if(isset($argv[2]))
    $count = $argv[2];

$gen = new Generator($filename, true);

if(count($argv) > 3) for($i=3; $i<count($argv); $i++)
{
    list($key, $value) = explode('=', $argv[$i]);
    $gen->setVar($key, $value);
}

$gen->process($count);
