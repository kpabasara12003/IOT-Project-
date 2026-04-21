<?php
include('../config/db.php');
include('../components/auth_check.php');

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$book_id = $_GET['id'];

/* Get book details */

$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

/* Get copy details (NFC etc.) */
$stmt2 = $conn->prepare("SELECT * FROM book_copies WHERE book_id = ?");
$stmt2->bind_param("i", $book_id);
$stmt2->execute();
$copy = $stmt2->get_result()->fetch_assoc();

if (isset($_POST['update_book'])) {

    // book table
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $isbn = $_POST['isbn'];
    $publisher = $_POST['publisher'];
    $edition = $_POST['edition'];
    $language = $_POST['language'];
    $year = $_POST['publication_year'];
    $pages = $_POST['pages'];
    $summary = $_POST['summary'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("
        UPDATE books SET
        title=?, subtitle=?, isbn=?, publisher=?, edition=?,
        language=?, publication_year=?, pages=?, summary=?, description=?, category_id=?
        WHERE book_id=?
    ");

    $stmt->bind_param(
        "ssssssisssii",
        $title,
        $subtitle,
        $isbn,
        $publisher,
        $edition,
        $language,
        $year,
        $pages,
        $summary,
        $description,
        $category_id,
        $book_id
    );

    $stmt->execute();


    // update NFC copy
    $nfc_uid = $_POST['nfc_uid'];
    $row_id = $_POST['row_id'];

    $stmt2 = $conn->prepare("
        UPDATE book_copies
        SET nfc_uid=?, row_id=?
        WHERE book_id=?
    ");

    $stmt2->bind_param("sii", $nfc_uid, $row_id, $book_id);
    $stmt2->execute();


    echo "<script>alert('Book updated successfully'); window.location='books.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h2>✏️ Edit Book</h2>

        <form method="POST">

            <input type="text" name="title" value="<?= $book['title'] ?>" required>
            <input type="text" name="subtitle" value="<?= $book['subtitle'] ?>">

            <input type="text" name="isbn" value="<?= $book['isbn'] ?>">
            <input type="text" name="publisher" value="<?= $book['publisher'] ?>">
            <input type="text" name="edition" value="<?= $book['edition'] ?>">
            <input type="text" name="language" value="<?= $book['language'] ?>">

            <input type="number" name="publication_year" value="<?= $book['publication_year'] ?>">
            <input type="number" name="pages" value="<?= $book['pages'] ?>">

            <textarea name="summary"><?= $book['summary'] ?></textarea>
            <textarea name="description"><?= $book['description'] ?></textarea>

            <!-- CATEGORY -->
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php
                $cat = $conn->query("SELECT * FROM book_categories");
                while ($c = $cat->fetch_assoc()) {
                    $selected = ($c['category_id'] == $book['category_id']) ? "selected" : "";
                    echo "<option value='{$c['category_id']}' $selected>{$c['category_name']}</option>";
                }
                ?>
            </select>

            <hr>

            <!-- NFC + LOCATION -->
            <input type="text" name="nfc_uid" value="<?= $copy['nfc_uid'] ?>" placeholder="NFC UID" required>
            <input type="number" name="row_id" value="<?= $copy['row_id'] ?>" placeholder="Row ID" required>

            <br><br>

            <button type="submit" name="update_book">Update Book</button>

        </form>
    </div>
</body>

</html>