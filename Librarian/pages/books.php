<?php
include('../config/db.php');
include('../components/auth_check.php');


if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $isbn = $_POST['isbn'];
    $publisher = $_POST['publisher'];
    $edition = $_POST['edition'];
    $language = $_POST['language'];
    $publication_year = $_POST['publication_year'];
    $pages = $_POST['pages'];
    $summary = $_POST['summary'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $author_id = $_POST['author_id']; 
    $row_id = $_POST['row_id'];
    $nfc_uid = $_POST['nfc_uid'];

    
    $check_isbn = $conn->prepare("SELECT book_id FROM books WHERE isbn = ?");
    $check_isbn->bind_param("s", $isbn);
    $check_isbn->execute();
    $res = $check_isbn->get_result();

    if ($res->num_rows > 0) {
        
        $book_data = $res->fetch_assoc();
        $book_id = $book_data['book_id'];
    } else {
       
        $stmt1 = $conn->prepare("INSERT INTO books (title, subtitle, isbn, publisher, edition, language, publication_year, pages, summary, description, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("ssssssisssi", $title, $subtitle, $isbn, $publisher, $edition, $language, $publication_year, $pages, $summary, $description, $category_id);
        $stmt1->execute();
        $book_id = $stmt1->insert_id;

        
        if (!empty($author_id)) {
            $stmt2 = $conn->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $book_id, $author_id);
            $stmt2->execute();
        }
    }

    
    $stmt3 = $conn->prepare("INSERT INTO book_copies (book_id, row_id, nfc_uid) VALUES (?, ?, ?)");
    $stmt3->bind_param("iis", $book_id, $row_id, $nfc_uid);
    
    if ($stmt3->execute()) {
        echo "<script>alert('Record updated successfully!'); window.location='books.php';</script>";
    } else {
        echo "<script>alert('Error: NFC UID already exists in system.');</script>";
    }
}


if (isset($_POST['make_copy'])) {
    $book_id = $_POST['existing_book_id'];
    $row_id = $_POST['row_id'];
    $nfc_uid = $_POST['nfc_uid'];

    $stmt_copy = $conn->prepare("INSERT INTO book_copies (book_id, row_id, nfc_uid) VALUES (?, ?, ?)");
    $stmt_copy->bind_param("iis", $book_id, $row_id, $nfc_uid);

    if ($stmt_copy->execute()) {
        echo "<script>alert('Extra copy added successfully'); window.location='books.php';</script>";
    } else {
        echo "<script>alert('Error: Duplicate NFC UID.');</script>";
    }
}


if (isset($_GET['delete_copy'])) {
    $id = $_GET['delete_copy'];
    // Delete history first to avoid SQL Foreign Key constraint errors
    $stmt_history = $conn->prepare("DELETE FROM borrows WHERE copy_id = ?");
    $stmt_history->bind_param("i", $id);
    $stmt_history->execute();

    $stmt_del = $conn->prepare("DELETE FROM book_copies WHERE copy_id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();

    echo "<script>window.location='books.php';</script>";
}


$query = "SELECT b.book_id, b.title, b.isbn, bc.copy_id, bc.nfc_uid, bc.status FROM book_copies bc JOIN books b ON bc.book_id = b.book_id ORDER BY bc.copy_id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Books Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #F2E8CF; color: #386641; }
        .content { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .card { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.05); margin-bottom: 30px; border: 1px solid #6A994E; }
        h2 { color: #386641; font-size: 1.8rem; margin-bottom: 20px; text-transform: uppercase; }
        h3 { color: #6A994E; margin-bottom: 15px; border-left: 5px solid #A7C957; padding-left: 10px; }

    
        .grid-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        form { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        input, select, textarea { padding: 10px; border: 1px solid #A7C957; border-radius: 6px; outline: none; background: #fff; color: #386641; }
        input:focus, select:focus { border-color: #386641; background: #FBFAF5; }
        textarea { grid-column: span 2; height: 60px; }
        .full { grid-column: span 2; }

        button { background: #386641; color: #F2E8CF; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #6A994E; }

       
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { background: #386641; color: #F2E8CF; padding: 12px; text-align: left; font-size: 13px; }
        td { background: #fff; padding: 12px; border-top: 1px solid #F2E8CF; border-bottom: 1px solid #F2E8CF; }
        td:first-child { border-radius: 8px 0 0 8px; border-left: 1px solid #F2E8CF; }
        td:last-child { border-radius: 0 8px 8px 0; border-right: 1px solid #F2E8CF; }

        .btn { text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .btn-edit { background: #A7C957; color: #386641; }
        .btn-del { background: #BC4749; color: white; }
    </style>
</head>
<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h2>Books Management</h2>

        <div class="grid-container">
            <div class="card">
                <h3>Add New Book Title</h3>
                <form method="POST">
                    <input type="text" name="title" placeholder="Book Title" required>
                    <input type="text" name="subtitle" placeholder="Subtitle">
                    <input type="text" name="isbn" placeholder="ISBN Number">
                    <input type="text" name="publisher" placeholder="Publisher">
                    <input type="text" name="edition" placeholder="Edition">
                    <input type="text" name="language" placeholder="Language">
                    <input type="number" name="publication_year" placeholder="Year">
                    <input type="number" name="pages" placeholder="Pages">
                    
                    <select name="category_id" required>
                        <option value="">Category</option>
                        <?php $c_res = $conn->query("SELECT * FROM book_categories"); while($c = $c_res->fetch_assoc()) echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>"; ?>
                    </select>

                    <select name="author_id">
                        <option value="">Author</option>
                        <?php $a_res = $conn->query("SELECT * FROM authors"); while($a = $a_res->fetch_assoc()) echo "<option value='{$a['author_id']}'>{$a['author_name']}</option>"; ?>
                    </select>

                    <textarea name="summary" placeholder="Summary"></textarea>
                    <textarea name="description" placeholder="Description"></textarea>
                    
                    <select name="row_id" required>
                        <option value="">Row Location</option>
                        <?php $r_res = $conn->query("SELECT * FROM shelf_rows"); while($r = $r_res->fetch_assoc()) echo "<option value='{$r['row_id']}'>Row {$r['row_id']}</option>"; ?>
                    </select>

                    <input type="text" name="nfc_uid" placeholder="Scan NFC UID" style="border: 2px solid #386641" required>
                    
                    <button type="submit" name="add_book" class="full">Register Book & NFC</button>
                </form>
            </div>

            <div class="card">
                <h3>Make Extra Copy</h3>
                <form method="POST" style="display:flex; flex-direction:column;">
                    <select name="existing_book_id" required>
                        <option value="">Select Existing Title</option>
                        <?php $b_res = $conn->query("SELECT book_id, title FROM books ORDER BY title ASC"); while($b = $b_res->fetch_assoc()) echo "<option value='{$b['book_id']}'>{$b['title']}</option>"; ?>
                    </select>
                    <select name="row_id" required>
                        <option value="">Shelf Row</option>
                        <?php $r_res2 = $conn->query("SELECT * FROM shelf_rows"); while($r2 = $r_res2->fetch_assoc()) echo "<option value='{$r2['row_id']}'>Row {$r2['row_id']}</option>"; ?>
                    </select>
                    <input type="text" name="nfc_uid" placeholder="New NFC Tag ID" style="border: 2px solid #386641" required>
                    <button type="submit" name="make_copy">Add Additional Copy</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3>Inventory Table</h3>
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
                        <td><span style="font-size:10px; color:#6A994E; font-weight:bold;"><?= strtoupper($row['status']) ?></span></td>
                        <td>
                            <a href="edit_book.php?id=<?= $row['book_id'] ?>" class="btn btn-edit">EDIT</a>
                            <a href="books.php?delete_copy=<?= $row['copy_id'] ?>" class="btn btn-del" onclick="return confirm('Delete copy and borrowing history?')">DELETE</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>