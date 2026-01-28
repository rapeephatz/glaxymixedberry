<?php
session_start();
include "db.php";

if(!isset($_SESSION['user']['id'])){
    header("Location: index.php");
    exit();
}

$id = $_SESSION['user']['id'];

/* ดึง role จาก DB แบบปลอดภัย */
$stmt = $conn->prepare("SELECT role FROM users WHERE discord_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    session_destroy();
    header("Location: index.php");
    exit();
}

$data = $result->fetch_assoc();
$role = $data['role'] ?? 'user';

/* sync session */
$_SESSION['user']['role'] = $role;

/* ❌ ไม่ใช่ admin / staff → popup */
if(!in_array($role, ['admin','staff'])){
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>No Permission</title>
<style>
body{
    margin:0;
    background:linear-gradient(135deg,#0f172a,#020617);
    font-family:Segoe UI;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
}
.modal{
    background:rgba(255,255,255,0.06);
    backdrop-filter:blur(16px);
    padding:40px 50px;
    border-radius:24px;
    text-align:center;
    box-shadow:0 40px 80px rgba(0,0,0,.6);
    animation:pop .5s ease;
    width:380px;
}
@keyframes pop{
    from{transform:scale(.8);opacity:0}
    to{transform:scale(1);opacity:1}
}
.icon{font-size:50px;margin-bottom:10px}
h2{margin:0}
p{color:#94a3b8;font-size:14px}
.btn{
    margin-top:25px;
    padding:12px 24px;
    border:none;
    border-radius:14px;
    background:#ef4444;
    color:white;
    font-size:15px;
    cursor:pointer;
}
.btn:hover{transform:scale(1.05)}
</style>
</head>
<body>

<div class="modal">
    <div class="icon">🚫</div>
    <h2>ไม่มีสิทธิ์เข้าใช้งาน</h2>
    <p>หน้านี้สำหรับแอดมินหรือสตาฟเท่านั้น</p>
    <button class="btn" onclick="location.href='dashboard.php'">
        กลับหน้าหลัก
    </button>
</div>

<script>
setTimeout(()=>{
    location.href="dashboard.php";
},5000);
</script>

</body>
</html>
<?php
exit();
}
