<?php

class Vote {

  public $id;
  public $userid;
  public $elecID;
  public $raceID;
  public $candidate;
  public $time;
  public $data;

  public function __construct($data) {
    if($data instanceof Vote) {
      $this->id = $data->id;
      $this->userid = $data->userid;
      $this->elecID = $data->elecID;
      $this->raceID = $data->raceID;
      $this->candidate = $data->candidate;
      $this->time = $data->time;
      $this->data = $data->data;
    } else {
      $this->userid = $data->user;
      $this->elecID = $data->election;
      $this->raceID = $data->race;
      $this->candidate = $data->candidate;
    }
  }

  public function getData($key) {
    if(isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      return null;
    }
  }

  public function setData($key, $value) {
    $this->data[$key] = $value;
  }

  public function save($db) {
    if ($this->id) {
      $q = $db->prepare("UPDATE votes SET data = :data WHERE id = :id");
      $q->bindParam(':data', serialize($this->data), PDO::PARAM_LOB);
      $q->bindParam(':id', $this->id);
      $q->execute();
      return ($q->rowCount() == 1);
    } else {
      $q = $db->prepare(
        "INSERT INTO votes (userid, elecID, raceID, candidate, data)
        VALUES (:user, :elec, :race, :can, :data)"
      );
      $q->bindParam(':user', $this->userid);
      $q->bindParam(':elec', $this->elecID);
      $q->bindParam(':race', $this->raceID);
      $q->bindParam(':can', $this->candidate);
      $q->bindParam(':data', json_encode($this->data));
      $q->execute();

      if ($q->rowCount() == 1) {
        $this->id = (int)$db->db->lastInsertId();
      }
    }
  }
}
