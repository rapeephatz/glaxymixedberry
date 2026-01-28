<?php
session_start();
include "db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ======================
   เช็ค session
   ====================== */
if (!isset($_SESSION['user']['id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบ";
    header("Location: index.php");
    exit();
}

$id = $_SESSION['user']['id'];

/* ======================
   กันเช็คซ้ำ
   ====================== */
$check = $conn->query("
    SELECT id FROM checkins
    WHERE discord_id='$id'
    AND DATE(time)=CURDATE()
");

if ($check && $check->num_rows > 0) {
    $_SESSION['error'] = "วันนี้คุณเช็คชื่อไปแล้ว";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   upload รูป (Cloudinary Unsigned)
   ====================== */
if (empty($_FILES['photo']['tmp_name'])) {
    $_SESSION['error'] = "กรุณาเลือกรูป";
    header("Location: dashboard.php");
    exit();
}

$cloudName    = getenv('CLOUDINARY_CLOUD_NAME');
$uploadPreset = "checkin_upload";

if (!$cloudName) {
    $_SESSION['error'] = "Cloudinary ENV missing";
    header("Location: dashboard.php");
    exit();
}

$file = $_FILES['photo']['tmp_name'];

$ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/upload");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => [
        'file' => new CURLFile($file),
        'upload_preset' => $uploadPreset
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (!isset($result['secure_url'])) {
    $_SESSION['error'] = "อัปโหลดรูปไม่สำเร็จ";
    header("Location: dashboard.php");
    exit();
}

$photo_url = $result['secure_url'];

/* ======================
   insert DB
   ====================== */
$stmt = $conn->prepare("
    INSERT INTO checkins (discord_id, time, photo)
    VALUES (?, NOW(), ?)
");
$stmt->bind_param("ss", $id, $photo_url);
$stmt->execute();

$_SESSION['success'] = "✅ เช็คชื่อเรียบร้อย";

/* ======================
   redirect
   ====================== */
header("Location: dashboard.php");
exit();
