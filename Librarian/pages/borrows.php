<?php 
include('../components/auth_check.php');
include('../config/db.php');

if (isset($_POST['borrow_book'])) {
    $student_id = $_POST['student_id'];
    $copy_id = $_POST['copy_id'];
    $due_date = $_POST['due_date'];

    //insert burrow record
    $stmt = $conn->prepare("
        INSERT INTO borrows (copy_id, student_id, due_date)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $copy_id, $student_id, $due_date);
    $stmt->execute();

    //update book status
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