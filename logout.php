<?php
session_start();
session_destroy();
header("Location: index.php"); // หรือหน้า login ของคุณ
exit();
