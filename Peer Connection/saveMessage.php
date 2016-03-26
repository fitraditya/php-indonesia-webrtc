<?php

require 'db.php';

$from = $_POST['from'];
$to = $_POST['to'];
$type = $_POST['type'];
$message = $_POST['message'];

$result = $db->exec("INSERT INTO messages VALUES (null, '".$from."', '".$to."', '".$type."', '".urldecode($message)."')");

$response = array('result' => $result);

echo json_encode($response);

?>
