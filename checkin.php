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
    // 🔥 ต้องตรงกับ Railway Variables เป๊ะ
    $cloud  = getenv('CLOUDINARY_CLOUD_NAME');
    $key    = getenv('CLOUDINARY_API_KEY');
    $secret = getenv('CLOUDINARY_API_SECRET');

    if (!$cloud || !$key || !$secret) {
        return [
            'error' => 'env_missing',
            'cloud' => $cloud,
            'key' => $key ? 'ok' : null,
            'secret' => $secret ? 'ok' : null
        ];
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
   อัปโหลดรูป
   ============================== */
$photo_url = null;

if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === 0) {

    $upload = uploadToCloudinary($_FILES['photo']);

    // ❌ ถ้า Cloudinary มีปัญหา
    if (!isset($upload['secure_url'])) {
        $_SESSION['error'] =
            "❌ Cloudinary error: " . json_encode($upload);
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

/* ==============================
   success
   ============================== */
$_SESSION['success'] = "✅ เช็คชื่อเรียบร้อยแล้ว";

header("Location: dashboard.php");
exit();
