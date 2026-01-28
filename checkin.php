<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$id   = $_SESSION['user']['id'];
$type = $_POST['type'] ?? '';

/* ======================
   กันซ้ำ (ทั้งเช็คชื่อและลา)
   ====================== */
$check = $conn->query("
    SELECT id FROM checkins
    WHERE discord_id='$id'
    AND DATE(time)=CURDATE()
");

if ($check->num_rows > 0) {
    $_SESSION['error'] = "วันนี้คุณส่งไปแล้ว";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   กรณี ขอลา
   ====================== */
if ($type === 'leave') {

    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        $_SESSION['error'] = "กรุณากรอกเหตุผลการลา";
        header("Location: dashboard.php");
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO checkins (discord_id, time, type, reason)
        VALUES (?, NOW(), 'leave', ?)
    ");
    $stmt->bind_param("ss", $id, $reason);
    $stmt->execute();

    $_SESSION['success'] = "📄 ส่งใบลาเรียบร้อย";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   กรณี เช็คชื่อ (ต้องมีรูป)
   ====================== */
if (empty($_FILES['photo']['tmp_name'])) {
    $_SESSION['error'] = "กรุณาอัปโหลดรูป";
    header("Location: dashboard.php");
    exit();
}

/* ===== Cloudinary (เหมือนเดิม) ===== */
function uploadToCloudinary($file){
    $cloud  = getenv('CLOUDINARY_CLOUD_NAME');
    $key    = getenv('CLOUDINARY_API_KEY');
    $secret = getenv('CLOUDINARY_API_SECRET');

    $timestamp = time();
    $signature = sha1("timestamp=$timestamp$secret");

    $data = [
        'file' => new CURLFile($file['tmp_name']),
        'api_key' => $key,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => 'checkins'
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

$upload = uploadToCloudinary($_FILES['photo']);

if (!isset($upload['secure_url'])) {
    $_SESSION['error'] = "อัปโหลดรูปไม่สำเร็จ";
    header("Location: dashboard.php");
    exit();
}

$photo = $upload['secure_url'];

$stmt = $conn->prepare("
    INSERT INTO checkins (discord_id, time, type, photo)
    VALUES (?, NOW(), 'checkin', ?)
");
$stmt->bind_param("ss", $id, $photo);
$stmt->execute();

$_SESSION['success'] = "✅ เช็คชื่อเรียบร้อย";
header("Location: dashboard.php");
exit();
