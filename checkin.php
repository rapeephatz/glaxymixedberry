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

/* ==============================
   ฟังก์ชันอัปโหลด Cloudinary
   ============================== */
function uploadToCloudinary($file){
    $cloud = "dzisxdaul";        // cloud name ของคุณ
    $key = "533369769661924";
    $secret = "ScW3SimCLzYDgMPGeBHfz3I-a3g";

    $timestamp = time();
    $signature = sha1("timestamp=$timestamp$secret");

    $data = [
        'file' => new CURLFile($file['tmp_name']),
        'api_key' => $key,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => 'checkin'   // โฟลเดอร์ใน Cloudinary
    ];

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloud/image/upload");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}

/* ==============================
   อัปโหลดรูป
   ============================== */
$photo_url = null;

if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
    $upload = uploadToCloudinary($_FILES['photo']);
    $photo_url = $upload['secure_url']; // URL รูปจริง
}

/* ==============================
   insert ลง DB
   ============================== */
$conn->query("
INSERT INTO checkins (discord_id,time,photo) 
VALUES ('$id', NOW(), '$photo_url')
");

header("Location: dashboard.php");
exit();
