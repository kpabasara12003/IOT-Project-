<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_name = $_SESSION['student_name'];
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Library App - Select Options</title>

  <link rel="stylesheet" href="SelectOption.css" />
</head>

<body>

    <main class="panel">

    <header class="panel__header">
      <div class="brand">
        <div class="logo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z" />
            <path d="M8 6h8M8 10h8" />
          </svg>
        </div>
      </div>
    </header>

    <section class="panel__body">

      <h1>Welcome, <?php echo htmlspecialchars($student_name); ?> </h1>

      <p class="sub">What would you like to do?</p>

      <div class="actions">

        <button
          class="btn btn--borrow"
          onclick="window.location.href='bscanescreen.php'">
          Borrow
        </button>

        <button
          class="btn btn--return"
          onclick="window.location.href='return.php'">
          Return
        </button>

        <button
          class="btn btn--search"
          onclick="window.location.href='search.php'">
          Search Book
        </button>

      </div>

    </section>

    <footer class="panel__footer">

      <button class="btn back" onclick="window.location.href='logout.php'">
        Logout
      </button>

      <div class="hint">Tip: Use Search to check availability</div>

    </footer>

    </main>

</body>
</html>