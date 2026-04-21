<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scan_value = trim($_POST['book_id'] ?? "");

    if ($scan_value !== "") {
        // Allow borrowing only via the Scan & Borrow flow
        $_SESSION['borrow_entrypoint'] = 'scan';
        header("Location: borrow.php?id=" . rawurlencode($scan_value));
        exit;
    } else {
        $error = "Invalid scan value";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Borrow - Scan Book</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="bscanescreen.css" />
</head>

<body>
  <div class="page-title">Borrow - Scan</div>

  <main class="panel panel--scan">
    <header class="panel__header panel__header--simple">
      <div class="brand">
        <div class="logo" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z" />
            <path d="M8 6h8M8 10h8" />
          </svg>
        </div>
      </div>
    </header>

    <section class="panel__body panel__body--scan">
      <h1 class="scan-title">Tap a book to scan</h1>

      <div class="scan-box" role="img" aria-label="Scan illustration">
        <div class="scan-placeholder">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
            <path d="M9 7h6M9 11h6M9 15h4"/>
          </svg>
          <p>Add Scan Image</p>
        </div>
      </div>

      <form class="scan-form" method="POST" id="scanForm">
        <input
          type="text"
          name="book_id"
          id="book_id"
          style="opacity: 0; position: absolute; top: -100px; left: -100px; height: 1px; width: 1px;"
          autocomplete="off"
          autofocus
        >
        <button class="btn btn--scan" type="submit" id="submitBtn" style="display: none;">Scan</button>
      </form>

      <?php if (!empty($error)): ?>
        <div class="scan-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
    </section>

    <footer class="panel__footer panel__footer--scan">
      <a class="btn back" href="SelectOption.php">Go Back</a>
      <div class="hint">Tap the book / scan area to continue</div>
    </footer>
  </main>

  <script>
    const bookIdInput = document.getElementById('book_id');
    const scanForm = document.getElementById('scanForm');
    
    let statusTimeout;
    
    let scanStatusDiv = document.querySelector('.scan-error');
    if (!scanStatusDiv) {
      scanStatusDiv = document.createElement('div');
      scanStatusDiv.className = 'scan-error';
      const panelBody = document.querySelector('.panel__body--scan');
      if (panelBody) {
        const formElement = document.querySelector('.scan-form');
        if (formElement && formElement.nextSibling) {
          panelBody.insertBefore(scanStatusDiv, formElement.nextSibling);
        } else {
          panelBody.appendChild(scanStatusDiv);
        }
      }
    }
    
    function showStatusMessage(message, isError = false) {
      if (scanStatusDiv) {
        scanStatusDiv.textContent = message;
        scanStatusDiv.style.color = isError ? '#e74c3c' : '#27ae60';
        
        if (statusTimeout) {
          clearTimeout(statusTimeout);
        }
        
        statusTimeout = setTimeout(() => {
          if (scanStatusDiv) {
            scanStatusDiv.textContent = '';
          }
        }, 3000);
      }
    }
    
    bookIdInput.addEventListener('input', function(e) {
      const scannedValue = this.value.trim();
      
      if (scannedValue !== '') {
        setTimeout(() => {
          const currentValue = bookIdInput.value.trim();
          if (currentValue !== '') {
            console.log('Book ID scanned:', currentValue);
            showStatusMessage(' Book detected Processing...', false);
            // Automatically submit the form
            scanForm.submit();
          }
        }, 50);
      }
    });
    
    bookIdInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const scannedValue = this.value.trim();
        if (scannedValue !== '') {
          console.log('Book ID scanned (Enter):', scannedValue);
          showStatusMessage(' Book detected Processing...', false);
          scanForm.submit();
        }
      }
    });
    
    window.addEventListener('load', function() {
      bookIdInput.focus();
      if (scanStatusDiv) {
        scanStatusDiv.textContent = 'Ready to scan book...';
        scanStatusDiv.style.color = '#4A5759';
        setTimeout(() => {
          if (scanStatusDiv && scanStatusDiv.textContent === 'Ready to scan book...') {
            scanStatusDiv.textContent = '';
          }
        }, 3000);
      }
    });
    
    const panelBody = document.querySelector('.panel__body--scan');
    if (panelBody) {
      panelBody.addEventListener('click', function() {
        bookIdInput.focus();
      });
    }
    
    scanForm.addEventListener('submit', function(e) {
      if (bookIdInput.value.trim() === '') {
        e.preventDefault();
        showStatusMessage('✗ Please scan a book first.', true);
        bookIdInput.focus();
      }
    });
  </script>
</body>
</html>