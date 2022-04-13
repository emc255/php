<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/Database.php';

$collection = 'events';

$db = new Database();
$conn = $db->getConnection();
$dbname = "meetingDB";

$filter = [];
$option = [];
$read = new MongoDB\Driver\Query($filter, $option);

//fetch records
$records = $conn->executeQuery("$dbname.$collection", $read);

echo json_encode(iterator_to_array($records));
