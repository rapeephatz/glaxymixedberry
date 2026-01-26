<?php
include "db.php";

$users = $conn->query("SELECT COUNT(*) as total FROM users")
              ->fetch_assoc()['total'];

$today = $conn->query("
SELECT COUNT(*) as total 
FROM checkins 
WHERE DATE(time) = CURDATE()
")->fetch_assoc()['total'];

$total = $conn->query("SELECT COUNT(*) as total FROM checkins")
              ->fetch_assoc()['total'];

echo json_encode([
    "users" => $users,
    "today" => $today,
    "total" => $total
]);
