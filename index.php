<?php

require 'vendor/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
  'debug' => true,
  'view' => new \Slim\Views\Twig()
));

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

require 'routes/user.php';

$app->run();
