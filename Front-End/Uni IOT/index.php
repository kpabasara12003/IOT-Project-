<?php
session_start();
require_once 'db.php';

$error = "";
$debug_uid = "NFC001";  //nfc_uid

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $debug_uid = trim($_POST['nfc_uid']);

    if (!empty($debug_uid)) {

        $stmt = $conn->prepare("SELECT * FROM students WHERE nfc_uid = ?");
        $stmt->bind_param("s", $debug_uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($student = $result->fetch_assoc()) {

            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['student_number '] = $student['student_number '];

            header("Location: SelectOption.php");
            exit;

        } else {
            $error = " Invalid Student Card: ";
        }

    } else {
        $error = "No card detected";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Welcome</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="welcome.css" />
</head>

<body>

<main class="panel">

<header class="panel__header">
  <div class="brand">
    <div class="logo" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
        <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z" />
        <path d="M8 6h8M8 10h8" />
      </svg>
    </div>

    <div class="brand-text">
      <div class="brand-title">Library Self Service</div>
      <div class="brand-sub">Tap your Student ID to begin</div>
    </div>
  </div>
</header>

<section class="panel__body">

<h1 class="hero-title">Welcome</h1>

<p class="hero-sub">
  Tap your Student ID card to start borrowing, returning, and searching books.
</p>

<form method="POST" id="scanForm">
  <!-- debugging id input -->
  <div style="margin-bottom:15px;">
    <input 
      type="text" 
      name="nfc_uid" 
      id="rfidInput"
      value="<?php echo $debug_uid; ?>"
      placeholder="Enter / Scan Student Card UID"
      autofocus
      autocomplete="off"
      style="padding:10px; font-size:16px; width:260px;">

    <button type="submit" style="padding:10px;">
      Submit
    </button>
  </div>
    <!-- debugging -->

  <!-- <input 
    type="text" 
    name="nfc_uid" 
    id="rfidInput"
    autofocus
    autocomplete="off"
    style="position:absolute; opacity:0;"
  >

  <button class="tap-card" type="button"> -->
    <div class="tap-card__left">
      <div class="chip"></div>

      <div class="tap-title">Tap your ID Card</div>
      <div class="tap-sub">Hold near the scanner to continue</div>

      <div class="pulse"></div>
    </div>

    <div class="tap-card__right">
      <div class="device">
        <div class="device__screen"></div>
        <div class="device__base"></div>
      </div>

      <div class="idcard">
        <div class="idcard__row">
          <span class="dot"></span>
          <span class="line"></span>
        </div>
        <div class="idcard__row">
          <span class="line"></span>
          <span class="line short"></span>
        </div>
        <div class="waves">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>
  </button>

</form>

<?php if ($error): ?>
  <div style="color:red; margin-top:15px; font-weight:bold;">
    <?php echo $error; ?>
  </div>
<?php endif; ?>

<div class="hint">
  Waiting for card scan...
</div>

</section>

</main>

<script>
// Always focus input (for RFID + keyboard)
setInterval(() => {
  document.getElementById("rfidInput").focus();
}, 500);

// Submit on Enter
document.getElementById("rfidInput").addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    document.getElementById("scanForm").submit();
  }
});
</script>

</body>
</html>