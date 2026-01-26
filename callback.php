<?php
session_start();
include "db.php";

/* 1. เช็ก code */
if (!isset($_GET['code'])) {
    exit("No code");
}

$code = $_GET['code'];

$client_id = "1464996907016524048";
$client_secret = "JpokKOO-WUcfpOwjP92-rMdKIUw7nY8J";
$redirect_uri = "https://glaxymixedberry.onrender.com/callback.php";

/* 2. ขอ access token */
$token_url = "https://discord.com/api/oauth2/token";

$data = [
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "grant_type" => "authorization_code",
    "code" => $code,
    "redirect_uri" => $redirect_uri,
    "scope" => "identify"
];

$context = stream_context_create([
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/x-www-form-urlencoded",
        "content" => http_build_query($data)
    ]
]);

$response = file_get_contents($token_url, false, $context);
$result = json_decode($response, true);

if (!isset($result['access_token'])) {
    var_dump($result);
    exit("No access token");
}

$access_token = $result['access_token'];

/* 3. ดึงข้อมูล user จาก Discord */
$ch = curl_init("https://discord.com/api/users/@me");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$user = json_decode($response, true);

if (!isset($user['id'])) {
    var_dump($user);
    exit("Discord user error");
}

/* 4. ข้อมูล */
$id = $user['id'];
$name = $user['username'];
$avatar = $user['avatar'] ?? null;

/* 5. ถ้ายังไม่เคยมีใน DB ให้สร้าง */
$check = $conn->query("SELECT * FROM users WHERE discord_id='$id'");
if ($check->num_rows == 0) {
    $conn->query("
        INSERT INTO users (discord_id, username, role, status)
        VALUES ('$id', '$name', 'user', 'new')
    ");
}

/* 6. ดึงข้อมูลล่าสุด */
$row = $conn->query("
    SELECT role, display_name, status 
    FROM users 
    WHERE discord_id='$id'
")->fetch_assoc();

/* 7. สร้าง session */
$_SESSION['user'] = [
    "id" => $id,
    "username" => $name,
    "avatar" => $avatar,
    "role" => $row['role']
];

/* 8. FLOW กลาง (สำคัญที่สุด) */
if (empty($row['display_name'])) {
    header("Location: setname.php");
    exit();
}

if ($row['status'] != 'approved') {
    header("Location: wait.php");
    exit();
}

header("Location: dashboard.php");
exit();
