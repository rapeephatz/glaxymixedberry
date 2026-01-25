<?php include "auth_admin.php"; 
$id = $_SESSION['user']['id'];
$row = $conn->query("SELECT role FROM users WHERE discord_id='$id'")
            ->fetch_assoc();

$_SESSION['user']['role'] = $row['role'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {margin:0;font-family:Inter;background:#f4f6fb;display:flex;}
.sidebar{width:220px;background:#1e1f29;color:white;min-height:100vh;padding:30px}
.sidebar a{display:block;color:#ccc;text-decoration:none;padding:12px;border-radius:8px;margin-bottom:10px}
.sidebar a:hover{background:#5865F2;color:white}
.main{flex:1;padding:40px}
.stat{display:flex;gap:20px}
.box{flex:1;background:#5865F2;color:white;padding:20px;border-radius:14px}
.box h3{margin:0;font-size:14px;opacity:.8}
.box p{font-size:32px;margin:5px 0 0}
.logout-btn{
    display:inline-block;
    padding:10px 20px;
    border-radius:12px;
    background:#ef4444;
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:.2s;
}

.logout-btn:hover{
    background:#dc2626;
    transform:scale(1.05);
}

</style>
</head>
<body>


<div class="sidebar">
<h2>⚙️ จัดการ</h2>
<a href="admin.php">แดชบอร์ด</a>
<a href="dashboard.php">หน้าหลัก</a>
<a href="users.php">สมาชิก</a>
<a href="report.php">สรุปเช็คชื่อ</a>
<a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="main">
<h1>Realtime Dashboard</h1>

<div class="stat">
    <div class="box">
        <h3>Users</h3>
        <p id="u">0</p>
    </div>
    <div class="box">
        <h3>Today Check-in</h3>
        <p id="t">0</p>
    </div>
    <div class="box">
        <h3>Total Check-in</h3>
        <p id="c">0</p>
    </div>
</div>
</div>

<script>
function loadStats(){
    fetch("api_stats.php")
    .then(r=>r.json())
    .then(d=>{
        u.innerText = d.users;
        t.innerText = d.today;
        c.innerText = d.total;
    });
}
loadStats();
setInterval(loadStats,2000);
</script>
</body>
</html>
