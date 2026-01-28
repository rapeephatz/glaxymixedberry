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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Attendance</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}

body{
    margin:0;
    font-family:'Inter',sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
        linear-gradient(120deg,
        #ff004c,
        #7c00ff,
        #00d4ff,
        #00ff85);
    background-size:300% 300%;
    animation:bgMove 10s ease infinite;
    padding:20px;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

/* RGB border */
.rgb-border{
    padding:3px;
    border-radius:24px;
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
    width:100%;
    max-width:380px;
}

@keyframes borderMove{
    0%{background-position:0%}
    100%{background-position:400%}
}

/* card */
.card{
    background:#0f172a;
    color:white;
    padding:40px 34px;
    border-radius:22px;
    width:100%;
    text-align:center;
    box-shadow:0 40px 90px rgba(0,0,0,.7);
}

.card img.logo{
    width:78px;
    margin-bottom:20px;
    filter:drop-shadow(0 0 12px #5865F2);
}

.card h1{
    margin:0;
    font-size:22px;
}

.card p{
    color:#94a3b8;
    margin:12px 0 30px;
    font-size:14px;
}

/* button */
.btn-discord{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#5865F2;
    color:white;
    text-decoration:none;
    padding:14px;
    border-radius:14px;
    font-weight:600;
    transition:.2s;
    box-shadow:0 0 18px rgba(88,101,242,.6);
}

.btn-discord:hover{
    transform:scale(1.05);
    box-shadow:0 0 28px rgba(88,101,242,.9);
}

/* ===== Mobile ===== */
@media (max-width:480px){
    .card{
        padding:32px 24px;
    }
    .card img.logo{
        width:64px;
    }
    .card h1{
        font-size:20px;
    }
    .card p{
        font-size:13px;
    }
    .btn-discord{
        padding:13px;
        font-size:14px;
    }
}

/* ===== Tablet ===== */
@media (min-width:768px) and (max-width:1024px){
    .rgb-border{
        max-width:420px;
    }
    .card{
        padding:44px 38px;
    }
}
</style>
</head>
<body>

<div class="rgb-border">
    <div class="card">
        <img class="logo" src="https://cdn-icons-png.flaticon.com/512/2111/2111370.png">
        <h1>Galaxy Mixed Berry</h1>
        <p>เข้าสู่ระบบด้วย Discord เพื่อเช็คชื่อ</p>

        <a class="btn-discord" href="<?= $auth_url ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/2111/2111370.png" width="22">
            Login with Discord
        </a>
    </div>
</div>

</body>
</html>
