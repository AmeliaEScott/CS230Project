<?php

require_once 'lib/db.php';

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

$app->get(
    '/',
    function() use($app, $db) {
      $elections = array();
      $q = $db->prepare("SELECT * FROM elections");
      if($q->execute()) {
        while($row = $q->fetch(PDO::FETCH_OBJ)) {
          $elec = new Election($row);
            $elections[] = $elec;
        }
      }
      $app->render('index.html', array(
        'elections' => $elections
      ));
    }
)->name('homepage');

$app->get(
  '/campaigns(/)',
  function() use($app) {
    $app->render('campaigns.html');
  }
)->name('campaigns');

$app->get(
  '/campaign/:id(/)',
  function($id) use ($app, $db) {
    $q = $db->prepare('SELECT * FROM elections WHERE id = :id');
    $q->bindParam(':id', $id);
    if($q->execute()) {
      $row = $q->fetch(PDO::FETCH_OBJ);
      $elec = new Election($row);
      $elec->ec = $db->getUser($elec->ec);
      $app->render('campaign.html', array(
        'election' => $elec
      ));
    }
  }
)->name('election');

$app->post(
  '/campaign/:id/vote',
  $checkAuth(3),
  function($id) use ($app, $db) {
    $q = $db->prepare('SELECT * FROM elections WHERE id = :id');
    $q->bindParam(':id', $id);
    $user = $app->view->get('user');

    if($q->execute()) {
      $row = $q->fetch(PDO::FETCH_OBJ);

      if(!empty($row)) {
        $elec = new Election($row);
        $races = $elec->getRaces();
        $data = json_decode($app->request->getBody());
        foreach($data as $item) {
          foreach($item as $key => $value) {
            $k = explode(':', $key);
            $v = explode(':', $value);
            $raceID = intval($k[1], 10);
            $canID = intval($v[1], 10);

            if($k[0] != 'race' || $raceID > sizeof($races)-1 || $canID > sizeof($races[$raceID]->candidates)) {
              echo json_encode(array(
                'success' => false,
                'error' => 'There was an error submitting your vote. Please refresh and try again later.'
              ));
              return;
            }

            $vote = new Vote((object) array(
              'user' => $user->id,
              'election' => $elec->id,
              'race' => $raceID,
              'candidate' => $canID
            ));
            $vote->save($db);
          }
        }

        $app->response->headers->set('Content-Type', 'application/json');
        echo json_encode(array(
          'success' => true,
          'data' => $data,
          'vote' => $vote
        ));
      } else {
        echo json_encode(array(
          'success' => false,
          'error' => 'An election with that ID does not exist.'
        ));
      }
    }
  }
)->name('voteFor');

$app->get(
  '/results(/)',
  function() use($app) {
    $app->render('results.html');
  }
)->name('results');

$app->get(
  '/about(/)',
  function() use($app) {
    $app->render('about.html');
  }
)->name('about');

$app->get(
  '/dashboard(/)',
  function() use($app) {
    $user = $app->view->get('user');
    if ($user == false) {
      $app->flash('error', 'You must be logged in to access that page.');
      $app->redirect($app->urlFor('homepage'));
    } else {
      $app->render('dashboard.html');
    }
  }
)->name('dashboard');

$app->get(
  '/dashboard/create-ballot',
  function() use($app) {
    $user = $app->view->get('user');
    if (!isset($user)) {
      $app->flash('error', 'You must be logged in to access that page.');
      $app->redirect($app->urlFor('homepage'));
    } else if ($user->isEC()) {
      $app->render('create-ballot.html');
    } else {
      $app->flash('error', 'You do not have permission to access that page.');
      $app->redirect($app->urlFor('homepage'));
    }
  }
)->name('create.ballot');

$app->post(
  '/dashboard/create-ballot',
  function() use ($app, $db) {
    $user = $app->view->get('user');
    $data = array();
    $app->response->headers->set('Content-Type', 'application/json');
    if(!$user->isEC()) {
      $data['success'] = false;
      $data['message'] = 'You do not have permission to perform that action.';
    } else {
      $postData = json_decode($app->request->getBody());
      $election = new Election($postData);
      $election->save($db);
      if(isset($election->id)) {
        $data['success'] = true;
        $data['id'] = $election->id;
        $data['message'] = 'Successfully created ballot "'.$election->name.'"';
      }
    }
    $app->response->setBody(json_encode($data));
  }
);
