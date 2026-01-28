<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$id = $_SESSION['user']['id'];

$row = $conn->query("
    SELECT display_name,status 
    FROM users 
    WHERE discord_id='$id'
")->fetch_assoc();

if(empty($row['display_name'])){
    header("Location: setname.php");
    exit();
}

if($row['status']=='approved'){
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Waiting Approval</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}

body{
    margin:0;
    font-family:Inter,sans-serif;
    background:linear-gradient(135deg,#0f172a,#020617);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
    color:white;
}

.card{
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(16px);
    border-radius:24px;
    padding:50px 60px;
    text-align:center;
    box-shadow:0 40px 80px rgba(0,0,0,.5);
    width:100%;
    max-width:420px;
}

.icon{
    font-size:48px;
    margin-bottom:20px;
}

h1{
    margin:0;
    font-size:26px;
    font-weight:600;
}

p{
    margin-top:10px;
    color:#94a3b8;
}

.name{
    margin-top:8px;
    font-size:15px;
    color:#22c55e;
    word-break:break-word;
}

.loader{
    margin:30px auto 10px;
    width:50px;
    height:50px;
    border-radius:50%;
    border:4px solid rgba(255,255,255,.2);
    border-top:4px solid #6366f1;
    animation:spin 1s linear infinite;
}

@keyframes spin{
    to{transform:rotate(360deg);}
}

.small{
    margin-top:20px;
    font-size:13px;
    color:#64748b;
}

.logout{
    display:inline-block;
    margin-top:30px;
    color:#ef4444;
    text-decoration:none;
    font-size:14px;
}
.logout:hover{opacity:.8}

/* 📱 Mobile */
@media (max-width:480px){
    .card{
        padding:36px 26px;
    }
    h1{
        font-size:22px;
    }
    .icon{
        font-size:42px;
    }
}

/* 📲 iPad */
@media (min-width:768px) and (max-width:1024px){
    .card{
        max-width:460px;
        padding:48px 50px;
    }
}
</style>
</head>
<body>

<div class="card">
    <div class="icon">⏳</div>
    <h1>รอการอนุมัติจากแอดมิน</h1>
    <p>ชื่อของคุณถูกส่งไปแล้ว</p>

    <div class="name"><?= htmlspecialchars($row['display_name']) ?></div>

    <div class="loader"></div>

    <div class="small">
        ระบบจะรีเฟรชอัตโนมัติทุก 5 วินาที
    </div>

    <a class="logout" href="logout.php">Logout</a>
</div>

<meta http-equiv="refresh" content="5">
</body>
</html>
