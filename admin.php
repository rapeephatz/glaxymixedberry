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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Inter;
    background:radial-gradient(circle at top,#1e1b4b,#020617);
    color:white;
    display:flex;
    min-height:100vh;
}

/* ================= SIDEBAR ================= */
.sidebar{
    width:240px;
    background:linear-gradient(180deg,#020617,#0f172a);
    padding:28px 24px;
    box-shadow:0 0 40px rgba(0,0,0,.8);
}
.sidebar h2{
    margin:0 0 24px;
    font-size:18px;
    letter-spacing:1px;
}
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    color:#94a3b8;
    text-decoration:none;
    padding:12px 14px;
    border-radius:12px;
    margin-bottom:10px;
    transition:.2s;
}
.sidebar a:hover{
    background:#1e293b;
    color:white;
}
.logout-btn{
    margin-top:20px;
    background:#7f1d1d;
    color:white !important;
}
.logout-btn:hover{background:#991b1b}

/* ================= MAIN ================= */
.main{
    flex:1;
    padding:40px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:40px;
}
.header h1{
    margin:0;
    font-size:28px;
}

/* ================= STATS ================= */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:40px;
}
.stat-box{
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(16px);
    border-radius:20px;
    padding:24px;
    box-shadow:0 20px 60px rgba(0,0,0,.6);
}
.stat-box h3{
    margin:0;
    font-size:14px;
    color:#94a3b8;
}
.stat-box p{
    margin:10px 0 0;
    font-size:34px;
    font-weight:700;
}

/* ================= HUB ================= */
.hub{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:24px;
}
.hub-card{
    background:linear-gradient(135deg,#1e293b,#0f172a);
    border-radius:24px;
    padding:30px;
    text-decoration:none;
    color:white;
    box-shadow:0 30px 80px rgba(0,0,0,.7);
    transition:.25s;
}
.hub-card:hover{
    transform:translateY(-6px);
    box-shadow:0 40px 120px rgba(0,0,0,.9);
}
.hub-icon{
    font-size:34px;
    margin-bottom:14px;
}
.hub-title{
    font-size:18px;
    font-weight:600;
}
.hub-desc{
    margin-top:6px;
    font-size:13px;
    color:#94a3b8;
}

/* ================= RESPONSIVE ================= */
@media(max-width:900px){
    body{flex-direction:column}
    .sidebar{width:100%;display:flex;gap:10px;overflow-x:auto}
    .sidebar h2{display:none}
    .sidebar a{white-space:nowrap}
}

/* ================= MOBILE / TABLET ================= */
@media(max-width:1024px){
    .main{
        padding:24px;
    }
}

@media(max-width:768px){
    body{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        display:flex;
        gap:8px;
        padding:16px;
        overflow-x:auto;
    }

    .sidebar a{
        flex:0 0 auto;
        padding:10px 14px;
        font-size:14px;
    }

    .header h1{
        font-size:22px;
    }

    .stat-box p{
        font-size:26px;
    }

    .hub{
        grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
        gap:16px;
    }

    .hub-card{
        padding:22px;
    }
}

@media(max-width:480px){
    .hub{
        grid-template-columns:1fr;
    }

    .sidebar{
        justify-content:flex-start;
    }
}


</style>
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <h2>⚙️ ADMIN</h2>

    <?php if($role === 'admin'): ?>
        <a href="admin.php">📊 Dashboard</a>
    <?php endif; ?>

    <a href="dashboard.php">🏠 หน้าหลัก</a>
    <a href="users.php">👥 สมาชิก</a>
    <a href="report.php">📄 Report</a>

    <a href="logout.php" class="logout-btn">🚪 Logout</a>
</div>


<!-- ================= MAIN ================= -->
<div class="main">

<div class="header">
    <h1>Dashboard</h1>
</div>

<!-- ===== STATS ===== -->
<div class="stats">
    <div class="stat-box">
        <h3>สมาชิกทั้งหมด</h3>
        <p id="u">0</p>
    </div>
    <div class="stat-box">
        <h3>สมาชิกวันนี้เช็คชื่อไปแล้ว</h3>
        <p id="t">0</p>
    </div>
    <div class="stat-box">
        <h3>ตรวจสอบสมาชิกเช็คชื่อไปแล้ว</h3>
        <p id="c">0</p>
    </div>
</div>

<!-- ===== HUB BUTTONS ===== -->
<div class="hub">

    <a href="report.php" class="hub-card">
        <div class="hub-icon">📊</div>
        <div class="hub-title">Report</div>
        <div class="hub-desc">ดูสรุปการเช็คชื่อ</div>
    </a>

    <a href="users.php" class="hub-card">
        <div class="hub-icon">👥</div>
        <div class="hub-title">Users</div>
        <div class="hub-desc">จัดการสมาชิก</div>
    </a>

    <?php if($role === 'admin'): ?>
    <a href="admin.php" class="hub-card">
        <div class="hub-icon">⚙️</div>
        <div class="hub-title">Admin</div>
        <div class="hub-desc">ระบบผู้ดูแล</div>
    </a>
    <?php endif; ?>

    <a href="logout.php" class="hub-card">
        <div class="hub-icon">🚪</div>
        <div class="hub-title">Logout</div>
        <div class="hub-desc">ออกจากระบบ</div>
    </a>

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
