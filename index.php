<?php

require 'vendor/Slim/Slim.php';
require 'lib/middleware.php';
require 'lib/db.php';

\Slim\Slim::registerAutoloader();

$config = include('lib/config.php');
$db = new DB($config);

$app = new \Slim\Slim(array(
  'debug' => $config['debug'],
  'view' => new \Slim\Views\Twig()
));

$app->add(new \UserMiddleware($config));
$app->add(new Slim\Middleware\SessionCookie());

$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

require 'lib/routes/user.php';
require 'lib/routes/api.php';

$app->run();
