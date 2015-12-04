<?php

class Election {

  public $id;
  public $name;
  public $description;
  public $ec;
  public $time;
  public $approved;
  public $data;

  public function __construct($data) {
    if (isset($data)) {
      if (isset($data->ballotName)) {
        $this->name = $data->ballotName;
        $this->time = $data->ballotRange;
        $this->description = $data->ballotDesc;
        $this->ec = (int)$data->ballotEC;
        $this->data = $data->ballotRaces;
      } else if (isset($data->name)) {
        $this->id = (int)$data->id;
        $this->name = $data->name;
        $this->time = $data->time;
        $this->description = $data->description;
        $this->ec = (int)$data->ec;
        $this->data = json_decode($data->data);
      }
      $this->approved = isset($data->approved) ? $data->approved : false;
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

  public function setStatus($status, $db) {
    $q = $db->prepare("UPDATE elections SET approved = :status WHERE id = :id");
    $q->bindParam(':status', $status);
    $q->bindParam(':id', $this->id);
    $q->execute();
    return ($q->rowCount() == 1);
  }

  public function save($db) {
    if ($this->id) {
      $q = $db->prepare("UPDATE elections SET data = :data WHERE id = :id");
      $q->bindParam(':data', serialize($this->data), PDO::PARAM_LOB);
      $q->bindParam(':id', $this->id);
      $q->execute();
      return ($q->rowCount() == 1);
    } else {
      $q = $db->prepare(
        "INSERT INTO elections (name, description, ec, time, data)
        VALUES (:name, :desc, :ec, :time, :data)"
      );
      $q->bindParam(':name', $this->name);
      $q->bindParam(':desc', $this->description);
      $q->bindParam(':ec', $this->ec);
      $q->bindParam(':time', $this->time);
      $q->bindParam(':data', json_encode($this->data));
      $q->execute();

      if ($q->rowCount() == 1) {
        $this->id = (int)$db->db->lastInsertId();
      }
    }
  }

  public function getRaces() {
    $races = array();
    foreach($this->data as $item) {
      if(array_key_exists('raceName', $item)) {
        $races[] = $item;
      }
    }
    return empty($races) ? null : $races;
  }

  public function isActive() {
    $format = 'm/d/Y h:i A';
    $currDate = date($format);
    $dateRange = array();
    $dateInput = explode(' - ', $this->time);
    foreach($dateInput as &$d) {
      $dateRange[] = date_format(date_create($d), $format);
    }
    return ($currDate > $dateRange[0] && $currDate < $dateRange[1]);
  }
}
