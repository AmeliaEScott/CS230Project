<?php

require 'lib/user.php';
require 'lib/election.php';
require 'lib/vote.php';

class DB {

  public $db;

  public function __construct($config = array()) {
    $this->db = $this->getDB($config);
  }

  public function getDB($config = array()) {
    $conn = new PDO($config['mysql_conn'], $config['mysql_user'], $config['mysql_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
  }

  public function getVotes($elecID, $userID = -1) {
    try {
      if ($userID > 0) {
        $q = $this->prepare("SELECT * FROM votes WHERE elecID = :id and userid = :user");
        $q->bindParam(':user', $userID);
      } else {
        $q = $this->prepare("SELECT * FROM votes WHERE elecID = :id");
      }
      $q->bindParam(':id', $elecID);
      $q->execute();

      $votes = array();

      while($row = $q->fetch(PDO::FETCH_OBJ)) {
        $votes[] = new Vote($row);
      }

      return $votes;
    } catch (PDOException $e) {
      if ($e->getCode() == '02000') {
        return null;
      } else {
        error_log($e->getMessage());
        return false;
      }
    }
  }

  public function getElection($elecID) {
    try {
      $q = $this->prepare("SELECT * FROM elections WHERE id = :id");
      $q->bindParam(':id', $elecID);
      $q->execute();

      $data = $q->fetch(PDO::FETCH_OBJ);
      return new Election($data);
    } catch (PDOException $e) {
      if ($e->getCode() == '02000') {
        return null;
      } else {
        error_log($e->getMessage());
        return false;
      }
    }
  }

  public function getUser($userID) {
    try {
      $q = $this->prepare("SELECT * FROM users WHERE userid = :id");
      $q->bindParam(':id', $userID);
      $q->execute();

      $data = $q->fetch(PDO::FETCH_OBJ);
      if(isset($data->data)) {
        $data->data = unserialize($data->data);
      }
      return new User($data);
    } catch (PDOException $e) {
      if ($e->getCode() == '02000') {
        return null;
      } else {
        error_log($e->getMessage());
        return false;
      }
    }
  }

  public function prepare($q) {
    return $this->db->prepare($q);
  }
}
