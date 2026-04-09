<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$book_id = null;

if (isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
} elseif (isset($_GET['id'])) {
    $book_id = $_GET['id'];
}

if (!$book_id || !is_numeric($book_id)) {
    die("Invalid book ID");
}

$book_id = intval($book_id);
$error = "";
$success = false;

$book_sql = "SELECT b.book_id, b.title, c.category_name,
    COALESCE(GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', '), 'Unknown') AS authors,
    COUNT(CASE WHEN bc.status = 'available' THEN 1 END) AS available_copies
FROM books b
JOIN book_categories c ON b.category_id = c.category_id
LEFT JOIN book_authors ba ON b.book_id = ba.book_id
LEFT JOIN authors a ON ba.author_id = a.author_id
LEFT JOIN book_copies bc ON b.book_id = bc.book_id
WHERE b.book_id = ?
GROUP BY b.book_id";

$book_stmt = $conn->prepare($book_sql);
$book_stmt->bind_param("i", $book_id);
$book_stmt->execute();
$book_result = $book_stmt->get_result();

if ($book_result->num_rows === 0) {
    die("Book not found");
}

$book = $book_result->fetch_assoc();

$copy_id = null;
$copy_stmt = $conn->prepare("SELECT copy_id FROM book_copies WHERE book_id = ? AND status = 'available' LIMIT 1");
$copy_stmt->bind_param("i", $book_id);
$copy_stmt->execute();
$copy_result = $copy_stmt->get_result();

if ($copy_row = $copy_result->fetch_assoc()) {
    $copy_id = $copy_row['copy_id'];
}

$borrowed_at = date("Y-m-d H:i:s");
$due_date = date("Y-m-d", strtotime("+2 weeks"));

$confirm_action = isset($_POST['confirm']) && $_POST['confirm'] === '1';
$can_borrow = $copy_id !== null;

if ($confirm_action && $can_borrow) {
    $insert_sql = "INSERT INTO borrows (copy_id, student_id, borrowed_at, due_date, returned_at)
                  VALUES (?, ?, ?, ?, NULL)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiss", $copy_id, $student_id, $borrowed_at, $due_date);

    if ($insert_stmt->execute()) {
        $update_stmt = $conn->prepare("UPDATE book_copies SET status = 'borrowed' WHERE copy_id = ?");
        $update_stmt->bind_param("i", $copy_id);
        $update_stmt->execute();
        header("Location: success.php");
        exit;
    } else {
        $error = "Unable to confirm borrowing. Please try again.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Confirm Borrowing</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="borrow.css" />
</head>

<body>
  <main class="panel">
    <header class="panel__header">
      <div class="logo" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
          <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z" />
          <path d="M8 6h8M8 10h8" />
        </svg>
      </div>

      <button class="btn btn--backTop" type="button" onclick="window.history.back();">
        Go Back
      </button>
    </header>

    <section class="panel__body">
      <h1 class="title">Confirm Borrowing</h1>
      <p class="subtitle">NFC reader connected via USB. Tap the book to verify before confirming.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if (!$can_borrow): ?>
        <div class="alert alert--warn">No available copies right now.</div>
      <?php endif; ?>

      <div class="summary-card">
        <div class="summary-row">
          <span>Book Title</span>
          <strong><?php echo htmlspecialchars($book['title']); ?></strong>
        </div>
        <div class="summary-row">
          <span>Author(s)</span>
          <strong><?php echo htmlspecialchars($book['authors']); ?></strong>
        </div>
        <div class="summary-row">
          <span>Category</span>
          <strong><?php echo htmlspecialchars($book['category_name']); ?></strong>
        </div>
        <div class="summary-row">
          <span>Student ID</span>
          <strong><?php echo htmlspecialchars($student_id); ?></strong>
        </div>
        <div class="summary-row">
          <span>Copy ID</span>
          <strong><?php echo $copy_id ? htmlspecialchars($copy_id) : "N/A"; ?></strong>
        </div>
        <div class="summary-row">
          <span>Due Date</span>
          <strong><?php echo $due_date; ?></strong>
        </div>
      </div>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Borrow ID</th>
              <th>Copy ID</th>
              <th>Student ID</th>
              <th>Borrowed At</th>
              <th>Due Date</th>
              <th>Returned At</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Auto</td>
              <td><?php echo $copy_id ? htmlspecialchars($copy_id) : "N/A"; ?></td>
              <td><?php echo htmlspecialchars($student_id); ?></td>
              <td><?php echo $borrowed_at; ?></td>
              <td><?php echo $due_date; ?></td>
              <td>NULL</td>
            </tr>
          </tbody>
        </table>
      </div>

      <form method="POST" class="actions">
        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
        <input type="hidden" name="confirm" value="1">
        <button class="btn btn--confirm" type="submit" <?php echo $can_borrow ? '' : 'disabled'; ?>>
          Confirm Borrowing
        </button>
      </form>
    </section>
  </main>
</body>
</html>
