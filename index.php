<?php

require('config.php');
include('BaseModel.php');

$cl = new Solarium\Client($config);
$up = $cl->createUpdate();
$cc = $up->createDocument();

$c = new BaseModel($config);
//var_dump($c->getVersion());
//var_dump($c->updateAll());
//var_dump($c->updateById(1));
//var_dump($c->deleteById('0e11ce7a-9b36-4dc3-94c5-071eb7993d0d'));
//var_dump($c->deleteByQuery('id:*'));
var_dump($c->query());