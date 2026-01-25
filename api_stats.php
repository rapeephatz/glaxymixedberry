<?php
$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) die("DB error");

$q1 = $conn->query("SELECT COUNT(*) AS c FROM users");
$q2 = $conn->query("SELECT COUNT(*) AS c FROM checkins");
$q3 = $conn->query("SELECT COUNT(*) AS c FROM checkins WHERE DATE(time)=CURDATE()");

if(!$q1 || !$q2 || !$q3){
    die("SQL error: ".$conn->error);
}

$total_users = $q1->fetch_assoc()['c'];
$total_checkin = $q2->fetch_assoc()['c'];
$today = $q3->fetch_assoc()['c'];

echo json_encode([
    "users" => $total_users,
    "today" => $today,
    "total" => $total_checkin
]);
