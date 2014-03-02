<?php

require_once 'vendor/autoload.php';
require_once 'generator.php';

$cmd = new Commando\Command();

$cmd->option()->require()->expectsFile()->describedAs('XML File path');

$cmd->option('c')->aka('count')->describedAs('Generation iteration count');

$cmd->option('p')->aka('print-only')->boolean()->describedAs('Only print result');

$cmd->option('d')->aka('define')->describedAs('A define to override file value (key1=value1;key2=value2)');

if($cmd['p'])
  echo "/!\ Runing on print mode no data altered\n";

$gen = new Generator($cmd[0], $cmd['p']);

if($cmd['d'])
{
  foreach(explode(';', $cmd['d']) as $assign)
  {
    list($key, $value) = explode('=', $assign);
    $gen->setVar($key, $value);
  }
}

$gen->process($cmd['c'] ? $cmd['c'] : 1);
