<?php

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

$app->delete(
  '/campaign/:id(/)',
  $checkAuth(2),
  function($id) use ($app, $db) {
    $user = $app->view->get('user');
    $app->response->headers->set('Content-Type', 'application/json');
    $elec = $db->getElection($id);

    if (empty($elec)) {
      $data['message'] = 'Election with ID "'.$id.'" doesn\'t exist.';
    } else {
      $data['success'] = $elec->setStatus(0, $db);
    }

    $app->response->setBody(json_encode($data));
  }
);

$app->group(
  '/dashboard/api',
  function() use ($app, $db, $checkAuth) {

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

    $app->delete(
      '/commissioners/:id(/)',
      $checkAuth(2),
      function($id) use ($app, $db) {
        $app->response->headers->set('Content-Type', 'application/json');
        $ecUser = $db->getUser($id);

        if (empty($ecUser)) {
          $data['message'] = 'User with ID "'.$id.'" doesn\'t exist.';
        } else if (!$ecUser->isEC()) {
          $data['message'] = 'User "'.$ecUser->name.'" is not an election commissioner.';
        } else {
          $ecUser->setData('isEC', false);
          $data['success'] = $ecUser->save($db);
        }

        $app->response->setBody(json_encode($data));
      }
    )->setMiddleware($checkAuth(2));

    $app->post(
      '/approve',
      $checkAuth(2),
      function() use ($app, $db) {
        $user = $app->view->get('user');
        $app->response->headers->set('Content-Type', 'application/json');
        $elec = $db->getElection($app->request->post('elecID'));

        if (empty($elec)) {
          $data['message'] = 'Election with ID "'.$app->request->post('elecID').'" doesn\'t exist.';
        } else if ($elec->approved == true) {
          $data['message'] = 'Election with ID "'.$app->request->post('elecID').'" is already approved.';
        } else {
          $data['success'] = $elec->setStatus(1, $db);
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
