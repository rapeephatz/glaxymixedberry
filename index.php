<?php
$client_id = "1464996907016524048";
$redirect_uri = "https://glaxymixedberry.onrender.com/callback.php";

$auth_url = "https://discord.com/api/oauth2/authorize"
          . "?client_id=" . $client_id
          . "&redirect_uri=" . urlencode($redirect_uri)
          . "&response_type=code"
          . "&scope=identify";
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Login | Attendance</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    margin:0;
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg,#5865F2,#2C2F33);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card {
    background:#fff;
    padding:40px;
    border-radius:16px;
    width:360px;
    text-align:center;
    box-shadow:0 20px 40px rgba(0,0,0,.2);
}

.card img {
    width:80px;
    margin-bottom:20px;
}

.card h1 {
    margin:0;
    font-size:22px;
}

.card p {
    color:#666;
    margin:10px 0 30px;
}

.btn-discord {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#5865F2;
    color:white;
    text-decoration:none;
    padding:14px;
    border-radius:10px;
    font-weight:600;
    transition:.2s;
}

.btn-discord:hover {
    background:#4752c4;
    transform:translateY(-2px);
}
</style>
</head>
<body>

<div class="card">
    <img src="https://cdn-icons-png.flaticon.com/512/2111/2111370.png">
    <h1>Attendance System</h1>
    <p>เข้าสู่ระบบด้วย Discord เพื่อเช็คชื่อ</p>

    <a class="btn-discord" href="<?= $auth_url ?>">
        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111370.png" width="22">
        Login with Discord
    </a>
</div>

</body>
</html>
