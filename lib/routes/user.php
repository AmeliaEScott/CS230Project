<?php

require_once 'lib/db.php';

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
  function($id) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(array(
      'success' => true
    ));
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
