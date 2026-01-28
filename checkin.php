<?php
session_start();
include "db.php";

/* ==============================
   เช็ค session
   ============================== */
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user']['id'];

/* ==============================
   กันเช็คอินซ้ำ
   ============================== */
$check = $conn->query("
    SELECT id FROM checkins
    WHERE discord_id='$id'
    AND DATE(time)=CURDATE()
");

if ($check && $check->num_rows > 0) {
    header("Location: dashboard.php");
    exit();
}

/* ==============================
   Cloudinary upload
   ============================== */
function uploadToCloudinary($file){
    $cloud  = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? null;
    $key    = $_ENV['CLOUDINARY_API_KEY'] ?? null;
    $secret = $_ENV['CLOUDINARY_API_SECRET'] ?? null;

    if (!$cloud || !$key || !$secret) {
        return ['error' => 'Cloudinary env missing'];
    }

    $timestamp = time();
    $signature = sha1("timestamp=$timestamp$secret");

    $data = [
        'file' => new CURLFile($file['tmp_name']),
        'api_key' => $key,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => 'checkin'
    ];

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloud/image/upload");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}

/* ==============================
   อัปโหลดรูป (ถ้ามี)
   ============================== */
$photo_url = null;

if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === 0) {
    $upload = uploadToCloudinary($_FILES['photo']);

if (isset($upload['error'])) {
    $_SESSION['error'] = "❌ Cloudinary config ผิดพลาด";
    header("Location: dashboard.php");
    exit();
}


    $photo_url = $upload['secure_url'];
}

/* ==============================
   insert DB
   ============================== */
$stmt = $conn->prepare("
    INSERT INTO checkins (discord_id, time, photo)
    VALUES (?, NOW(), ?)
");
$stmt->bind_param("ss", $id, $photo_url);
$stmt->execute();

/* ✅ ส่งข้อความสำเร็จไปหน้า dashboard */
$_SESSION['success'] = "✅ เช็คชื่อเรียบร้อยแล้ว";

/* redirect */
header("Location: dashboard.php");
exit();