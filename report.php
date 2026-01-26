<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$search = $_GET['q'] ?? "";

$sql = "
SELECT 
u.display_name,
u.username,
COUNT(c.id) AS total
FROM users u
LEFT JOIN checkins c ON u.discord_id = c.discord_id
GROUP BY 
u.display_name,
u.username
ORDER BY total DESC
";


$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Leaderboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Inter;
    background:linear-gradient(135deg,#0f172a,#020617);
    color:white;
}
.wrapper{padding:50px}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}
.search{
    background:#1e293b;
    padding:10px 16px;
    border-radius:14px;
}
.search input{
    background:none;
    border:none;
    color:white;
    outline:none;
}
.card{
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(16px);
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 40px 80px rgba(0,0,0,.6);
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{padding:18px}
th{
    color:#94a3b8;
    text-align:left;
}
tr{
    border-top:1px solid rgba(255,255,255,0.05);
}
tr:hover{
    background:rgba(255,255,255,0.04);
}
.rank{
    font-weight:700;
    font-size:18px;
}
.today{
    color:#22c55e;
    font-weight:600;
}
.back{
    display:inline-block;
    margin-top:25px;
    color:#94a3b8;
    text-decoration:none;
}
.back:hover{color:white}
</style>
</head>
<body>

<div class="wrapper">

<div class="header">
    <h1>🏆 สรุปเช็คชื่อ</h1>
    <form class="search">
        <input name="q" placeholder="ค้นหาชื่อ..." value="<?=htmlspecialchars($search)?>">
    </form>
</div>

<div class="card">
<table>
<tr>
<th>#</th>
<th>รายชื่อ</th>
<th>สถานะเช็คชื่อ</th>
<th>รอบ</th>
</tr>

<?php 
$rank=1; 
while($u=$result->fetch_assoc()): 
?>
<tr>
<td class="rank">#<?= $rank++ ?></td>
<td><?= htmlspecialchars($u['display_name'] ?: $u['username']) ?></td>
<td class="today"><?= $u['today'] ? '✔' : '-' ?></td>
<td><?= $u['total'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<a class="back" href="admin.php">⬅ Back</a>

</div>
</body>
</html>
