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
    <title>Students Management</title>
    <style>
        /* Unified Theme Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background-color: #F2E8CF; color: #386641; padding-bottom: 50px; }

        .content { max-width: 1000px; margin: 40px auto; padding: 20px; }

        h3 { 
            color: #386641; 
            margin-bottom: 20px; 
            font-size: 1.5rem; 
            border-left: 5px solid #A7C957; 
            padding-left: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Form Styling */
        form {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 50px;
        }

        form input {
            padding: 12px;
            border: 2px solid #F2E8CF;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }

        form input:focus { border-color: #6A994E; }

        /* Full width for NFC scan and Button */
        input[name="nfc_uid"], button[type="submit"] {
            grid-column: span 2;
        }

        /* Special focus for NFC scan field */
        input[name="nfc_uid"] {
            border: 2px solid #386641;
            background-color: #fafff0;
        }

        button[type="submit"] {
            background-color: #386641;
            color: white;
            border: none;
            padding: 15px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            border-radius: 8px;
            margin-top: 10px;
            transition: 0.3s;
        }

        button[type="submit"]:hover { background-color: #6A994E; }

        /* Creative Table Layout */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            margin-top: 10px;
        }

        th {
            background-color: #386641;
            color: #F2E8CF;
            padding: 15px;
            text-align: left;
            text-transform: uppercase;
            font-size: 13px;
        }

        th:first-child { border-radius: 10px 0 0 10px; }
        th:last-child { border-radius: 0 10px 10px 0; }

        td {
            background-color: #ffffff;
            padding: 18px 15px;
            color: #386641;
            font-weight: 500;
            border-top: 1px solid #F2E8CF;
            border-bottom: 1px solid #F2E8CF;
        }

        td:first-child { border-left: 1px solid #F2E8CF; border-radius: 10px 0 0 10px; }
        td:last-child { border-right: 1px solid #F2E8CF; border-radius: 0 10px 10px 0; }

        /* Row hover effect */
        tr:hover td { 
            background-color: #f9fdf5; 
            border-color: #A7C957;
            transform: translateY(-2px);
            transition: 0.2s;
        }

        /* Delete Action styling */
        .delete-btn {
            color: #BC4749;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            padding: 6px 12px;
            border: 1px solid #BC4749;
            border-radius: 6px;
            transition: 0.3s;
        }

        .delete-btn:hover {
            background-color: #BC4749;
            color: white;
        }

        /* NFC code styling */
        code {
            background: #F2E8CF;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #386641;
        }

        @media (max-width: 600px) {
            form { grid-template-columns: 1fr; }
            input[name="nfc_uid"], button[type="submit"] { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h3>Add New Student</h3>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Student Email" required>
            <input type="text" name="student_number" placeholder="Student ID Number" required>
            <input type="text" name="nfc_uid" placeholder="Scan Student NFC Card Here" required>

            <button type="submit" name="add_student">Register Student</button>
        </form>

        <h3>Registered Students</h3>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student No</th>
                    <th>NFC UID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $result = $conn->query("SELECT * FROM students");
                while($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><strong><?= $row['full_name'] ?></strong></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['student_number'] ?></td>
                    <td><code><?= $row['nfc_uid'] ?></code></td>
                    <td>
                        <a href="students.php?delete=<?= $row['student_id'] ?>" class="delete-btn" onclick="return confirm('Remove this student?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>