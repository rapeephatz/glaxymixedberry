<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

if(!empty($user['avatar'])){
    $ext = str_starts_with($user['avatar'], 'a_') ? 'gif' : 'png';
    $avatar = "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.$ext";
}else{
    $avatar = "https://cdn.discordapp.com/embed/avatars/0.png";
}

$check = $conn->query("
SELECT * FROM checkins 
WHERE discord_id='{$user['id']}' 
AND DATE(time)=CURDATE()
");
$checked_today = $check->num_rows > 0;

$total = $conn->query("
SELECT COUNT(*) t FROM checkins 
WHERE discord_id='{$user['id']}'
")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ระบบเช็คชื่อ GMB</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>
body{
    margin:0;
    background: linear-gradient(135deg,#020617,#0f172a);
    font-family: 'Inter', sans-serif;
    color:white;
}
.container{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}
.card{
    width:420px;
    background:rgba(255,255,255,0.06);
    border-radius:30px;
    padding:40px 36px;
    box-shadow:0 50px 120px rgba(0,0,0,.8);
    text-align:center;
    backdrop-filter: blur(18px);
}
.avatar{
    width:120px;
    height:120px;
    border-radius:50%;
    border:4px solid #6366f1;
    box-shadow:0 0 25px #6366f1;
}
h2{margin:18px 0 6px}
.id{
    color:#94a3b8;
    font-size:13px;
}

.stats{
    display:flex;
    gap:14px;
    margin:22px 0;
}
.stat{
    flex:1;
    background:#0f172a;
    padding:14px;
    border-radius:16px;
}
.stat .label{
    font-size:13px;
    color:#94a3b8;
}
.stat .value{
    margin-top:4px;
    font-size:20px;
    font-weight:700;
}

.progress{
    height:8px;
    background:#0f172a;
    border-radius:8px;
    overflow:hidden;
    margin-bottom:20px;
}
.bar{
    height:100%;
    width:<?= min(100,$total*5) ?>%;
    background:linear-gradient(90deg,#6366f1,#22c55e);
}

/* กล่องอัปโหลดใหม่ */
.upload{
    background:#0f172a;
    border:1px solid #1e293b;
    border-radius:16px;
    padding:14px;
    margin-bottom:12px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    cursor:pointer;
    transition:.2s;
}
.upload span{
    color:#cbd5f5;
    font-size:14px;
}
.upload small{
    color:#64748b;
    font-size:12px;
}
.upload:hover{
    border-color:#6366f1;
    background:#111c3a;
}

.input{
    background:#0f172a;
    padding:14px;
    border-radius:16px;
    margin-bottom:16px;
}
.input input{
    width:100%;
    background:none;
    border:none;
    color:white;
    outline:none;
}

.btn{
    width:100%;
    padding:16px;
    border:none;
    border-radius:18px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}
.btn.primary{
    background:linear-gradient(135deg,#6366f1,#22c55e);
}
.btn.danger{
    margin-top:12px;
    background:linear-gradient(135deg,#ef4444,#f97316);
}
.btn:hover{transform:scale(1.05)}
.btn:disabled{background:#334155}

.footer{
    margin-top:18px;
    font-size:13px;
}
.footer a{
    color:#94a3b8;
    text-decoration:none;
}
.footer a:hover{color:white}

/* popup */
.popup{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.7);
    display:none;
    align-items:center;
    justify-content:center;
}
.modal{
    width:300px;
    background:#0f172a;
    padding:24px;
    border-radius:20px;
}
.modal textarea{
    width:100%;
    height:90px;
    margin-top:10px;
    background:#020617;
    border:none;
    border-radius:12px;
    color:white;
    padding:10px;
}
</style>
</head>
<body>

<div class="container">
<div class="card">

<img class="avatar" src="<?= $avatar ?>">
<h2><?= htmlspecialchars($user['username']) ?></h2>
<div class="id">Discord ID: <?= $user['id'] ?></div>

<div class="stats">
    <div class="stat">
        <div class="label">🔥 เช็คสะสม</div>
        <div class="value"><?= $total ?></div>
    </div>
    <div class="stat">
        <div class="label">📅 วันนี้</div>
        <div class="value"><?= $checked_today?'เช็คแล้ว':'ยังไม่เช็ค' ?></div>
    </div>
</div>

<div class="progress"><div class="bar"></div></div>

<form action="checkin.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type" value="checkin">

<label class="upload">
    <span>📸 รูปตอนเล่นเกม</span>
    <small>คลิกเพื่อเลือกไฟล์</small>
    <input type="file" name="photo" hidden required>
</label>

<div class="input">
<input type="text" name="gm_name" placeholder="˚₊‧ ɢᴍʙ ‧₊˚ ชื่อเพื่อนตรวจ" required>
</div>

<button class="btn primary" <?= $checked_today?'disabled':'' ?>>
<?= $checked_today?'เช็คแล้ววันนี้':'เช็คชื่อวันนี้' ?>
</button>
</form>

<button class="btn danger" onclick="openLeave()">ขอลา</button>

<div class="footer">
<a href="admin.php">Admin</a> | 
<a href="logout.php">Logout</a>
</div>

</div>
</div>

<div class="popup" id="leavePopup">
<div class="modal">
<h3>ขอลา</h3>
<form action="checkin.php" method="post">
<input type="hidden" name="type" value="leave">
<textarea name="reason" placeholder="เหตุผลการลา..." required></textarea>
<button class="btn danger">ยืนยัน</button>
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
