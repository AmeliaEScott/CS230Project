<?php

$app->group(
  '/dashboard/api',
  function() use ($app, $db) {

    $checkAuth = function($role = 1) {
      return function () use ($role) {
          $app = \Slim\Slim::getInstance();
          $user = $app->view->get('user');
          $data = array(
            'success' => false
          );

          if($user == false) {
            $data['error'] = 'You must be logged in to perform that action.';
          } else if ($user->role > $role) {
            $data['error'] = 'You have insufficient privileges to perform that action.';
          }

          if($user == false || $user->role > $role) {
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setBody(json_encode($data));
            $app->stop();
          }
      };
    };

    $app->post(
      '/commissioners',
      $checkAuth(2),
      function() use ($app, $db) {
        $user = $app->view->get('user');
        $app->response->headers->set('Content-Type', 'application/json');
        $ecUser = $db->getUser($app->request->post('user'));

        if (empty($ecUser)) {
            $data['message'] = 'User with ID "'.$app->request->post('user').'" doesn\'t exist.';
        } else if ($ecUser->isEC()) {
            $data['message'] = 'User "'.$ecUser->name.'" is already an election commissioner.';
        } else {
          $ecUser->setData('isEC', true);
            $data['success'] = $ecUser->save($db);
        }

        $app->response->setBody(json_encode($data));
      }
    )->setMiddleware($checkAuth(2));

    $app->get(
      '/sysinfo',
      $checkAuth(2),
      function() use ($app, $db) {
        $user = $app->view->get('user');

        $app->response->headers->set('Content-Type', 'application/json');
        $data = array(
          'success' => true,
          'user' => $user
        );
        $app->response->setBody(json_encode($data));
      }
    );

  }
);
