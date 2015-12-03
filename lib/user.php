<?php

class User {

  public $name;
  public $id;
  public $role;
  public $data;

  public function __construct($data = array()) {
    if($data != false && get_class($data) != 'User') {
      $this->name = $data->name;
      $this->role = (int)$data->role;
      $this->id = (int)$data->userid;
      $this->data = $data->data;
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
    $q = $db->prepare("UPDATE users SET data = :data WHERE userid = :id");
    $q->bindParam(':data', serialize($this->data), PDO::PARAM_LOB);
    $q->bindParam(':id', $this->id);
    $q->execute();
    return ($q->rowCount() == 1);
  }

  public function isAdmin() {
    return ($this->role == 2 || $this->isSuperAdmin());
  }

  public function isSuperAdmin() {
    return ($this->role == 1);
  }

  public function isEC() {
    if($this->isAdmin()) {
      return true;
    } else if ($this->getData('isEC')) {
      return $this->getData('isEC');
    } else {
      return false;
    }
  }
}
