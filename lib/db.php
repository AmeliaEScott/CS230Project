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
      $stmt;
      if ($elecID > 0) {
        $stmt = "SELECT * FROM votes WHERE elecID = :id";
        if ($userID > 0) {
          $stmt .= " and userid = :user";
        }
      } else if ($elecID == -1) {
        $stmt = "SELECT * FROM votes".(($userID > 0) ? " WHERE userid = :user" : "");
      }

      $q = $this->prepare($stmt);
      if ($userID > 0) {
        $q->bindParam(':user', $userID);
      }
      if ($elecID > 0) {
        $q->bindParam(':id', $elecID);
      }
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
        error_log('getVotes('.$elecID.', '.$userID.'): '.$e->getMessage());
        return false;
      }
    }
  }

  public function getElection($elecID = -1) {
    try {
      if ($elecID == -1) {
        $q = $this->prepare("SELECT * FROM elections");
      } else {
        $q = $this->prepare("SELECT * FROM elections WHERE id = :id");
        $q->bindParam(':id', $elecID);
      }

      $q->execute();

      if ($elecID == -1) {
        $elections = array();
        while($row = $q->fetch(PDO::FETCH_OBJ)) {
          $elec = new Election($row);
          if (isset($elec->ec)) {
            $elec->ec = $this->getUser($elec->ec);
          }
          $elections[] = $elec;
        }
        return $elections;
      } else {
        $data = $q->fetch(PDO::FETCH_OBJ);
        $elec = new Election($data);
        if (isset($elec->ec)) {
          $elec->ec = $this->getUser($elec->ec);
        }
        return $elec;
      }

    } catch (PDOException $e) {
      if ($e->getCode() == '02000') {
        return null;
      } else {
        error_log($e->getMessage());
        return false;
      }
    }
  }

  public function getUser($userID = -1) {
    try {
      if ($userID == -1) {
        $q = $this->prepare("SELECT * FROM users");
      } else {
        $q = $this->prepare("SELECT * FROM users WHERE userid = :id");
        $q->bindParam(':id', $userID);
      }

      $q->execute();

      if ($userID == -1) {
        $users = array();
        while($row = $q->fetch(PDO::FETCH_OBJ)) {
          if (isset($row->data)) {
            $row->data = unserialize($row->data);
          }
          $users[] = new User($row);
        }
        return $users;
      } else {
        $data = $q->fetch(PDO::FETCH_OBJ);
        if(isset($data->data)) {
          $data->data = unserialize($data->data);
        }
        return new User($data);
      }
    } catch (PDOException $e) {
      if ($e->getCode() == '02000') {
        return null;
      } else {
        error_log($e->getMessage());
        return false;
      }
    }
  }

  public function count($type) {
    $q = $this->prepare("SELECT count(*) FROM ".$type);
    $q->execute();
    return intval($q->fetchColumn());
  }

  public function prepare($q) {
    return $this->db->prepare($q);
  }
}
