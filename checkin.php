<?php
session_start();
include "db.php";

$id = $_SESSION['user']['id'];

/* กันเช็คซ้ำ */
$check = $conn->query("
SELECT * FROM checkins 
WHERE discord_id='$id' 
AND DATE(time)=CURDATE()
");

if($check->num_rows > 0){
    header("Location: dashboard.php");
    exit();
}

/* insert */
$conn->query("INSERT INTO checkins (discord_id,time) 
VALUES ('$id', NOW())");

header("Location: dashboard.php");
exit();
