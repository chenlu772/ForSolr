<?php

require('config.php');
include('BaseModel.php');

$cl = new Solarium\Client($config);
$up = $cl->createUpdate();
$cc = $up->createDocument();
$param = $_GET['d_id'];
$c = new BaseModel($config);
//var_dump($c->ping());
//var_dump($c->update());
//var_dump($c->deleteById('e3e2c139-6118-4074-9f4e-08d9efc2326c'));
//var_dump($c->deleteByQuery($param.':*'));