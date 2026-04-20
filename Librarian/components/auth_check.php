<?php 
session_start();

if (!isset($_SESSION['librarian_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>