<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php';
include_once '../model/CustomError.php';

//date is formatted in year-month-day
//if month or date is single digit add zero to be accepted
//example 2022-01-10 or 2022-12-04

//DB connection
$db = new Database();
$conn = $db->getConnection();
$dbname = "meetingDB";
$collection = 'events';

try {
  $data = json_decode(file_get_contents("php://input", true));

  if (!validateDate($data->startDate) || !validateDate($data->endDate)) {
    http_response_code(416);
    throw new CustomError("416", "Invalid Date");
    die();
  } else {
    $filter = ['name' => $data->name, 'startDate' => $data->startDate, 'endDate' => $data->endDate];
    $option = [];
    $read = new MongoDB\Driver\Query($filter, $option);
    $records = $conn->executeQuery("$dbname.$collection", $read);

    $dateToday = date('Y-m-d');
    $tempStartDate = new DateTime($data->startDate);
    $tempEndDate = new DateTime($data->endDate);
    $startDate = date_format($tempStartDate, 'Y-m-d');
    $endDate = date_format($tempEndDate, 'Y-m-d');

    foreach ($records as $record) {
      if (
        $record->name === $data->name
        && $record->startDate === $data->startDate
        && $record->endDate === $data->endDate
      ) {
        http_response_code(400);
        throw new CustomError("400", "Event Already Exist");
        die();
      }
    }

    if (
      $startDate >= $dateToday
      && $startDate < $endDate
    ) {
      $data = ["name" => $data->name, "startDate" => $startDate, "endDate" => $endDate];
      $insert = new MongoDB\Driver\BulkWrite();
      $insert->insert($data);
      $result = $conn->executeBulkWrite("$dbname.$collection", $insert);
    } else {
      http_response_code(400);
      throw new CustomError("400", "Unacceptable Date");
      die();
    }
  }
} catch (CustomError $e) {
  echo json_encode(
    array(
      "status" => $e->getErrorStatus(),
      "error message" => $e->getErrorMessage(),
    )
  );
}

function validateDate($date, $format = 'Y-m-d')
{
  $d = DateTime::createFromFormat($format, $date);
  return $d && $d->format($format) === $date;
}
