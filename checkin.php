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
   กันส่งซ้ำ (วันเดียวกัน)
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
if (
    !isset($_FILES['photo']) ||
    $_FILES['photo']['error'] !== UPLOAD_ERR_OK ||
    !is_uploaded_file($_FILES['photo']['tmp_name'])
) {
    $_SESSION['error'] = "กรุณาอัปโหลดรูปใหม่อีกครั้ง";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   ตรวจ MIME (รองรับมือถือ)
   ====================== */
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['photo']['tmp_name']);
finfo_close($finfo);

$allow = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
    'image/heic',
    'image/heif'
];

if (!in_array($mime, $allow)) {
    $_SESSION['error'] = "ชนิดไฟล์ไม่รองรับ ($mime)";
    header("Location: dashboard.php");
    exit();
}

/* ======================
   Upload → Cloudinary (ถูกต้อง)
   ====================== */
function uploadToCloudinary($file){

    $cloud  = getenv('CLOUDINARY_CLOUD_NAME');
    $key    = getenv('CLOUDINARY_API_KEY');
    $secret = getenv('CLOUDINARY_API_SECRET');

    $timestamp = time();

    // ✅ ต้อง sign folder + timestamp
    $stringToSign = "folder=checkins&timestamp=$timestamp";
    $signature = sha1($stringToSign . $secret);

    $data = [
        'file' => new CURLFile(
            $file['tmp_name'],
            mime_content_type($file['tmp_name']),
            $file['name']
        ),
        'api_key'   => $key,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder'    => 'checkins'
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

/* ======================
   อัปโหลดจริง
   ====================== */
$upload = uploadToCloudinary($_FILES['photo']);

if (!isset($upload['secure_url'])) {

    // DEBUG
    echo "<pre>";
    echo "Cloudinary error\n";
    print_r($upload);
    echo "</pre>";
    exit();

    // production
    // $_SESSION['error'] = "อัปโหลดรูปไม่สำเร็จ (Cloudinary)";
    // header("Location: dashboard.php");
    // exit();
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
