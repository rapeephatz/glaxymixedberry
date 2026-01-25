<?php
session_start();
include "db.php";

if($_SESSION['user']['role'] != 'admin'){
    exit("No permission");
}

$id = $_GET['id'];

$conn->query("UPDATE users 
SET status='approved' 
WHERE discord_id='$id'");

header("Location: users.php");
exit();
