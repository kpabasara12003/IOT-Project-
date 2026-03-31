<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Warning</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="warning.css" />
</head>

<body>

  <main class="panel">

    <section class="panel__body">
      <h1 class="title">You have already<br>borrowed 2 books</h1>
      <p class="sub">Please select a book to return</p>

      <div class="list">
        <button class="book-btn" data-book="Book 1">Book 1</button>
        <button class="book-btn" data-book="Book 2">Book 2</button>
      </div>

      <button class="btn btn--return">Return</button>
    </section>

    <footer class="panel__footer">
      <button class="btn btn--back">Go Back</button>
    </footer>

  </main>

  <script>
    let selectedBook = null;

    document.querySelectorAll('.book-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.book-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedBook = btn.dataset.book;
      });
    });

    document.querySelector('.btn--return').addEventListener('click', () => {
      if (!selectedBook) {
        alert("Please select a book first");
        return;
      }
      alert(selectedBook + " returned successfully!");
      window.location.href = "index.html";
    });

    document.querySelector('.btn--back').addEventListener('click', () => {
      window.location.href = "index.html";
    });
  </script>

</body>
</html>
