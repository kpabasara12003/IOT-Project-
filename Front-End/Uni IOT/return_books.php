<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT 
            br.borrow_id, 
            b.title, 
            br.due_date,
            DATEDIFF(CURRENT_DATE, br.due_date) AS overdue_days
        FROM borrows br
        JOIN book_copies bc ON br.copy_id = bc.copy_id
        JOIN books b ON bc.book_id = b.book_id
        WHERE br.student_id = ? AND br.returned_at IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Return Books</title>
  <link rel="stylesheet" href="return_books.css" />
</head>
<body>
  <main class="panel">
    <header class="panel__header">
        </header>

    <section class="panel__body">
      <h1 class="title">Choose the book to<br>Return</h1>

      <div class="return-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $overdue_days = $row['overdue_days'];
                $fine = 0;
                $is_overdue = false;

                if ($overdue_days > 0) {
                    $is_overdue = true;
                    $fine = $overdue_days * 20;
                }
            ?>
                <div class="return-row">
                  <div class="book-name"><?php echo htmlspecialchars($row['title']); ?></div>
                  
                  <button class="btn btn--returnSmall" type="button"
                    onclick="window.location.href='return_scan.php?borrow_id=<?php echo $row['borrow_id']; ?>'">
                    Return
                  </button>

                  <?php if ($is_overdue): ?>
                      <div class="status status--overdue">
                          Overdue (Fine: LKR <?php echo $fine; ?>)
                      </div>
                  <?php else: ?>
                      <div class="status">Due: <?php echo $row['due_date']; ?></div>
                  <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have no books to return!</p>
        <?php endif; ?>
      </div>
    </section>

    <footer class="panel__footer">
      <button class="btn btn--back" type="button" onclick="window.location.href='SelectOption.php'">Go Back</button>
    </footer>
  </main>
</body>
</html>
