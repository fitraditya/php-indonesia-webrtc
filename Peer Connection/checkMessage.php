<?php

require 'db.php';

$to = $_GET['to'];

$result = $db->query("SELECT `id`, `from`, `type`, `messages` FROM messages WHERE `to`='".$to."'");

$data = array();

while ($row = $result->fetchArray()) {
  $tmp['id'] = $row[0];
  $tmp['from'] = $row[1];
  $tmp['type'] = $row[2];
  $tmp['messages'] = $row[3];
  array_push($data, $tmp);
  $db->exec("DELETE FROM messages WHERE `id`='".$tmp['id']."'");
}

if (count($data)) {
  $length = true;
} else {
  $length = false;
}

$response = array('result' => $length , 'data' => $data);

echo json_encode($response);

?>
