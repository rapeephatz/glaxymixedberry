<?php
include "db.php";

$today = date("Y-m-d");

/* จำนวน user */
$u = $conn->query("SELECT COUNT(*) as total FROM users")
          ->fetch_assoc()['total'];

/* วันนี้ */
$t = $conn->query("
SELECT COUNT(*) as total 
FROM checkins 
WHERE DATE(created_at) = '$today'
")->fetch_assoc()['total'];

/* ทั้งหมด */
$c = $conn->query("SELECT COUNT(*) as total FROM checkins")
          ->fetch_assoc()['total'];

echo json_encode([
    "users" => $u,
    "today" => $t,
    "total" => $c
]);
