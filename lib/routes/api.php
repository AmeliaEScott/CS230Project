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

$app->delete(
  '/campaign/:id/purge(/)',
  $checkAuth(1),
  function($id) use ($app, $db) {
    $user = $app->view->get('user');
    $elec = $db->getElection($id);
    $app->response->headers->set('Content-Type', 'application/json');

    if (empty($elec)) {
      $data['message'] = 'Election with ID "'.$id.'" doesn\'t exist.';
    } else if ($user->isSuperAdmin()) {
      $q = $db->prepare("DELETE FROM elections WHERE id = :id");
      $q->bindParam(':id', $id);
      if ($q->execute()) {
        $q = $db->prepare("DELETE FROM votes WHERE elecID = :id");
        $q->bindParam(':id', $id);
        $data['success'] = $q->execute();
        if (!$data['success']) {
          $data['message'] = $db->db->errorInfo();
        }
      } else {
        $data['message'] = $db->db->errorInfo();
      }
    }

    $app->response->setBody(json_encode($data));
  }
)->name('purgeElection');

$app->post(
  '/campaign/:id/results',
  $checkAuth(2),
  function($id) use ($app, $db) {
    $user = $app->view->get('user');
    $elec = $db->getElection($id);
    $app->response->headers->set('Content-Type', 'application/json');

    if (empty($elec)) {
      $data['message'] = 'Election with ID "'.$id.'" doesn\'t exist.';
      $data['success'] = false;
    } else {
      if ($app->request->post('certify')) {
        $elec->setData('certified', $app->request->post('certify'));
      }
      $data['success'] = $elec->save($db);
    }

    $app->response->setBody(json_encode($data));
  }
)->name('postResults');

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
        } else if ($app->request->post('elec')) {
          $elec = $db->getElection($app->request->post('elec'));
          if (empty($elec)) {
            $data['success'] = false;
            $data['message'] = 'Election with ID "'.$app->request->post('elec').'" doesn\'t exist.';
          } else {
            $elec->ec = $ecUser->id;
            if (!$ecUser->isEC()) {
              $ecUser->setData('isEC', true);
              $data['success'] = ($elec->save($db) && $ecUser->save($db));
            } else {
              $data['success'] = $elec->save($db);
            }
          }
        } else {
          if ($ecUser->isEC()) {
            $data['message'] = 'User "'.$ecUser->name.'" is already an election commissioner.';
          } else {
            $ecUser->setData('isEC', true);
            $data['success'] = $ecUser->save($db);
          }
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

    $app->put(
      '/users/:id(/)',
      $checkAuth(1),
      function($id) use ($app, $db) {
        $app->response->headers->set('Content-Type', 'application/json');
        $editUser = $db->getUser($id);
        $data = array();

        if (empty($editUser)) {
          $data['message'] = 'User with ID "'.$id.'" doesn\'t exist.';
          $data['success'] = false;
        } else {
          $usrData = $app->request->put('data');
          if ($usrData != null && (is_object($usrData) || is_array($usrData))) {
            $editUser->data = $usrData;
            $data['success'] = $editUser->save($db);
          }
        }

        $app->response->setBody(json_encode($data));
      }
    )->setMiddleware($checkAuth(1));

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

    $app->post(
      '/:type',
      $checkAuth(2),
      function($type) use ($app, $db) {
        $user = $app->view->get('user');
        $app->response->headers->set('Content-Type', 'application/json');

        $userID = $app->request->post('userID');
        $elecID = $app->request->post('elecID');

        if (isset($elecID) && !isset($userID)) {
          $data['message'] = 'Malformed query.';
          $data['success'] = false;
        } else {
          $banUser = $db->getUser($userID);
          $elec = $db->getElection($elecID);

          if (empty($banUser)) {
            $data['message'] = 'User with ID "'.$userID.'" doesn\'t exist.';
            $data['success'] = false;
          } else if (isset($elecID)){
            if(empty($elec)) {
              $data['message'] = 'Election with ID "'.$elecID.'" doesn\'t exist.';
              $data['success'] = false;
            } else {
              $banned = (array)$elec->getData('bannedUsers');
              if ($type == 'allow') {
                if(($key = array_search($banUser->id, $banned)) !== false) {
                  unset($banned[$key]);
                } else {
                  $data['message'] = $banUser->name.' is not banned from election "'.$elec->name.'".';
                  $data['success'] = false;
                }
              } else {
                $banned[] = $banUser->id;
              }
              $elec->setData('bannedUsers', $banned);
              $data['success'] = $elec->save($db);
              if (!$data['success']) {
                $data['message'] = $db->db->errorInfo();
              }
            }
          } else {
            $banUser->setData('banned', $type == 'disqualify');
            $data['success'] = $banUser->save($db);
            if (!$data['success']) {
              $data['message'] = $db->db->errorInfo();
            }
          }
        }

        $app->response->setBody(json_encode($data));
      }
    )->conditions(array('route' => '(allow|disqualify)'))->setMiddleware($checkAuth(2));

    $app->get(
      '/sysinfo',
      $checkAuth(2),
      function() use ($app, $db) {
        $user = $app->view->get('user');
        $app->response->headers->set('Content-Type', 'application/json');
        foreach(array('users','elections','votes') as $type) {
          $data['num'.$type] = $db->count($type);
        }
        $app->response->setBody(json_encode($data));
      }
    );

  }
);
