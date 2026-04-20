<?php 
session_start();
include('../config/db.php');

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM librarians WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $password === $user['password']) {
    $_SESSION['librarian_id'] = $user['librarian_id'];
    $_SESSION['name'] = $user['name'];

    header("Location: ../pages/dashboard.php");
    exit;
} else {
    echo "Invalid login";
}
?>