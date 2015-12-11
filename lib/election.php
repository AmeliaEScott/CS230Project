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
      } else if (isset($data->name)) {
        $this->name = $data->name;
      }
      if (isset($data->id) && is_numeric($data->id)) {
        $this->id = (int)$data->id;
      }
      if (isset($data->ballotRange)) {
        $this->time = $data->ballotRange;
      } else if (isset($data->time)) {
        $this->time = $data->time;
      }
      if (isset($data->ballotDesc)) {
        $this->description = $data->ballotDesc;
      } else if (isset($data->description)) {
        $this->description = $data->description;
      }
      if (isset($data->ballotEC) && is_numeric($data->ballotEC)) {
        $this->ec = (int)$data->ballotEC;
      } else if (isset($data->ec)) {
        $this->ec = $data->ec;
      }

      if (isset($data->ballotRaces)) {
        $this->data = (object)array(
          'raceData' => is_string($data->ballotRaces) ? json_decode($data->ballotRaces) : $data->ballotRaces
        );
      } else if (isset($data->data)) {
        $this->data = is_string($data->data) ? json_decode($data->data) : $data->data;
      }

      $this->approved = isset($data->approved) ? $data->approved : false;
    }
  }

  public function getData($key) {
    if(isset($this->data) && isset($this->data->{$key})) {
      return $this->data->{$key};
    } else {
      return null;
    }
  }

  public function setData($key, $value) {
    $this->data->{$key} = $value;
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
      $q = $db->prepare("UPDATE elections SET ec = :ec, data = :data WHERE id = :id");
      if (is_string($this->data)) {
        $q->bindParam(':data', $this->data);
      } else {
        $q->bindParam(':data', json_encode($this->data), PDO::PARAM_LOB);
      }
      $q->bindParam(':id', $this->id);
      $q->bindParam(':ec', is_object($this->ec) ? $this->ec->id : $this->ec);
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
    return $this->getData('raceData');
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
