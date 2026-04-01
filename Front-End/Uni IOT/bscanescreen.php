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
    </section>

    <footer class="panel__footer panel__footer--scan">
      <a class="btn back" href="SelectOption.php">Go Back</a>
      <div class="hint">Tap the book / scan area to continue</div>
    </footer>
  </main>
</body>
</html>
