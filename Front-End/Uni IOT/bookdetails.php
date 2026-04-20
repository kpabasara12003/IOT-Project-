<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: welcome.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid book ID");
}

$book_id = intval($_GET['id']);
$student_id = $_SESSION['student_id'];

$sql = "SELECT   b.book_id, b.title, b.subtitle, b.isbn, b.publisher,
    b.edition, b.language, b.publication_year, b.pages, b.summary, b.description, c.category_name,
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

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found");
}

$book = $result->fetch_assoc();

// 🔹 Optional: set borrowing dates (2 weeks from now)
$today = date("Y-m-d");
$due_date = date("Y-m-d", strtotime("+2 weeks"));
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Book Information - <?php echo htmlspecialchars($book['title']); ?></title>

<link rel="stylesheet" href="bookdetails.css">
</head>

<body>

<main class="screen">

<h1 class="title">Book Information</h1>

<section class="info-card">

    <div class="row">
        <div class="label">Book Title -</div>
        <div class="value"><?php echo htmlspecialchars($book['title']); ?></div>
    </div>

    <div class="row">
        <div class="label">Subtitle -</div>
        <div class="value"><?php echo htmlspecialchars($book['subtitle']); ?></div>
    </div>

    <div class="row">
        <div class="label">Author(s) -</div>
        <div class="value"><?php echo htmlspecialchars($book['authors']); ?></div>
    </div>

    <div class="row">
        <div class="label">Category -</div>
        <div class="value"><?php echo htmlspecialchars($book['category_name']); ?></div>
    </div>

    <div class="row">
        <div class="label">Publisher -</div>
        <div class="value"><?php echo htmlspecialchars($book['publisher']); ?></div>
    </div>

    <div class="row">
        <div class="label">Edition -</div>
        <div class="value"><?php echo htmlspecialchars($book['edition']); ?></div>
    </div>

    <div class="row">
        <div class="label">Language -</div>
        <div class="value"><?php echo htmlspecialchars($book['language']); ?></div>
    </div>

    <div class="row">
        <div class="label">Publication Year -</div>
        <div class="value"><?php echo htmlspecialchars($book['publication_year']); ?></div>
    </div>

    <div class="row">
        <div class="label">Pages -</div>
        <div class="value"><?php echo htmlspecialchars($book['pages']); ?></div>
    </div>

    <div class="row">
        <div class="label">ISBN -</div>
        <div class="value"><?php echo htmlspecialchars($book['isbn']); ?></div>
    </div>

    <div class="row">
        <div class="label">Summary -</div>
        <div class="value"><?php echo htmlspecialchars($book['summary']); ?></div>
    </div>

    <div class="row">
        <div class="label">Description -</div>
        <div class="value"><?php echo nl2br(htmlspecialchars($book['description'])); ?></div>
    </div>

    <div class="row">
        <div class="label">Available Copies -</div>
        <div class="value"><?php echo $book['available_copies']; ?></div>
    </div>

    <div class="row">
        <div class="label">Current Date -</div>
        <div class="value"><?php echo $today; ?></div>
    </div>

    <div class="row">
        <div class="label">Return Date -</div>
        <div class="value"><?php echo $due_date; ?></div>
    </div>

</section>

<div class="actions">
    <button class="btn btn-back" type="button" onclick="window.history.back();">Go Back</button>
</div>

</main>

</body>
</html>
