<?php
require(__DIR__.'./library/Solarium/Autoloader.php');
require(__DIR__.'./library/Symfony/Component/EventDispatcher/EventDispatcherInterface.php');
require(__DIR__.'./library/Symfony/Component/EventDispatcher/EventDispatcher.php');
require(__DIR__.'./library/Symfony/Component/EventDispatcher/Event.php');
require(__DIR__.'./class/Model.php');
$config = array(
    'endpoint' => array(
        'localhost' => array(
            'host' => '127.0.0.1',
            'port' => 8080,
            'path' => '/solr/mynode',
        )
    )
);

Solarium\Autoloader::register();