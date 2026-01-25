<?php
session_start();
include "db.php";

if($_SESSION['user']['role'] != 'admin'){
    exit("No permission");
}

$id = $_GET['id'];
$conn->query("DELETE FROM checkins WHERE discord_id='$id'");
$conn->query("DELETE FROM users WHERE discord_id='$id'");
header("Location: users.php");
