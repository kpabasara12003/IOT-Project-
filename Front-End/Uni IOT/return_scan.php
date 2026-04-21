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

      <form class="scan-form" id="scanForm" method="POST" action="return_verify.php">
        <input type="hidden" name="borrow_id" value="<?php echo $borrow_id; ?>">
        
        <input
          type="text"
          id="nfc_uid"
          name="nfc_uid"
          style="opacity: 0; position: absolute; top: -100px; left: -100px; height: 1px; width: 1px;"
          autocomplete="off"
          autofocus
          required>
        
        <button class="btn btn--scan" type="submit" id="submitBtn" style="background-color: #B0C4B1; color: #fff; display: none;">Verify Scan</button>
      </form>
      
      <div id="scanStatus" style="text-align:center; margin-top:20px; font-weight:600; color:#2c3e50;"></div>
    </section>
  </main>

  <script>
    const nfcInput = document.getElementById('nfc_uid');
    const scanForm = document.getElementById('scanForm');
    const scanStatusDiv = document.getElementById('scanStatus');
    
    let statusTimeout;
    
    function showStatusMessage(message, isError = false) {
      scanStatusDiv.textContent = message;
      scanStatusDiv.style.color = isError ? '#e74c3c' : '#27ae60';
      
      if (statusTimeout) {
        clearTimeout(statusTimeout);
      }
      
      // Clear message after 3 seconds
      statusTimeout = setTimeout(() => {
        if (scanStatusDiv) {
          scanStatusDiv.textContent = '';
        }
      }, 3000);
    }
    
    nfcInput.addEventListener('input', function(e) {
      const scannedValue = this.value.trim();
      
      if (scannedValue !== '') {
        setTimeout(() => {
          const currentValue = nfcInput.value.trim();
          if (currentValue !== '') {
            console.log('NFC Tag Scanned:', currentValue);
            showStatusMessage('✓ NFC tag detected! Verifying...', false);
            
          
            scanForm.submit();
          }
        }, 50);
      }
    });
    
    nfcInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const scannedValue = this.value.trim();
        if (scannedValue !== '') {
          console.log('NFC Tag Scanned (Enter key):', scannedValue);
          showStatusMessage('✓ NFC tag detected! Verifying...', false);
          scanForm.submit();
        }
      }
    });
    
    window.addEventListener('load', function() {
      nfcInput.focus();
      showStatusMessage('Ready to scan NFC tag...', false);
    });
    
    const panelBody = document.querySelector('.panel__body--scan');
    if (panelBody) {
      panelBody.addEventListener('click', function() {
        nfcInput.focus();
      });
    }
    
    scanForm.addEventListener('submit', function(e) {
      if (nfcInput.value.trim() === '') {
        e.preventDefault();
        showStatusMessage('✗ Please scan an NFC tag first.', true);
        nfcInput.focus();
      }
    });
  </script>
</body>
</html>