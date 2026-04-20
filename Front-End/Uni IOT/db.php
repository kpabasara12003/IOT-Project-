<?php
$host = "168.144.22.111";
$user = "exadmin";
$pass = 'adix123@ZXCVBNM';
$db   = "LBMS";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>