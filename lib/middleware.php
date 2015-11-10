<?php

require 'vendor/Slim/Middleware.php';

class UserMiddleware extends \Slim\Middleware {

  public function __construct($config = array()) {
    $this->config = $config;
  }

  public function call() {

    $db = new DB($this->config);
    $data = array(
      'user' => null
    );

    $app = $this->app;

    if($app->getCookie('sessid')) {
      $data['user'] = $db->getUser($app->getCookie('sessid'));

      if ($data['user'] == false) {
        $data['error'] = 'The user supplied does not exist. Please try again.';
      } else if ($data['user'] == null) {
        $data['error'] = 'An unknown error occurred. Please try again.';
      }
    }

    $app->view->appendData($data);
    $this->next->call();
  }
}
