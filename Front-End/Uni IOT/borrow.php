<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['borrow_entrypoint']) || $_SESSION['borrow_entrypoint'] !== 'scan') {
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width,initial-scale=1" />
      <title>Borrow Restricted</title>
      <link rel="stylesheet" href="borrow.css" />
    </head>
    <body>
      <main class="panel">
        <section class="panel__body">
          <h1 class="title">Borrowing Restricted</h1>
          <div class="alert alert--warn">Please use the Scan &amp; Borrow option to borrow books.</div>
          <div class="actions">
            <button class="btn btn--confirm" type="button" onclick="window.location.href='bscanescreen.php'">
              Go to Scan &amp; Borrow
            </button>
          </div>
        </section>
      </main>
    </body>
    </html>
    <?php
    exit;
}

$student_id = (int)$_SESSION['student_id'];

$scan_value = null;
if (isset($_POST['book_id'])) {
    $scan_value = trim((string)$_POST['book_id']);
} elseif (isset($_GET['id'])) {
    $scan_value = trim((string)$_GET['id']);
}

if ($scan_value === null || $scan_value === '') {
    die("Invalid scan value");
}

$error = "";
$borrow_limit = 2;

$active_borrows = 0;
$active_stmt = $conn->prepare("SELECT COUNT(*) AS active_count FROM borrows WHERE student_id = ? AND returned_at IS NULL");
if ($active_stmt) {
    $active_stmt->bind_param("i", $student_id);
    if ($active_stmt->execute()) {
        $active_result = $active_stmt->get_result();
        if ($active_row = $active_result->fetch_assoc()) {
            $active_borrows = (int)$active_row['active_count'];
        }
    }
}

$account_credit = 0.0;
$credit_stmt = $conn->prepare("SELECT balance, type FROM student_accounts WHERE student_id = ? LIMIT 1");
if ($credit_stmt) {
    $credit_stmt->bind_param("i", $student_id);
    if ($credit_stmt->execute()) {
        $credit_result = $credit_stmt->get_result();
        if ($credit_row = $credit_result->fetch_assoc()) {
            $balance = (float)$credit_row['balance'];
            if (strtolower($credit_row['type']) === 'fine') {
                $account_credit = -$balance; 
            } else {
                $account_credit = $balance;
            }
        }
    }
}

$live_fines = 0.0;
$fine_stmt = $conn->prepare("
    SELECT SUM(DATEDIFF(CURRENT_DATE, due_date) * 20) AS total_fines
    FROM borrows
    WHERE student_id = ? AND returned_at IS NULL AND CURRENT_DATE > due_date
");
if ($fine_stmt) {
    $fine_stmt->bind_param("i", $student_id);
    if ($fine_stmt->execute()) {
        $fine_result = $fine_stmt->get_result();
        if ($fine_row = $fine_result->fetch_assoc()) {
            $live_fines = (float)$fine_row['total_fines'];
        }
    }
}

$effective_credit = $account_credit - $live_fines;

if ($effective_credit < 0) {
    $display_balance = "Credit exhausted (LKR " . $effective_credit . ")";
} else {
    $display_balance = "Available Credit: LKR " . $effective_credit;
}

$borrow_limit_reached = $active_borrows >= $borrow_limit;
$credit_limit_reached = $effective_credit <= 0;

$copy_id = null;
$book_id = null;
$scanned_copy_status = null;
$scanned_specific_copy = false;

$resolve_nfc_stmt = $conn->prepare("SELECT copy_id, book_id, status FROM book_copies WHERE nfc_uid = ? LIMIT 1");
if ($resolve_nfc_stmt) {
    $resolve_nfc_stmt->bind_param("s", $scan_value);
    if ($resolve_nfc_stmt->execute()) {
        $resolve_nfc_result = $resolve_nfc_stmt->get_result();
        if ($resolve_nfc_row = $resolve_nfc_result->fetch_assoc()) {
            $copy_id = (int)$resolve_nfc_row['copy_id'];
            $book_id = (int)$resolve_nfc_row['book_id'];
            $scanned_copy_status = (string)$resolve_nfc_row['status'];
            $scanned_specific_copy = true;
        }
    }
}

if ($copy_id === null && ctype_digit($scan_value)) {
    $scan_int = (int)$scan_value;
    $resolve_copy_stmt = $conn->prepare("SELECT copy_id, book_id, status FROM book_copies WHERE copy_id = ? LIMIT 1");
    if ($resolve_copy_stmt) {
        $resolve_copy_stmt->bind_param("i", $scan_int);
        if ($resolve_copy_stmt->execute()) {
            $resolve_copy_result = $resolve_copy_stmt->get_result();
            if ($resolve_copy_row = $resolve_copy_result->fetch_assoc()) {
                $copy_id = (int)$resolve_copy_row['copy_id'];
                $book_id = (int)$resolve_copy_row['book_id'];
                $scanned_copy_status = (string)$resolve_copy_row['status'];
                $scanned_specific_copy = true;
            }
        }
    }
}

if ($book_id === null && ctype_digit($scan_value)) {
    $scan_int = (int)$scan_value;
    $resolve_book_stmt = $conn->prepare("SELECT book_id FROM books WHERE book_id = ? LIMIT 1");
    if ($resolve_book_stmt) {
        $resolve_book_stmt->bind_param("i", $scan_int);
        if ($resolve_book_stmt->execute()) {
            $resolve_book_result = $resolve_book_stmt->get_result();
            if ($resolve_book_result && $resolve_book_result->num_rows > 0) {
                $book_id = $scan_int;
            }
        }
    }
}

if ($book_id === null) {
    die("Book not found");
}

$scanned_copy_is_available = ($copy_id !== null && $scanned_copy_status !== null && strtolower($scanned_copy_status) === 'available');

if (!$scanned_specific_copy) {
    $copy_stmt = $conn->prepare("SELECT copy_id FROM book_copies WHERE book_id = ? AND LOWER(status) = 'available' LIMIT 1");
    if ($copy_stmt) {
        $copy_stmt->bind_param("i", $book_id);
        $copy_stmt->execute();
        $copy_result = $copy_stmt->get_result();
        if ($copy_row = $copy_result->fetch_assoc()) {
            $copy_id = (int)$copy_row['copy_id'];
            $scanned_copy_status = 'available';
        }
    }
}

if ($book_id === null) {
    die("Book not found");
}

$book_sql = "SELECT b.book_id, b.title, c.category_name,
    COALESCE(GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', '), 'Unknown') AS authors,
    (
      SELECT COUNT(*)
      FROM book_copies bc2
      WHERE bc2.book_id = b.book_id
        AND LOWER(bc2.status) = 'available'
    ) AS available_copies
FROM books b
JOIN book_categories c ON b.category_id = c.category_id
LEFT JOIN book_authors ba ON b.book_id = ba.book_id
LEFT JOIN authors a ON ba.author_id = a.author_id
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

$borrowed_at = date("Y-m-d H:i:s");
$due_date = date("Y-m-d", strtotime("+2 weeks"));

$confirm_action = isset($_POST['confirm']) && $_POST['confirm'] === '1';

$no_available_copy = ($copy_id === null) || ($scanned_specific_copy && !$scanned_copy_is_available);
$can_borrow = !$no_available_copy && !$borrow_limit_reached && !$credit_limit_reached;

if ($confirm_action && $can_borrow) {
    $insert_sql = "INSERT INTO borrows (copy_id, student_id, borrowed_at, due_date, returned_at)
                  VALUES (?, ?, ?, ?, NULL)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiss", $copy_id, $student_id, $borrowed_at, $due_date);

    if ($insert_stmt->execute()) {
        $status_stmt = $conn->prepare("UPDATE book_copies SET status = 'borrowed' WHERE copy_id = ?");
        if ($status_stmt) {
            $status_stmt->bind_param("i", $copy_id);
            $status_stmt->execute();
        }
        unset($_SESSION['borrow_entrypoint']);
        header("Location: success.php");
        exit;
    }

    $error = "Unable to confirm borrowing. Please try again.";
} elseif ($confirm_action && !$can_borrow) {
    if ($credit_limit_reached) {
        $error = "Borrowing blocked. " . $display_balance . ". Please clear your dues with the librarian.";
    } elseif ($borrow_limit_reached) {
        $error = "Borrow limit reached (max $borrow_limit books at a time). Return a book to borrow again.";
    } elseif ($scanned_specific_copy && !$scanned_copy_is_available && $scanned_copy_status !== null) {
        $error = "This copy is not available (status: " . $scanned_copy_status . ").";
    } elseif ($no_available_copy) {
        $error = "No available copies right now.";
    } else {
        $error = "Unable to confirm borrowing right now.";
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

      <button class="btn btn--backTop" type="button" onclick="window.location.href='bscanescreen.php';">
        Go Back
      </button>
    </header>

    <section class="panel__body">
      <h1 class="title">Confirm Borrowing</h1>
      <p class="subtitle">Tap the book to verify before confirming.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($borrow_limit_reached && empty($error)): ?>
        <div class="alert alert--warn">Borrow limit reached (max <?php echo htmlspecialchars((string)$borrow_limit); ?> books).</div>
      <?php endif; ?>

      <?php if ($credit_limit_reached && empty($error)): ?>
        <div class="alert alert--warn">Borrowing blocked. <?php echo $display_balance; ?>. Please clear dues.</div>
      <?php endif; ?>

      <?php if ($no_available_copy && empty($error)): ?>
        <?php if ($scanned_specific_copy): ?>
          <div class="alert alert--warn">This copy is not available (status: <?php echo htmlspecialchars((string)$scanned_copy_status); ?>).</div>
        <?php else: ?>
          <div class="alert alert--warn">No available copies right now.</div>
        <?php endif; ?>
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
          <strong><?php echo htmlspecialchars((string)$student_id); ?></strong>
        </div>
        <div class="summary-row">
          <span>Copy ID</span>
          <strong><?php echo $copy_id ? htmlspecialchars((string)$copy_id) : "N/A"; ?></strong>
        </div>
        <div class="summary-row">
          <span>Due Date</span>
          <strong><?php echo htmlspecialchars($due_date); ?></strong>
        </div>
        <div class="summary-row">
          <span>Current Credit</span>
          <strong style="color: <?php echo $effective_credit < 0 ? '#EDAFB8' : '#B0C4B1'; ?>;">
            LKR <?php echo $effective_credit; ?>
          </strong>
        </div>
      </div>

      <form method="POST" class="actions">
        <input type="hidden" name="book_id" value="<?php echo htmlspecialchars((string)$scan_value); ?>">
        <input type="hidden" name="confirm" value="1">
        <button class="btn btn--confirm" type="submit" <?php echo $can_borrow ? '' : 'disabled'; ?>>
          Confirm Borrowing
        </button>
      </form>
    </section>
  </main>
</body>
</html>