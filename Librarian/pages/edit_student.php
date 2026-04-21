<?php
include('../config/db.php');
include('../components/auth_check.php');

/* -------------------------
   GET STUDENT ID
--------------------------*/
if (!isset($_GET['id'])) {
    header("Location: students.php");
    exit;
}

$student_id = $_GET['id'];

/* -------------------------
   FETCH STUDENT DATA
--------------------------*/
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

/* -------------------------
   UPDATE LOGIC
--------------------------*/
if (isset($_POST['update_student'])) {

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $student_number = $_POST['student_number'];
    $nfc_uid = $_POST['nfc_uid'];

    $stmt = $conn->prepare("
        UPDATE students 
        SET full_name=?, email=?, student_number=?, nfc_uid=?
        WHERE student_id=?
    ");

    $stmt->bind_param(
        "ssssi",
        $full_name,
        $email,
        $student_number,
        $nfc_uid,
        $student_id
    );

    $stmt->execute();

    echo "<script>
        alert('Student updated successfully');
        window.location='students.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <?php include('../components/navbar.php'); ?>

    <div class="content">

        <h2>✏️ Edit Student</h2>

        <form method="POST">

            <input type="text" name="full_name"
                value="<?= $student['full_name'] ?>"
                placeholder="Full Name" required>

            <input type="email" name="email"
                value="<?= $student['email'] ?>"
                placeholder="Email" required>

            <input type="text" name="student_number"
                value="<?= $student['student_number'] ?>"
                placeholder="Student Number" required>

            <input type="text" name="nfc_uid"
                value="<?= $student['nfc_uid'] ?>"
                placeholder="NFC UID" required>

            <br><br>

            <button type="submit" name="update_student">
                Update Student
            </button>

        </form>

    </div>

</body>

</html>