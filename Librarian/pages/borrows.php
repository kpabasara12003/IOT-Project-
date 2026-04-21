<?php 
include('../components/auth_check.php');
include('../config/db.php');

if (isset($_POST['borrow_book'])) {
    $student_id = $_POST['student_id'];
    $copy_id = $_POST['copy_id'];
    $due_date = $_POST['due_date'];

    // insert burrow record
    $stmt = $conn->prepare("
        INSERT INTO borrows (copy_id, student_id, due_date)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $copy_id, $student_id, $due_date);
    $stmt->execute();

    // update book status
    $stmt2 = $conn->prepare("
        UPDATE book_copies
        SET status = 'borrowed'
        WHERE copy_id = ?
    ");
    $stmt2->bind_param("i", $copy_id);
    $stmt2->execute();

    echo "<script>alert('Book Borrowed'); window.location='borrows.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <style>
        /* Modern Reset & Theme Consistency */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background-color: #F2E8CF; color: #386641; padding-bottom: 50px; }

        .content { max-width: 1100px; margin: 40px auto; padding: 20px; }

        h3 { 
            color: #386641; 
            margin-bottom: 25px; 
            font-size: 1.6rem; 
            border-left: 6px solid #A7C957; 
            padding-left: 15px;
            text-transform: uppercase;
        }

        /* Borrowing Form Box */
        form {
            background: #ffffff;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(56, 102, 65, 0.08);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 50px;
            border-top: 5px solid #386641;
        }

        .form-group { display: flex; flex-direction: column; gap: 8px; }
        label { font-weight: 600; font-size: 0.9rem; color: #6A994E; }

        form select, form input {
            padding: 12px;
            border: 2px solid #F2E8CF;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
            background-color: #fff;
        }

        form select:focus, form input:focus { border-color: #6A994E; }

        /* Button spans full width */
        button[name="borrow_book"] {
            grid-column: span 2;
            background-color: #386641;
            color: white;
            border: none;
            padding: 15px;
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
            cursor: pointer;
            border-radius: 8px;
            margin-top: 10px;
            transition: 0.3s;
        }

        button[name="borrow_book"]:hover { background-color: #6A994E; transform: translateY(-2px); }

        /* Floating Row Table Style */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
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
            padding: 15px;
            color: #386641;
            font-weight: 500;
            border-top: 1px solid #F2E8CF;
            border-bottom: 1px solid #F2E8CF;
        }

        td:first-child { border-left: 1px solid #F2E8CF; border-radius: 10px 0 0 10px; }
        td:last-child { border-right: 1px solid #F2E8CF; border-radius: 0 10px 10px 0; }

        /* Status & Date Highlights */
        .due-date { color: #BC4749; font-weight: bold; }
        .status-pill {
            background-color: #A7C957;
            color: #386641;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            form { grid-template-columns: 1fr; }
            button[name="borrow_book"] { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h3>