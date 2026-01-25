<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$id = $_SESSION['user']['id'];
$error = "";

/* ดึงชื่อเดิม */
$row = $conn->query("SELECT display_name FROM users WHERE discord_id='$id'")
            ->fetch_assoc();
$display_name = $row['display_name'] ?? "";

if($_SERVER['REQUEST_METHOD']=="POST"){
    $name = trim($_POST['name']);
    $require = "˚₊‧ ɢᴍʙ ‧₊˚";

    if(empty($name)){
        $error = "กรุณากรอกชื่อ";
    }
    else if(!str_contains($name, $require)){
        $error = "ชื่อต้องมีคำว่า $require";
    }
    else{
        $conn->query("
            UPDATE users 
            SET display_name='$name', status='pending'
            WHERE discord_id='$id'
        ");
        header("Location: wait.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Set Name</title>
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
    background:url("https://i.imgur.com/8bKQXQy.png");
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
h2 { margin-bottom:10px; }
p { font-size:14px; color:#94a3b8; }

.input {
    width:100%;
    height:54px;
    background:#0f172a;
    border:none;
    border-radius:16px;
    padding:0 16px;
    color:white;
    font-size:16px;
    line-height:54px;
    outline:none;
    margin-bottom:14px;
}

.btn {
    width:100%;
    height:54px;
    border:none;
    border-radius:16px;
    background:#6366f1;
    color:white;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}

.btn:hover{
    transform:scale(1.03);
}

.error {
    margin-top:10px;
    color:#ef4444;
    font-size:13px;
}
.tip {
    margin-top:15px;
    font-size:12px;
    color:#22c55e;
}
</style>
</head>
<body>

<div class="bg"></div>

<div class="container">
<div class="card">

<h2>ตั้งชื่อในระบบ</h2>
<p>ต้องมีคำว่า <strong>˚₊‧ ɢᴍʙ ‧₊˚</strong></p>

<form method="post">
<input class="input" 
name="name" 
value="<?= htmlspecialchars($display_name) ?>"
placeholder="˚₊‧ ɢᴍʙ ‧₊˚ Hight">

<button class="btn">บันทึก</button>
</form>

<div class="error"><?= $error ?></div>
<div class="tip">หลังบันทึกต้องรอแอดมินอนุมัติ</div>

</div>
</div>

</body>
</html>
