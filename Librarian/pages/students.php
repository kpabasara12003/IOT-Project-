<?php 
include ('../components/auth_check.php');
include('../config/db.php');

if (isset($_POST['add_student'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $student_number = $_POST['student_number'];
    $nfc_uid = $_POST['nfc_uid'];

    $stmt = $conn->prepare("
        INSERT INTO students (full_name, email, student_number, nfc_uid)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("ssss", $name, $email, $student_number, $nfc_uid);
    $stmt->execute();

    echo "<script>alert('Student added'); window.location='students.php';</script>";
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $conn->query("DELETE FROM students WHERE student_id = $id");

    echo "<script>window.location='students.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h3>Add Student</h3>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="student_number" placeholder="Student Number" required>
            <input type="text" name="nfc_uid" placeholder="Scan NFC" required>

            <button type="submit" name="add_student">Add Student</button>
        </form>

        <h3>All Students</h3>

        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Student No</th>
                <th>NFC UID</th>
                <th>Actions</th>
            </tr>

        <?php 
        $result = $conn->query("SELECT * FROM students");

        while($row = $result->fetch_assoc()):
        ?>

        <tr>
            <td><?= $row['full_name'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['student_number'] ?></td>
            <td><?= $row['nfc_uid'] ?></td>
            <td>
                <a href="edit_student.php?id=<?= $row['student_id'] ?>">Edit</a>

                <a href="students.php?delete=<?= $row['student_id'] ?>">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>
</body>
</html>