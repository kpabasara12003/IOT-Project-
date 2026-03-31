<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Search the Book</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="search.css" />
</head>

<body>

  <main class="panel">

    <header class="panel__header">
      <div class="logo" aria-hidden="true">

        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
          <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z" />
          <path d="M8 6h8M8 10h8" />
        </svg>
      </div>

      <button class="btn btn--backTop" type="button" onclick="window.location.href='SelectOption.html'">
        Go Back
      </button>
    </header>

    <section class="panel__body">
      <h1 class="title">Search the Book</h1>

      <div class="searchbar">
        <input class="searchbar__input" type="text" placeholder="Search by Book title/author/category" />
        <button class="btn btn--search" type="button">Search</button>
      </div>

      <div class="table-wrap" role="region" aria-label="Search results">
        <table class="table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>Category</th>
              <th>Location</th>
            </tr>
          </thead>

          <tbody>

            <tr>
              <td>&nbsp;</td><td></td><td></td><td></td>
            </tr>
            <tr>
              <td>&nbsp;</td><td></td><td></td><td></td>
            </tr>
          </tbody>
        </table>
      </div>

    </section>

  </main>

</body>
</html>
