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

    $stmt1->bind_param(
        "ssssssisssi",
        $title,
        $subtitle,
        $isbn,
        $publisher,
        $edition,
        $language,
        $publication_year,
        $pages,
        $summary,
        $description,
        $category_id
    );

    $stmt1->execute();


    $book_id = $stmt1->insert_id;

    //link author
    if (!empty($author_id)) {
        $stmt2 = $conn->prepare("
            INSERT INTO book_authors (book_id, author_id)
            VALUES (?, ?)
        ");
        $stmt2->bind_param("ii", $book_id, $author_id);
        $stmt2->execute();
    }


    //Insert copy
    $stmt3 = $conn->prepare("
        INSERT INTO book_copies (book_id, row_id, nfc_uid)
        VALUES (?, ?, ?)
    ");
    $stmt3->bind_param("iis", $book_id, $row_id, $nfc_uid);
    $stmt3->execute();

    echo "<script>alert('Book added successfully');window.location.href='books.php';</script>";
}

if (isset($_GET['delete_copy'])) {

    $id = $_GET['delete_copy'];

    $stmt = $conn->prepare("DELETE FROM book_copies WHERE copy_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>window.location='books.php';</script>";
}


$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Books</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">

        <h2>Books</h2>

        <h3>Add New Book</h3>

        <form method="POST">

            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="subtitle" placeholder="Subtitle">

            <input type="text" name="isbn" placeholder="ISBN">

            <input type="text" name="publisher" placeholder="Publisher">
            <input type="text" name="edition" placeholder="Edition">
            <input type="text" name="language" placeholder="Language">

            <input type="number" name="publication_year" placeholder="Year">
            <input type="number" name="pages" placeholder="Pages">

            <textarea name="summary" placeholder="Summary"></textarea>
            <textarea name="description" placeholder="Description"></textarea>

            <!-- Category -->
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php
                $cat = $conn->query("SELECT * FROM book_categories");
                while ($c = $cat->fetch_assoc()) {
                    echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
                }
                ?>
            </select>

            <!-- Author -->
            <select name="author_id">
                <option value="">Select Author</option>
                <?php
                $auth = $conn->query("SELECT * FROM authors");
                while ($a = $auth->fetch_assoc()) {
                    echo "<option value='{$a['author_id']}'>{$a['author_name']}</option>";
                }
                ?>
            </select>

            <!-- Location -->
            <select name="row_id" required>
                <option value="">Select Row</option>
                <?php
                $rows = $conn->query("SELECT * FROM shelf_rows");
                while ($r = $rows->fetch_assoc()) {
                    echo "<option value='{$r['row_id']}'>Row {$r['row_id']}</option>";
                }
                ?>
            </select>

            <!-- NFC (USB scanner auto fills this) -->
            <input type="text" name="nfc_uid" placeholder="Scan NFC here" required autofocus>

            <button type="submit" name="add_book">Add Book</button>

        </form>


        <table>
            <tr>
                <th>Title</th>
                <th>ISBN</th>
                <th>NFC UID</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['isbn'] ?></td>
                    <td><?= $row['nfc_uid'] ?></td>
                    <td><?= $row['status'] ?></td>

                    <td>
                        <a href="edit_book.php?id=<?= $row['book_id'] ?>">Edit</a>

                        <a href="books.php?delete_copy=<?= $row['copy_id'] ?>">Delete Copy</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>

</html>