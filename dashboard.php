<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$today = date("Y-m-d");

/* avatar */
if(!empty($user['avatar'])){
    $ext = str_starts_with($user['avatar'], 'a_') ? 'gif' : 'png';
    $avatar = "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.$ext";
}else{
    $avatar = "https://cdn.discordapp.com/embed/avatars/0.png";
}

/* เช็ควันนี้ */
$check = $conn->query("
SELECT * FROM checkins 
WHERE discord_id='{$user['id']}' 
AND DATE(time)=CURDATE()
");
$checked_today = $check->num_rows > 0;

/* นับทั้งหมด */
$total = $conn->query("
SELECT COUNT(*) t FROM checkins 
WHERE discord_id='{$user['id']}'
")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance</title>
<style>
body{margin:0;background:#020617;color:white;font-family:sans-serif}
.container{height:100vh;display:flex;align-items:center;justify-content:center}
.card{width:380px;background:#111827;padding:30px;border-radius:20px;text-align:center}
.avatar{width:100px;border-radius:50%;border:3px solid #5865F2}
.btn{margin-top:10px;padding:12px;width:100%;border:none;border-radius:12px;
background:#5865F2;color:white;font-size:15px;cursor:pointer}
.btn.red{background:#ef4444}
.btn:disabled{background:#334155}
.popup{position:fixed;inset:0;background:rgba(0,0,0,.6);
display:none;align-items:center;justify-content:center}
.modal{background:#0f172a;padding:25px;border-radius:16px;width:300px}
input,textarea{width:100%;padding:10px;margin-top:10px;border-radius:8px;border:none}
</style>
</head>
<body>

<div class="container">
<div class="card">

<img class="avatar" src="<?= $avatar ?>">
<h2><?= htmlspecialchars($user['username']) ?></h2>
<p>ID: <?= $user['id'] ?></p>

<p>เช็คทั้งหมด: <?= $total ?></p>

<form action="checkin.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type" value="checkin">

<input type="file" name="photo" required>

<input type="text" name="gm_name" placeholder="ชื่อเพื่อนตรวจ (ต้องมี ˚₊‧ ɢᴍʙ ‧₊˚)" required>

<button class="btn" <?= $checked_today?'disabled':'' ?>>
<?= $checked_today?'เช็คแล้ววันนี้':'เช็คชื่อวันนี้' ?>
</button>
</form>

<button class="btn red" onclick="openLeave()">ขอลา</button>

<br><br>
<a href="logout.php" style="color:#94a3b8">Logout</a>

</div>
</div>

<!-- popup ลา -->
<div class="popup" id="leavePopup">
<div class="modal">
<h3>ขอลา</h3>
<form action="checkin.php" method="post">
<input type="hidden" name="type" value="leave">
<textarea name="reason" placeholder="เหตุผลการลา" required></textarea>
<button class="btn red">ยืนยันลา</button>
</form>
<button class="btn" onclick="closeLeave()">ยกเลิก</button>
</div>
</div>

<script>
function openLeave(){ leavePopup.style.display='flex' }
function closeLeave(){ leavePopup.style.display='none' }
</script>

</body>
</html>
