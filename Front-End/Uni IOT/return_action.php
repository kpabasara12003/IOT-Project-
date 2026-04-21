<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['borrow_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$borrow_id = intval($_POST['borrow_id']);

$loc_sql = "SELECT 
                bc.copy_id,
                b.title,
                f.floor_name,
                sec.section_code,
                sh.shelf_code,
                sr.row_position,
                DATEDIFF(CURRENT_DATE, br.due_date) AS overdue_days
            FROM borrows br
            JOIN book_copies bc ON br.copy_id = bc.copy_id
            JOIN books b ON bc.book_id = b.book_id
            JOIN shelf_rows sr ON bc.row_id = sr.row_id
            JOIN bookshelves sh ON sr.shelf_id = sh.shelf_id
            JOIN sections sec ON sh.section_id = sec.section_id
            JOIN library_floors f ON sec.floor_id = f.floor_id
            WHERE br.borrow_id = ? AND br.student_id = ? AND br.returned_at IS NULL";

$loc_stmt = $conn->prepare($loc_sql);
$loc_stmt->bind_param("ii", $borrow_id, $student_id);
$loc_stmt->execute();
$loc_result = $loc_stmt->get_result();

if ($loc_result->num_rows === 0) {
    die("Invalid return request or book already returned.");
}

$book_data = $loc_result->fetch_assoc();
$copy_id = $book_data['copy_id'];

$conn->begin_transaction();

try {
    $update_borrow = $conn->prepare("UPDATE borrows SET returned_at = NOW() WHERE borrow_id = ?");
    $update_borrow->bind_param("i", $borrow_id);
    $update_borrow->execute();

    $update_copy = $conn->prepare("UPDATE book_copies SET status = 'available' WHERE copy_id = ?");
    $update_copy->bind_param("i", $copy_id);
    $update_copy->execute();

    if ($book_data['overdue_days'] > 0) {
        $fine_amount = $book_data['overdue_days'] * 20;
        
        $ledger_stmt = $conn->prepare("SELECT account_id, balance, type FROM student_accounts WHERE student_id = ?");
        $ledger_stmt->bind_param("i", $student_id);
        $ledger_stmt->execute();
        $ledger_result = $ledger_stmt->get_result();
        
        $current_credit = 0.0;
        if ($ledger_row = $ledger_result->fetch_assoc()) {
            $current_balance = (float)$ledger_row['balance'];
            $current_credit = (strtolower($ledger_row['type']) === 'fine') ? -$current_balance : $current_balance;
        }

        $new_credit = $current_credit - $fine_amount;
        $new_type = ($new_credit < 0) ? 'fine' : 'credit';
        $new_balance = abs($new_credit);

        if ($ledger_result->num_rows > 0) {
            $update_account = $conn->prepare("UPDATE student_accounts SET balance = ?, type = ? WHERE student_id = ?");
            $update_account->bind_param("dsi", $new_balance, $new_type, $student_id);
            $update_account->execute();
        } else {
            $insert_account = $conn->prepare("INSERT INTO student_accounts (student_id, balance, type) VALUES (?, ?, ?)");
            $insert_account->bind_param("ids", $student_id, $new_balance, $new_type);
            $insert_account->execute();
        }
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die("Return failed: " . $e->getMessage());
}

$location_string = $book_data['floor_name'] . " / Section " . $book_data['section_code'] . 
                   " / Shelf " . $book_data['shelf_code'] . " / Row: " . $book_data['row_position'];
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Return Success</title>
  <link rel="stylesheet" href="return_success.css" />
</head>
<body>
  <main class="panel">
    <section class="panel__body">
      <h1 class="title">Book Returned<br>Successfully!!</h1>
      <p class="sub">Please return "<strong><?php echo htmlspecialchars($book_data['title']); ?></strong>" to the following location:</p>

      <div class="location-box" style="background-color: #B0C4B1; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <div class="loc-text" style="font-weight: bold; color: #333;">
            <?php echo $location_string; ?>
        </div>
      </div>

      <button class="btn btn--done" type="button" onclick="window.location.href='index.php'">
        Done
      </button>
    </section>
  </main>
</body>
</html>