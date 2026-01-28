<?php
session_start();
include "db.php";

require 'vendor/autoload.php'; // cloudinary sdk

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

/* =======================
   CHECK LOGIN
======================= */
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$id   = $user['id'];

/* =======================
   CONFIG CLOUDINARY
   (ใช้ ENV เท่านั้น)
======================= */
Configuration::instance([
    'cloud' => [
        'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => getenv('CLOUDINARY_API_KEY'),
        'api_secret' => getenv('CLOUDINARY_API_SECRET'),
    ],
    'url' => ['secure' => true]
]);

/* =======================
   HANDLE REQUEST
======================= */
$type = $_POST['type'] ?? '';

/* =======================
   CHECK-IN
======================= */
if ($type === 'checkin') {

    $gm = trim($_POST['gm_name'] ?? '');

    if ($gm === '') {
        $_SESSION['error'] = 'กรุณากรอกชื่อเพื่อนตรวจ';
        header("Location: home.php");
        exit;
    }

    /* เช็คว่ามีวันนี้แล้วหรือยัง */
    $chk = $conn->prepare("
        SELECT id FROM checkins
        WHERE discord_id = ? AND DATE(time) = CURDATE()
    ");
    $chk->bind_param("s", $id);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows > 0) {
        $_SESSION['error'] = 'วันนี้คุณเช็คชื่อไปแล้ว';
        header("Location: home.php");
        exit;
    }

    /* =======================
       UPLOAD IMAGE (OPTIONAL)
    ======================= */
    $photo = null;

    if (!empty($_FILES['photo']['tmp_name'])) {
        try {
            $cloudinary = new Cloudinary();

            $upload = $cloudinary->uploadApi()->upload(
                $_FILES['photo']['tmp_name'],
                [
                    'folder' => 'checkin',
                    'resource_type' => 'image'
                ]
            );

            if (!empty($upload['secure_url'])) {
                $photo = $upload['secure_url'];
            }

        } catch (Exception $e) {
            // ❗ ไม่ล้างรูปเก่า ไม่เขียน photo ว่าง
            $_SESSION['error'] = 'อัปโหลดรูปไม่สำเร็จ';
            header("Location: home.php");
            exit;
        }
    }

    /* =======================
       INSERT DB
    ======================= */
    if ($photo) {
        $stmt = $conn->prepare("
            INSERT INTO checkins (discord_id, photo, gm_name)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $id, $photo, $gm);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO checkins (discord_id, gm_name)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $id, $gm);
    }

    $stmt->execute();

    $_SESSION['success'] = 'เช็คชื่อสำเร็จ';
    header("Location: home.php");
    exit;
}

/* =======================
   LEAVE
======================= */
if ($type === 'leave') {

    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        $_SESSION['error'] = 'กรุณากรอกเหตุผลการลา';
        header("Location: home.php");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO leaves (discord_id, reason)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ss", $id, $reason);
    $stmt->execute();

    $_SESSION['success'] = 'ส่งคำขอลาเรียบร้อย';
    header("Location: home.php");
    exit;
}

header("Location: home.php");
exit;
