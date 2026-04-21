<?php
session_start();
require_once 'db.php';
require_once 'email_config.php';

$error = "";
$show_verification = false;
$student_name_for_ui = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
    header('Content-Type: application/json');
    if (isset($_SESSION['temp_auth'])) {
        $student_id = $_SESSION['temp_auth']['student_id'];
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        if ($student && !empty($student['email'])) {
            $new_code = sprintf("%06d", mt_rand(1, 999999));
            $_SESSION['temp_auth']['code'] = $new_code;
            $_SESSION['temp_auth']['expires'] = time() + 600; // 10 minutes
            if (sendVerificationCode($student['email'], $new_code, $student['full_name'])) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
    }
    echo json_encode(['success' => false]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    $entered_code = trim($_POST['verification_code']);
    if (isset($_SESSION['temp_auth']) && $_SESSION['temp_auth']['expires'] > time()) {
        if ($entered_code == $_SESSION['temp_auth']['code']) {
            $_SESSION['student_id']   = $_SESSION['temp_auth']['student_id'];
            $_SESSION['student_name'] = $_SESSION['temp_auth']['student_name'];
            $_SESSION['student_number'] = $_SESSION['temp_auth']['student_number']; 
            unset($_SESSION['temp_auth']);
            header("Location: SelectOption.php");
            exit;
        } else {
            $error = "Invalid verification code. Please try again.";
            $show_verification = true;
            $student_name_for_ui = $_SESSION['temp_auth']['student_name'];
        }
    } else {
        $error = "Verification code expired. Please scan your card again.";
        unset($_SESSION['temp_auth']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nfc_uid']) && !isset($_POST['verification_code'])) {
    $debug_uid = trim($_POST['nfc_uid']);
    if (!empty($debug_uid)) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE nfc_uid = ?");
        $stmt->bind_param("s", $debug_uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($student = $result->fetch_assoc()) {
            if (empty($student['email'])) {
                $error = "No email registered for this student. Contact administrator.";
            } else {
                $code = sprintf("%06d", mt_rand(1, 999999));
                $_SESSION['temp_auth'] = [
                    'student_id'   => $student['student_id'],
                    'student_name' => $student['full_name'],
                    'student_number'=> $student['student_number'],
                    'code'         => $code,
                    'expires'      => time() + 600   
                ];
       
                if (sendVerificationCode($student['email'], $code, $student['full_name'])) {
                    $show_verification = true;
                    $student_name_for_ui = $student['full_name'];
                } else {
                    $error = "Failed to send verification email. Please try again.";
                    unset($_SESSION['temp_auth']);
                }
            }
        } else {
            $error = "Invalid Student Card";
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
    <?php if (!$show_verification): ?>
      <h1 class="hero-title">Welcome</h1>
      <p class="hero-sub">
        Tap your Student ID card to start borrowing, returning, and searching books.
      </p>

      <form method="POST" id="scanForm">
        <input 
          type="text" 
          name="nfc_uid" 
          id="rfidInput"
          value=""
          autofocus
          autocomplete="off"
          style="opacity: 0; position: absolute; top: -100px; left: -100px; height: 1px; width: 1px;"
        >
        <button type="submit" id="submitBtn" style="display: none;">Submit</button>
      </form>

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
          <div class="idcard__row"><span class="dot"></span><span class="line"></span></div>
          <div class="idcard__row"><span class="line"></span><span class="line short"></span></div>
          <div class="waves"><span></span><span></span><span></span></div>
        </div>
      </div>

    <?php else: ?>
      <div class="verification-box">
        <h2>Verify Your Email</h2>
        <p>Hello <strong><?php echo htmlspecialchars($student_name_for_ui); ?></strong>!</p>
        <p>We sent a 6-digit code to your registered email address.</p>
        <form method="POST" id="verifyForm">
          <input type="text" name="verification_code" class="code-input" 
                 placeholder="000000" maxlength="6" pattern="[0-9]{6}" autofocus required>
          <br><br>
          <button type="submit" style="padding:12px 32px; border-radius:40px; background:#2c3e50; color:white; border:none;">Verify & Login</button>
        </form>
        <div class="timer">Code expires in: <span id="countdown">10:00</span></div>
        <button type="button" class="resend-btn" onclick="resendCode()">Resend Code</button>
        <br>
        <a href="?" class="back-link">← Use a different card</a>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div style="color:#ff8888; margin-top:15px; font-weight:bold; background:#33000080; padding:10px; border-radius:12px;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <div class="hint">
      <?php echo $show_verification ? "Enter the 6-digit code sent to your email" : "Waiting for card scan..."; ?>
    </div>
  </section>
</main>

<script>
<?php if ($show_verification): ?>
  let expiry = <?php echo $_SESSION['temp_auth']['expires'] ?? (time() + 600); ?> * 1000;
  function updateTimer() {
    let now = new Date().getTime();
    let distance = expiry - now;
    if (distance < 0) {
      document.getElementById('countdown').innerHTML = "EXPIRED";
      document.querySelector('.timer').style.color = "#ff6666";
      return;
    }
    let minutes = Math.floor((distance % (1000 * 3600)) / (1000 * 60));
    let seconds = Math.floor((distance % (1000 * 60)) / 1000);
    document.getElementById('countdown').innerHTML = 
      minutes.toString().padStart(2,'0') + ":" + seconds.toString().padStart(2,'0');
    setTimeout(updateTimer, 1000);
  }
  updateTimer();

  function resendCode() {
    fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'resend_code=1'
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("New verification code sent to your email!");
        expiry = new Date().getTime() + 10*60000;
        updateTimer();
      } else {
        alert("Failed to resend code. Please scan your card again.");
      }
    })
    .catch(() => alert("Network error. Please try again."));
  }
<?php else: ?>
  let inputField = document.getElementById("rfidInput");
  let scanForm = document.getElementById("scanForm");
  
  if (inputField) {
    inputField.addEventListener('input', function(e) {
      const scannedValue = this.value.trim();
      
      if (scannedValue !== '') {
        setTimeout(() => {
          const currentValue = inputField.value.trim();
          if (currentValue !== '') {
            console.log('Student card scanned:', currentValue);
            scanForm.submit();
          }
        }, 50);
      }
    });
    
    inputField.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const scannedValue = this.value.trim();
        if (scannedValue !== '') {
          console.log('Student card scanned (Enter):', scannedValue);
          scanForm.submit();
        }
      }
    });
  }
  

  setInterval(() => {
    if (inputField && document.activeElement !== inputField) {
      inputField.focus();
    }
  }, 500);
  

  document.body.addEventListener('click', function() {
    if (inputField) inputField.focus();
  });
<?php endif; ?>
</script>

</body>
</html>