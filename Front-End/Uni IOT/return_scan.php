<?php
session_start();

if (!isset($_SESSION['student_id']) || !isset($_GET['borrow_id'])) {
    header("Location: index.php");
    exit;
}

$borrow_id = intval($_GET['borrow_id']);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Scan to Return</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bscanescreen.css" /> </head>

<body>
  <div class="page-title">Physical Verification</div>

  <main class="panel panel--scan">
    <header class="panel__header panel__header--simple">
      <button class="btn btn--backTop" type="button" onclick="window.location.href='return_books.php';">
        Go Back
      </button>
    </header>

    <section class="panel__body panel__body--scan">
      <h1 class="scan-title">Scan the book</h1>
      <p style="text-align:center; color:#4A5759; margin-bottom:20px;">Please scan the NFC tag of the book to verify possession.</p>

      <form class="scan-form" method="POST" action="return_verify.php">
        <input type="hidden" name="borrow_id" value="<?php echo $borrow_id; ?>">
        
        <input
          type="text"
          name="nfc_uid"
          placeholder="Scan Book NFC Tag..."
          autocomplete="off"
          autofocus
          required
        >
        <button class="btn btn--scan" type="submit" style="background-color: #B0C4B1; color: #fff;">Verify Scan</button>
      </form>
    </section>
  </main>
</body>
</html>