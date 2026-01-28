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

/* ⭐ ดึงรูปล่าสุดของแต่ละ user */
$sql = "
SELECT 
u.discord_id,
u.username,
u.display_name,
u.role,
u.status,
COUNT(c.id) AS total,
(
  SELECT photo 
  FROM checkins c2 
  WHERE c2.discord_id = u.discord_id 
  ORDER BY c2.time DESC 
  LIMIT 1
) AS photo
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Inter,sans-serif;
    background:linear-gradient(135deg,#0f172a,#020617);
    color:white;
}
.wrapper{
    padding:40px;
    max-width:1200px;
    margin:auto;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    flex-wrap:wrap;
    gap:12px;
}
.header h1{
    margin:0;
    font-size:28px;
}
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
    overflow:auto;
    box-shadow:0 40px 80px rgba(0,0,0,.5);
}

/* table */
table{
    width:100%;
    border-collapse:collapse;
    min-width:720px;
}
th{
    text-align:left;
    padding:16px;
    color:#94a3b8;
    font-weight:600;
    font-size:14px;
}
td{
    padding:16px;
    border-top:1px solid rgba(255,255,255,0.05);
    vertical-align:middle;
    font-size:14px;
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

.thumb{
    width:52px;
    height:52px;
    object-fit:cover;
    border-radius:10px;
    cursor:pointer;
    border:2px solid #334155;
}

/* 📱 Mobile */
@media (max-width:768px){
    .wrapper{
        padding:20px;
    }
    .header h1{
        font-size:22px;
    }
    th,td{
        padding:12px;
        font-size:13px;
    }
    .btn{
        margin-bottom:6px;
    }
}
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
<th>Photo</th>
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

<!-- ⭐ รูปจาก Cloudinary -->
<td>
<?php if($u['photo']): 
    $thumb = str_replace(
        "/upload/",
        "/upload/w_200,h_200,c_fill/",
        $u['photo']
    );
?>
<img src="<?= $thumb ?>" class="thumb"
onclick="openPhoto('<?= $u['photo'] ?>')">
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
inset:0;
background:rgba(0,0,0,0.85);
justify-content:center;
align-items:center;
z-index:999;
">
  <div style="background:#020617;padding:20px;border-radius:20px;text-align:center">
    <img id="popupImg" style="max-width:80vw;max-height:80vh;border-radius:16px">
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
