<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$today = date("Y-m-d");

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
<html>
<head>
<title>GMB Attendance</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>
body{
    margin:0;
    background: radial-gradient(circle at top,#1e293b,#020617);
    font-family: 'Inter', sans-serif;
    color:white;
}
.container{
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.card{
    width:400px;
    background:rgba(255,255,255,0.06);
    backdrop-filter: blur(18px);
    border-radius:28px;
    padding:40px 35px;
    text-align:center;
    box-shadow:0 40px 100px rgba(0,0,0,.7);
}
.avatar{
    width:120px;
    border-radius:50%;
    border:4px solid #6366f1;
    box-shadow:0 0 30px #6366f1;
}
h2{margin:15px 0 4px}
p{color:#94a3b8;font-size:14px}

.stat{
    display:flex;
    justify-content:space-between;
    margin:20px 0;
}
.stat div{
    background:#0f172a;
    padding:12px 18px;
    border-radius:14px;
    width:48%;
}

.progress{
    height:8px;
    background:#0f172a;
    border-radius:8px;
    overflow:hidden;
    margin:20px 0;
}
.bar{
    height:100%;
    width:<?= min(100,$total*5) ?>%;
    background:linear-gradient(90deg,#6366f1,#22c55e);
}

.input{
    margin-top:12px;
    background:#0f172a;
    padding:12px;
    border-radius:14px;
}
.input input{
    width:100%;
    background:none;
    border:none;
    color:white;
    outline:none;
}

.file{
    border:2px dashed #334155;
    padding:18px;
    border-radius:14px;
    margin-top:14px;
    cursor:pointer;
    color:#94a3b8;
}
.file:hover{
    border-color:#6366f1;
    color:white;
}

.btn{
    margin-top:18px;
    padding:14px;
    width:100%;
    border:none;
    border-radius:16px;
    font-size:16px;
    cursor:pointer;
    font-weight:600;
    transition:.2s;
}
.btn.primary{
    background:linear-gradient(135deg,#6366f1,#22c55e);
}
.btn.danger{
    background:linear-gradient(135deg,#ef4444,#f97316);
}
.btn:hover{transform:scale(1.05)}
.btn:disabled{background:#334155}

a{color:#94a3b8;font-size:13px;text-decoration:none}
a:hover{color:white}
</style>

</head>
<body>

<div class="bg"></div>

<div class="container">
<div class="card">

<img class="avatar" src="<?= $avatar ?>">
<h2><?= htmlspecialchars($user['username']) ?></h2>
<div class="id">ID: <?= $user['id'] ?></div>

<div class="stat">
<div class="box">🔥 Streak<span><?= $total ?></span></div>
<div class="box">📅 Today<span><?= $checked_today?'✔':'-' ?></span></div>
</div>

<div class="progress"><div class="bar"></div></div>

<form action="checkin.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type" value="checkin">

<label class="file">
    📸 อัปโหลดรูปการเล่น
    <input type="file" name="photo" hidden required>
</label>

<div class="input">
<input type="text" name="gm_name" 
placeholder="˚₊‧ ɢᴍʙ ‧₊˚ ชื่อเพื่อนตรวจ" required>
</div>

<button class="btn primary" <?= $checked_today?'disabled':'' ?>>
<?= $checked_today?'เช็คแล้ววันนี้':'🎉 เช็คชื่อวันนี้' ?>
</button>

</form>

<button class="btn danger" onclick="openLeave()">🚨 ขอลา</button>

<div class="footer">
<a href="admin.php">Admin</a> | 
<a href="logout.php">Logout</a>
</div>

</div>
</div>

<div class="popup" id="leavePopup">
<div class="modal">
<h3>🛌 ขอลา</h3>
<form action="checkin.php" method="post">
<input type="hidden" name="type" value="leave">
<textarea name="reason" placeholder="เหตุผลการลา..." required></textarea>
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
