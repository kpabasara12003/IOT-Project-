<?php
include('../config/db.php');
include('../components/auth_check.php');

$query = "
SELECT 
b.book_id,
b.title,
b.isbn,
bc.copy_id,
bc.nfc_uid,
bc.status
FROM book_copies bc
JOIN books b ON bc.book_id = b.book_id
";

if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $category_id = $_POST['category_id'];
    $author_id = $_POST['author_id'];
    $row_id = $_POST['row_id'];
    $nfc_uid = $_POST['nfc_uid'];
    $subtitle = $_POST['subtitle'];
    $publisher = $_POST['publisher'];
    $edition = $_POST['edition'];
    $language = $_POST['language'];
    $publication_year = $_POST['publication_year'];
    $pages = $_POST['pages'];
    $summary = $_POST['summary'];
    $description = $_POST['description'];

    $stmt1 = $conn->prepare("
        INSERT INTO books 
        (title, subtitle, isbn, publisher, edition, language, publication_year, pages, summary, description, category_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt1->bind_param("ssssssisssi", $title, $subtitle, $isbn, $publisher, $edition, $language, $publication_year, $pages, $summary, $description, $category_id);
    $stmt1->execute();
    $book_id = $stmt1->insert_id;

    if (!empty($author_id)) {
        $stmt2 = $conn->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $book_id, $author_id);
        $stmt2->execute();
    }

    $stmt3 = $conn->prepare("INSERT INTO book_copies (book_id, row_id, nfc_uid) VALUES (?, ?, ?)");
    $stmt3->bind_param("iis", $book_id, $row_id, $nfc_uid);
    $stmt3->execute();

    echo "<script>alert('Book added successfully');window.location.href='books.php';</script>";
}

if (isset($_GET['delete_copy'])) {
    $id = $_GET['delete_copy'];
    $conn->query("DELETE FROM book_copies WHERE copy_id = $id");
    echo "<script>window.location='books.php';</script>";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background-color: #F2E8CF; color: #386641; padding-bottom: 50px; }

        .content { max-width: 1100px; margin: 40px auto; padding: 20px; }

        h2 { color: #386641; font-size: 2.2rem; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        h3 { color: #6A994E; margin-bottom: 20px; border-left: 5px solid #A7C957; padding-left: 10px; }

        /* Form Layout Grid */
        form {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        form input, form select, form textarea {
            padding: 12px;
            border: 2px solid #F2E8CF;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        form input:focus, form select:focus, form textarea:focus { border-color: #6A994E; }

        /* Make textareas and submit button full width */
        form textarea, form button, .full-width { grid-column: span 2; }
        form textarea { height: 80px; resize: vertical; }

        form button {
            background-color: #386641;
            color: white;
            border: none;
            padding: 15px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            border-radius: 8px;
            margin-top: 10px;
            transition: 0.3s;
        }

        form button:hover { background-color: #6A994E; }

        /* Creative Table Styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px; /* Space between rows */
            margin-top: 20px;
        }

        th {
            background-color: #386641;
            color: #F2E8CF;
            padding: 15px;
            text-align: left;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
        }

        th:first-child { border-radius: 10px 0 0 10px; }
        th:last-child { border-radius: 0 10px 10px 0; }

        td {
            background-color: #ffffff;
            padding: 15px;
            color: #386641;
            font-weight: 500;
            border-top: 1px solid #F2E8CF;
            border-bottom: 1px solid #F2E8CF;
        }

        td:first-child { border-left: 1px solid #F2E8CF; border-radius: 10px 0 0 10px; }
        td:last-child { border-right: 1px solid #F2E8CF; border-radius: 0 10px 10px 0; }

        tr:hover td { background-color: #f9f9f9; transform: scale(1.005); transition: 0.2s; }

        /* Action Links */
        .delete-link {
            color: #BC4749;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            padding: 5px 10px;
            border: 1px solid #BC4749;
            border-radius: 5px;
            transition: 0.3s;
        }

        .delete-link:hover { background-color: #BC4749; color: white; }

        /* Status Badge */
        .status-badge {
            background-color: #A7C957;
            color: #386641;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Scan Input Focus Effect */
        input[name="nfc_uid"] {
            border: 2px solid #386641;
            background-color: #f0fff4;
        }
    </style>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h2>Books Management</h2>

        <h3>Add New Book</h3>
        <form method="POST">
            <input type="text" name="title" placeholder="Book Title" required>
            <input type="text" name="subtitle" placeholder="Subtitle (Optional)">

            <input type="text" name="isbn" placeholder="ISBN Number">
            <input type="text" name="publisher" placeholder="Publisher">

            <input type="text" name="edition" placeholder="Edition">
            <input type="text" name="language" placeholder="Language">

            <input type="number" name="publication_year" placeholder="Year of Publication">
            <input type="number" name="pages" placeholder="Number of Pages">

            <textarea name="summary" placeholder="Brief Summary..."></textarea>
            <textarea name="description" placeholder="Detailed Description..."></textarea>

            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php
                $cat = $conn->query("SELECT * FROM book_categories");
                while ($c = $cat->fetch_assoc()) {
                    echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
                }
                ?>
            </select>

            <select name="author_id">
                <option value="">Select Author</option>
                <?php
                $auth = $conn->query("SELECT * FROM authors");
                while ($a = $auth->fetch_assoc()) {
                    echo "<option value='{$a['author_id']}'>{$a['author_name']}</option>";
                }
                ?>
            </select>

            <select name="row_id" required>
                <option value="">Select Shelf Location (Row)</option>
                <?php
                $rows = $conn->query("SELECT * FROM shelf_rows");
                while ($r = $rows->fetch_assoc()) {
                    echo "<option value='{$r['row_id']}'>Row {$r['row_id']}</option>";
                }
                ?>
            </select>

            <input type="text" name="nfc_uid" placeholder="Ready to scan NFC..." required autofocus>

            <button type="submit" name="add_book">Register New Book</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>ISBN</th>
                    <th>NFC UID</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= $row['title'] ?></strong></td>
                    <td><?= $row['isbn'] ?></td>
                    <td><code><?= $row['nfc_uid'] ?></code></td>
                    <td><span class="status-badge"><?= $row['status'] ?></span></td>
                    <td>
                        <a href="books.php?delete_copy=<?= $row['copy_id'] ?>" class="delete-link" onclick="return confirm('Delete this copy?')">Delete Copy</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>