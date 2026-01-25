<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];   // ⭐ บรรทัดนี้สำคัญมาก


$row = $conn->query("SELECT display_name,status 
FROM users WHERE discord_id='{$user['id']}'")
->fetch_assoc();

if(empty($row['display_name'])){
    header("Location: setname.php");
    exit();
}

if($row['status'] == 'pending'){
    header("Location: wait.php");
    exit();
}



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


/* เช็คว่าวันนี้เช็คยัง */
$check = $conn->query("
SELECT * FROM checkins 
WHERE discord_id='{$user['id']}' 
AND DATE(time) = CURDATE()
");
$checked_today = $check->num_rows > 0;


/* นับทั้งหมด */
$total = $conn->query("SELECT COUNT(*) as t FROM checkins 
WHERE discord_id='{$user['id']}'")->fetch_assoc()['t'];

?>
<!DOCTYPE html>
<html>
<head>
<title>Attendance</title>
<style>
body {
    margin:0;
    background: radial-gradient(circle at top,#1e293b,#020617);
    font-family:'Segoe UI',sans-serif;
    color:white;
}
.bg {
    position:absolute;
    inset:0;
    background: url("https://i.imgur.com/8bKQXQy.png");
    opacity:.08;
}
.container {
    position:relative;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}
.card {
    width:360px;
    background: rgba(255,255,255,0.06);
    border-radius:24px;
    padding:40px;
    text-align:center;
    backdrop-filter: blur(14px);
    box-shadow:0 40px 80px rgba(0,0,0,.6);
    animation: pop .8s ease;
}
@keyframes pop {
    from { transform:scale(.8); opacity:0 }
    to { transform:scale(1); opacity:1 }
}
.avatar {
    width:110px;
    border-radius:50%;
    border:4px solid #5865F2;
    box-shadow:0 0 30px #5865F2;
}
h2 { margin:15px 0 5px; }
.badge {
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    background:#22c55e;
    font-size:13px;
}
.badge.off { background:#ef4444; }
.progress {
    margin:25px 0;
    height:10px;
    background:#0f172a;
    border-radius:10px;
    overflow:hidden;
}
.bar {
    height:100%;
    width:<?= min(100,$total*5) ?>%;
    background: linear-gradient(90deg,#5865F2,#22c55e);
}
.btn {
    margin-top:20px;
    padding:14px;
    width:100%;
    border:none;
    border-radius:14px;
    background:#5865F2;
    color:white;
    font-size:16px;
    cursor:pointer;
}
.btn:hover { transform:scale(1.05); }
.btn:disabled { background:#334155; }
a { color:#94a3b8; font-size:13px; }
</style>
</head>
<body>

<div class="bg"></div>

<div class="container">
<div class="card">

<img class="avatar" src="<?= $avatar ?>">

<h2><?= htmlspecialchars($user['username']) ?></h2>
<p>ID: <?= $user['id'] ?></p>

<span class="badge <?= $checked_today?'':'off' ?>">
<?= $checked_today ? 'Checked Today' : 'Not Checked' ?>
</span>

<div class="progress">
    <div class="bar"></div>
</div>

<p>Total Check-ins: <strong><?= $total ?></strong></p>

<form action="checkin.php" method="post">
    <button class="btn" <?= $checked_today?'disabled':'' ?>>
        <?= $checked_today?'เช็คแล้ววันนี้':'เช็คชื่อวันนี้' ?>
    </button>
</form>

<br>
👷‍♂️ <a href="admin.php">Admin Panel</a> | 
🚪 <a href="logout.php">Logout</a>

</div>
</div>

</body>
</html>
