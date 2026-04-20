<?php
$host = "168.144.22.111";
$user = "user";
$pass = 'R9!xT4#kQ7@vL2$z';
$db   = "LBMS";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>


