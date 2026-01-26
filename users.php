<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$id = $user['id'];

if(!in_array($_SESSION['user']['role'], ['admin','staff'])){
    exit("No permission");
}

$myrole = $_SESSION['user']['role'];
$search = $_GET['q'] ?? "";

/* ⭐ ดึง photo มาด้วย */
$sql = "
SELECT 
u.discord_id,
u.username,
u.display_name,
u.role,
u.status,
COUNT(c.id) AS total,
MAX(c.photo) AS photo
FROM users u
LEFT JOIN checkins c ON u.discord_id = c.discord_id
WHERE u.username LIKE '%$search%'
GROUP BY 
u.discord_id,
u.username,
u.display_name,
u.role,
u.status
ORDER BY total DESC
";

$result = $conn->query($sql);

if(!$result){
    die("SQL ERROR: ".$conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>User Management</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Inter;
    background:linear-gradient(135deg,#0f172a,#020617);
    color:white;
}
.wrapper{padding:50px;}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}
.header h1{margin:0;font-size:28px;}
.search{
    background:#1e293b;
    border-radius:12px;
    padding:8px 14px;
}
.search input{
    background:none;
    border:none;
    outline:none;
    color:white;
}
.card{
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(14px);
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 40px 80px rgba(0,0,0,.5);
}
table{width:100%;border-collapse:collapse;}
th{
    text-align:left;
    padding:16px;
    color:#94a3b8;
    font-weight:600;
}
td{
    padding:16px;
    border-top:1px solid rgba(255,255,255,0.05);
}
tr:hover{background:rgba(255,255,255,0.03);}
.role select{
    background:#0f172a;
    color:white;
    border:none;
    padding:6px 10px;
    border-radius:8px;
}
.btn{
    padding:6px 12px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    margin-right:6px;
    display:inline-block;
    cursor:pointer;
}
.reset{background:#f59e0b;color:black}
.delete{background:#ef4444;color:white}
.save{background:#22c55e;color:black}
.back{
    display:inline-block;
    margin-top:20px;
    color:#94a3b8;
    text-decoration:none;
}
.back:hover{color:white}
.badge{
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
}
.badge.admin{background:#6366f1}
.badge.staff{background:#22c55e}
.badge.user{background:#64748b}
a.photo-link{color:#38bdf8;text-decoration:none;}
a.photo-link:hover{text-decoration:underline;}
</style>
</head>
<body>

<div class="wrapper">

<div class="header">
    <h1>👥 User Management</h1>
    <form class="search">
        <input name="q" placeholder="Search user..." value="<?=htmlspecialchars($search)?>">
    </form>
</div>

<div class="card">
<table>
<tr>
<th>Username</th>
<th>Role</th>
<th>Total</th>
<th>Photo</th> <!-- ⭐ -->
<th>Status</th>
<th>Action</th>
</tr>

<?php while($u=$result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($u['display_name'] ?: $u['username']) ?></td>

<td>
<?php if($myrole=='admin'): ?>
<form action="update_role.php" method="post" class="role">
<input type="hidden" name="id" value="<?= $u['discord_id'] ?>">
<select name="role">
<option value="user" <?= $u['role']=='user'?'selected':'' ?>>user</option>
<option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>staff</option>
<option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>admin</option>
</select>
<button class="btn save">Save</button>
</form>
<?php else: ?>
<span class="badge <?= $u['role'] ?>"><?= $u['role'] ?></span>
<?php endif; ?>
</td>

<td><?= $u['total'] ?></td>

<!-- ⭐ ดูรูป -->
<td>
<?php if($u['photo']): ?>
<a href="#" class="photo-link"
onclick="openPhoto('uploads/<?= $u['photo'] ?>')">
<?= $u['photo'] ?>
</a>
<?php else: ?>
-
<?php endif; ?>
</td>

<td><?= $u['status'] ?></td>

<td>
<?php if($myrole=='admin'): ?>
<a class="btn reset" href="reset.php?id=<?=$u['discord_id']?>">Reset</a>
<a class="btn delete" href="delete.php?id=<?=$u['discord_id']?>"
onclick="return confirm('ลบ checkin ทั้งหมด?')">Delete</a>
<?php if($u['status']=='pending'): ?>
<a class="btn save" href="approve.php?id=<?=$u['discord_id']?>">Approve</a>
<?php endif; ?>
<?php else: ?>
👀 View only
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<a href="admin.php" class="back">⬅ Back to Dashboard</a>

</div>

<!-- ⭐ POPUP รูป -->
<div id="photoPopup" style="
display:none;
position:fixed;
top:0;left:0;right:0;bottom:0;
background:rgba(0,0,0,0.8);
justify-content:center;
align-items:center;
z-index:999;
">
  <div style="background:#111;padding:20px;border-radius:16px;text-align:center">
    <img id="popupImg" style="max-width:600px;border-radius:12px">
    <br><br>
    <button onclick="closePhoto()" class="btn reset">Close</button>
  </div>
</div>

<script>
function openPhoto(src){
  document.getElementById('popupImg').src = src;
  document.getElementById('photoPopup').style.display = 'flex';
}
function closePhoto(){
  document.getElementById('photoPopup').style.display = 'none';
}
</script>

</body>
</html>
