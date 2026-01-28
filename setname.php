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
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set Name</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}

body{
    margin:0;
    min-height:100vh;
    font-family:'Inter',sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    background:
        linear-gradient(120deg,
        #ff004c,
        #7c00ff,
        #00d4ff,
        #00ff85);
    background-size:300% 300%;
    animation:bgMove 10s ease infinite;
    color:white;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

/* RGB Border */
.rgb-border{
    width:100%;
    max-width:380px;
    padding:3px;
    border-radius:26px;
    background:
        linear-gradient(90deg,
        #ff004c,
        #ffea00,
        #00ff85,
        #00d4ff,
        #7c00ff,
        #ff004c);
    background-size:400% 400%;
    animation:borderMove 6s linear infinite;
}

@keyframes borderMove{
    0%{background-position:0%}
    100%{background-position:400%}
}

/* Card */
.card{
    background:rgba(15,23,42,.92);
    border-radius:24px;
    padding:40px 34px;
    text-align:center;
    backdrop-filter:blur(14px);
    box-shadow:0 40px 90px rgba(0,0,0,.8);
    animation:pop .7s ease;
}

@keyframes pop{
    from{transform:scale(.85);opacity:0}
    to{transform:scale(1);opacity:1}
}

h2{
    margin:0 0 8px;
    font-size:22px;
}

p{
    margin:0 0 22px;
    font-size:14px;
    color:#94a3b8;
}

/* Input */
.input{
    width:100%;
    height:54px;
    background:#020617;
    border:1px solid rgba(255,255,255,.08);
    border-radius:16px;
    padding:0 16px;
    color:white;
    font-size:15px;
    outline:none;
    margin-bottom:16px;
}

.input:focus{
    border-color:#22c55e;
    box-shadow:0 0 0 2px rgba(34,197,94,.25);
}

/* Button */
.btn{
    width:100%;
    height:54px;
    border:none;
    border-radius:16px;
    background:linear-gradient(135deg,#6366f1,#22c55e);
    color:white;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.btn:hover{
    transform:scale(1.05);
    box-shadow:0 0 24px rgba(99,102,241,.7);
}

/* Info */
.error{
    margin-top:12px;
    color:#ef4444;
    font-size:13px;
}

.tip{
    margin-top:16px;
    font-size:12px;
    color:#22c55e;
}

/* Mobile */
@media (max-width:480px){
    .card{
        padding:32px 26px;
    }
    h2{font-size:20px}
    .input,.btn{height:50px}
}

/* Tablet */
@media (min-width:768px) and (max-width:1024px){
    .rgb-border{max-width:420px}
}
</style>
</head>
<body>

<div class="rgb-border">
<div class="card">

<h2>ตั้งชื่อในระบบเช็คชื่อ</h2>
<p>ต้องมีคำว่า <strong>˚₊‧ ɢᴍʙ ‧₊˚</strong></p>

<form method="post">
<input
class="input"
name="name"
value="<?= htmlspecialchars($display_name) ?>"
placeholder="˚₊‧ ɢᴍʙ ‧₊˚ Hight"
required>

<button class="btn">บันทึก</button>
</form>

<?php if(!empty($error)): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<div class="tip">หลังบันทึกต้องรอแอดมินอนุมัติ</div>

</div>
</div>

</body>
</html>
