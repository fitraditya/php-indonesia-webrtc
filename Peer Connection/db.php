<?php

class MyDB extends SQLite3 {
  function __construct() {
    $this->open('php_webrtc.db');
  }
}

$db = new MyDB();

?>
