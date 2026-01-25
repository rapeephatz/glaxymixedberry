<?php
session_start();
include "db.php";

if($_SESSION['user']['role'] != 'admin'){
    exit("No permission");
}

$id = $_POST['id'];
$role = $_POST['role'];

$conn->query("UPDATE users SET role='$role' WHERE discord_id='$id'");
header("Location: users.php");
