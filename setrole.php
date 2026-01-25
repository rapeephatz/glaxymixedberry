<?php
include "auth_admin.php";
$conn->query("UPDATE users SET role='{$_GET['role']}' 
WHERE id={$_GET['id']}");
header("Location: users.php");
