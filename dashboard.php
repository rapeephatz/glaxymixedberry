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
*{box-sizing:border-box}
body{
margin:0;
font-family:Inter;
background:
radial-gradient(circle at top,#1e293b,#020617);
color:white;
overflow:hidden;
}
.bg{
position:fixed;inset:0;
background:url("https://i.imgur.com/8bKQXQy.png");
opacity:.07;
}
.container{
height:100vh;
display:flex;
align-items:center;
justify-content:center;
position:relative;
}
.card{
width:420px;
padding:40px;
border-radius:28px;
background:rgba(255,255,255,0.08);
backdrop-filter:blur(18px);
box-shadow:0 60px 120px rgba(0,0,0,.7);
text-align:center;
animation:pop .8s ease;
}
@keyframes pop{
from{transform:scale(.8);opacity:0}
to{transform:scale(1);opacity:1}
}
.avatar{
width:120px;
border-radius:50%;
border:4px solid #5865F2;
box-shadow:0 0 40px #5865F2;
}
h2{margin:15px 0 0;font-size:26px}
.id{font-size:13px;color:#94a3b8}

.stat{
margin:20px 0;
display:flex;
justify-content:space-between;
}
.box{
flex:1;
margin:0 6px;
background:rgba(0,0,0,.4);
padding:12px;
border-radius:14px;
font-size:13px;
}
.box span{display:block;font-size:22px;font-weight:800}

.progress{
margin:25px 0;
height:12px;
background:#0f172a;
border-radius:20px;
overflow:hidden;
}
.bar{
height:100%;
width:<?= min(100,$total*5) ?>%;
background:linear-gradient(90deg,#5865F2,#22c55e);
box-shadow:0 0 20px #5865F2;
}

.btn{
margin-top:12px;
padding:14px;
width:100%;
border:none;
border-radius:16px;
background:linear-gradient(135deg,#5865F2,#22c55e);
color:white;
font-size:16px;
font-weight:700;
cursor:pointer;
transition:.25s;
box-shadow:0 0 30px rgba(88,101,242,.5);
}
.btn:hover{
transform:scale(1.06);
box-shadow:0 0 60px rgba(88,101,242,1);
}
.btn.red{
background:linear-gradient(135deg,#ef4444,#f97316);
box-shadow:0 0 30px rgba(239,68,68,.6);
}
.btn:disabled{
background:#334155;
box-shadow:none;
transform:none;
cursor:not-allowed;
}

.popup{
position:fixed;inset:0;
background:rgba(0,0,0,.7);
display:none;
align-items:center;
justify-content:center;
z-index:10;
}
.modal{
width:340px;
background:rgba(15,23,42,.9);
backdrop-filter:blur(16px);
padding:30px;
border-radius:22px;
box-shadow:0 40px 80px rgba(0,0,0,.7);
text-align:center;
animation:pop .4s ease;
}
input,textarea{
width:100%;
padding:12px;
margin-top:10px;
border-radius:12px;
border:none;
background:#020617;
color:white;
}
.footer{
margin-top:20px;
font-size:13px;
color:#94a3b8;
}
.footer a{color:#94a3b8;text-decoration:none}
.footer a:hover{color:white}
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

<input type="file" name="photo" required>
<input type="text" name="gm_name" placeholder="˚₊‧ ɢᴍʙ ‧₊˚ ชื่อคนตรวจ" required>

<button class="btn" <?= $checked_today?'disabled':'' ?>>
<?= $checked_today?'🎉 เช็คแล้ววันนี้':'🎮 เช็คชื่อวันนี้' ?>
</button>
</form>

<button class="btn red" onclick="openLeave()">🛌 ขอลา</button>

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
