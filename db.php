<?php
$host = getenv("MYSQL_HOST") ?: "localhost";
$user = getenv("MYSQL_USER") ?: "root";
$pass = getenv("MYSQL_PASSWORD") ?: "";
$db   = getenv("MYSQL_DATABASE") ?: "attendance";
$port = getenv("MYSQL_PORT") ?: 3306;

$conn = new mysqli($host,$user,$pass,$db,$port);

if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}
