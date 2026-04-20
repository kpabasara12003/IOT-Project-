<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$search = "";
$results = null;


  $sql = "SELECT  b.book_id, b.title, c.category_name, COALESCE(GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', '), 'Unknown') AS authors,
      (
        SELECT COUNT(*)
        FROM book_copies bc2
        LEFT JOIN borrows br2
          ON br2.copy_id = bc2.copy_id
         AND br2.returned_at IS NULL
        WHERE bc2.book_id = b.book_id
          AND br2.copy_id IS NULL
      ) AS available_copies
  FROM books b
  JOIN book_categories c ON b.category_id = c.category_id
  LEFT JOIN book_authors ba ON b.book_id = ba.book_id
  LEFT JOIN authors a ON ba.author_id = a.author_id";

if (isset($_GET['search']) && $_GET['search'] !== "") {

    $search = trim($_GET['search']);
    $search_param = "%" . $search . "%";

    $sql .= "
    WHERE 
        b.title LIKE ? OR
        c.category_name LIKE ? OR
        a.author_name LIKE ?
    ";
}

$sql .= " GROUP BY b.book_id ORDER BY b.title ASC";

$stmt = $conn->prepare($sql);

// Bind only if searching
if (!empty($search)) {
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$results = $stmt->get_result();
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Search the Book</title>

  <link rel="stylesheet" href="search.css" />
</head>

<body>

  <main class="panel">

    <header class="panel__header">
      <div class="logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
          <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z" />
          <path d="M8 6h8M8 10h8" />
        </svg>
      </div>

      <button class="btn btn--backTop" onclick="window.location.href='SelectOption.php'">
        Go Back
      </button>
    </header>

  <section class="panel__body">

  <h1 class="title">Search the Book</h1>

  <form method="GET" class="searchbar">

    <input class="searchbar__input" type="text" 
      name="search"
      placeholder="Search by Book title / author / category"
      value="<?php echo htmlspecialchars($search); ?>">

    <button class="btn btn--search" type="submit">
      Search
    </button>

  </form>

  <div class="table-wrap">

  <table class="table">

  <thead>
  <tr>
    <th>Title</th>
    <th>Author</th>
    <th>Category</th>
    <th>Action</th>
  </tr>
  </thead>

  <tbody>

  <?php if ($results && $results->num_rows > 0): ?>

    <?php while ($row = $results->fetch_assoc()): ?>

      <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['authors']); ?></td>
        <td><?php echo htmlspecialchars($row['category_name']); ?></td>

        <td>
          <a href="bookdetails.php?id=<?php echo $row['book_id']; ?>">
            <button type="button">View</button>
          </a>
        </td>
      </tr>

    <?php endwhile; ?>

  <?php else: ?>

    <tr>
      <td colspan="4" style="text-align:center;">
        No books found
      </td>
    </tr>

  <?php endif; ?>

  </tbody>

  </table>

  </div>

  </section>

  </main>

</body>
</html>
