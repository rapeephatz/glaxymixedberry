<?php
session_start();
include "db.php";

/* ======================
   รองรับรูปมือถือ / iPhone
   ====================== */
ini_set('upload_max_filesize','15M');
ini_set('post_max_size','15M');
ini_set('memory_limit','256M');

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$id   = $_SESSION['user']['id'];
$type = $_POST['type'] ?? '';

/* ======================
   กันซ้ำวันเดียวกัน
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
   ขอลา
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
   ตรวจไฟล์
   ====================== */
if (
    !isset($_FILES['photo']) ||
    $_FILES['photo']['error'] !== UPLOAD_ERR_OK
) {
    $_SESSION['error'] = "กรุณาอัปโหลดรูป";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   รองรับ HEIC / HEIF (iPhone)
   ====================== */
$allow = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
    'image/heic',
    'image/heif'
];

if (!in_array($_FILES['photo']['type'], $allow)) {
    $_SESSION['error'] = "ไฟล์รูปไม่รองรับ";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   Upload Cloudinary
   ====================== */
function uploadToCloudinary($file){
    $cloud  = getenv('CLOUDINARY_CLOUD_NAME');
    $key    = getenv('CLOUDINARY_API_KEY');
    $secret = getenv('CLOUDINARY_API_SECRET');

    $timestamp = time();
    $signature = sha1("timestamp=$timestamp$secret");

    $data = [
        'file' => curl_file_create(
            $file['tmp_name'],
            $file['type'],
            $file['name']
        ),
        'api_key' => $key,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => 'checkins',
        'resource_type' => 'image',
        'format' => 'jpg' // ⭐ แปลง HEIC → JPG
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

if (empty($upload['secure_url'])) {
    $_SESSION['error'] = "อัปโหลดรูปไม่สำเร็จ (Cloudinary)";
    header("Location: dashboard.php");
    exit();
}

$photo = $upload['secure_url'];

/* ======================
   บันทึก DB
   ====================== */
$stmt = $conn->prepare("
    INSERT INTO checkins (discord_id, time, type, photo)
    VALUES (?, NOW(), 'checkin', ?)
");
$stmt->bind_param("ss", $id, $photo);
$stmt->execute();

$_SESSION['success'] = "✅ เช็คชื่อเรียบร้อย";
header("Location: dashboard.php");
exit();