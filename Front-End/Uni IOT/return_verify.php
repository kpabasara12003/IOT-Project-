<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$borrow_id = intval($_POST['borrow_id']);
$nfc_uid = trim($_POST['nfc_uid']);
$error = "";

$sql = "SELECT b.title, c.category_name, bc.copy_id, br.due_date,
        DATEDIFF(CURRENT_DATE, br.due_date) AS overdue_days,
        COALESCE(GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', '), 'Unknown') AS authors
        FROM borrows br
        JOIN book_copies bc ON br.copy_id = bc.copy_id
        JOIN books b ON bc.book_id = b.book_id
        JOIN book_categories c ON b.category_id = c.category_id
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        WHERE br.borrow_id = ? AND bc.nfc_uid = ? AND br.student_id = ? AND br.returned_at IS NULL
        GROUP BY b.book_id, bc.copy_id, br.borrow_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $borrow_id, $nfc_uid, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "Verification Failed: The scanned tag does not match the book you selected to return.";
} else {
    $book = $result->fetch_assoc();
    
    $account_credit = 0.0;
    $credit_stmt = $conn->prepare("SELECT balance, type FROM student_accounts WHERE student_id = ? LIMIT 1");
    $credit_stmt->bind_param("i", $student_id);
    $credit_stmt->execute();
    $credit_result = $credit_stmt->get_result();
    
    if ($credit_row = $credit_result->fetch_assoc()) {
        $balance = (float)$credit_row['balance'];
        $account_credit = (strtolower($credit_row['type']) === 'fine') ? -$balance : $balance;
    }

    $book_fine = max(0, $book['overdue_days'] * 20);
    $remaining_fine = $book_fine - $account_credit; 
    $is_blocked = ($remaining_fine > 0);
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Confirm Return</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="borrow.css" />
</head>

<body>
  <main class="panel">
    <header class="panel__header">
      <button class="btn btn--backTop" type="button" onclick="window.location.href='return_books.php';">Cancel</button>
    </header>

    <section class="panel__body">
      <h1 class="title">Confirm Return</h1>

      <?php if (!empty($error)): ?>
        <div class="alert alert--error" style="background-color: #EDAFB8; color: #fff; padding:15px; border-radius:5px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <button class="btn" style="margin-top:20px;" onclick="window.location.href='return_scan.php?borrow_id=<?php echo $borrow_id; ?>'">Try Scanning Again</button>
      <?php else: ?>
        
        <div class="alert alert--success" style="background-color: #B0C4B1; color: #fff; padding:15px; border-radius:5px; margin-bottom:20px;">
            Physical Verification Successful!
        </div>

        <div class="summary-card">
          <div class="summary-row"><span>Book Title</span><strong><?php echo htmlspecialchars($book['title']); ?></strong></div>
          <div class="summary-row"><span>Author(s)</span><strong><?php echo htmlspecialchars($book['authors']); ?></strong></div>
          <div class="summary-row"><span>Category</span><strong><?php echo htmlspecialchars($book['category_name']); ?></strong></div>
          <div class="summary-row"><span>Due Date</span><strong><?php echo htmlspecialchars($book['due_date']); ?></strong></div>
          
          <?php if ($book_fine > 0): ?>
            <div class="summary-row" style="color: #EDAFB8;">
              <span>Late Fine Detected</span>
              <strong>LKR <?php echo $book_fine; ?></strong>
            </div>
            
            <?php if (!$is_blocked): ?>
                <div class="summary-row" style="color: #B0C4B1; font-size: 0.9em;">
                  <span>Covered by Account Credit</span>
                  <strong>- LKR <?php echo $book_fine; ?></strong>
                </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <?php if ($is_blocked): ?>
            <div class="alert alert--warn" style="background-color: #EDAFB8; color: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; text-align: center;">
                <h3 style="margin: 0 0 10px 0;">Return Blocked</h3>
                <p style="margin: 0;">This book has a fine of LKR <?php echo $book_fine; ?>, but your account only has LKR <?php echo max(0, $account_credit); ?> in available credit.<br><br>Please meet the librarian to pay the remaining <strong>LKR <?php echo $remaining_fine; ?></strong> to process this return.</p>
            </div>
            <button class="btn btn--back" style="margin-top: 20px; width: 100%; padding: 15px; font-weight: bold;" onclick="window.location.href='SelectOption.php'">Return to Home</button>
        
        <?php else: ?>
            <form method="POST" action="return_action.php" class="actions" style="margin-top: 20px;">
              <input type="hidden" name="borrow_id" value="<?php echo $borrow_id; ?>">
              <button class="btn btn--confirm" type="submit" style="background-color: #4A5759; color: #fff; width: 100%; padding: 15px; border-radius: 5px; font-weight: bold; cursor: pointer;">Finalize Return</button>
            </form>
        <?php endif; ?>

      <?php endif; ?>
    </section>
  </main>
</body>
</html>