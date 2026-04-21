<?php
include('../config/db.php');
include('../components/auth_check.php');

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$book_id = $_GET['id'];


$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();


$stmt2 = $conn->prepare("SELECT * FROM book_copies WHERE book_id = ? LIMIT 1");
$stmt2->bind_param("i", $book_id);
$stmt2->execute();
$copy = $stmt2->get_result()->fetch_assoc();

if (isset($_POST['update_book'])) {
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
    $stmt->bind_param("ssssssisssii", $title, $subtitle, $isbn, $publisher, $edition, $language, $year, $pages, $summary, $description, $category_id, $book_id);
    $stmt->execute();

    
    $nfc_uid = $_POST['nfc_uid'];
    $row_id = $_POST['row_id'];
    $stmt2 = $conn->prepare("UPDATE book_copies SET nfc_uid=?, row_id=? WHERE book_id=? LIMIT 1");
    $stmt2->bind_param("sii", $nfc_uid, $row_id, $book_id);
    $stmt2->execute();

    echo "<script>alert('Book record updated successfully!'); window.location='books.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book | Library System</title>
    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background-color: #F2E8CF; color: #386641; }
        
        .content { max-width: 900px; margin: 50px auto; padding: 20px; }
        
        .edit-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(56, 102, 65, 0.1);
            border: 1px solid #A7C957;
        }

        h2 { 
            color: #386641; 
            font-size: 2rem; 
            margin-bottom: 30px; 
            display: flex; 
            align-items: center; 
            gap: 15px;
        }

        h3 {
            color: #6A994E;
            font-size: 1.1rem;
            margin: 25px 0 15px 0;
            border-bottom: 2px solid #F2E8CF;
            padding-bottom: 5px;
            grid-column: span 2;
        }

       
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .input-group { display: flex; flex-direction: column; gap: 8px; }
        label { font-size: 0.9rem; font-weight: 600; color: #6A994E; }

        input, select, textarea {
            padding: 12px;
            border: 2px solid #F2E8CF;
            border-radius: 10px;
            font-size: 15px;
            color: #386641;
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #6A994E;
            background-color: #FBFAF5;
            box-shadow: 0 0 8px rgba(106, 153, 78, 0.2);
        }

        textarea { grid-column: span 2; height: 100px; resize: vertical; }
        .full-width { grid-column: span 2; }

        
        .btn-container {
            grid-column: span 2;
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        button {
            flex: 2;
            background-color: #386641;
            color: #F2E8CF;
            border: none;
            padding: 15px;
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover { background-color: #6A994E; transform: translateY(-2px); }

        .btn-back {
            flex: 1;
            background-color: #F2E8CF;
            color: #386641;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-weight: 600;
            border: 2px solid #386641;
        }

        
        .nfc-section {
            background: #F9FBE7;
            padding: 15px;
            border-radius: 12px;
            border: 2px dashed #A7C957;
        }
    </style>
</head>
<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <div class="edit-card">
            <h2>✏️ Edit Book Record</h2>
            
            <form method="POST">
                <div class="input-group">
                    <label>Main Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
                </div>
                <div class="input-group">
                    <label>Subtitle</label>
                    <input type="text" name="subtitle" value="<?= htmlspecialchars($book['subtitle']) ?>">
                </div>

                <div class="input-group">
                    <label>ISBN Number</label>
                    <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                </div>
                <div class="input-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>">
                </div>

                <div class="input-group">
                    <label>Edition</label>
                    <input type="text" name="edition" value="<?= htmlspecialchars($book['edition']) ?>">
                </div>
                <div class="input-group">
                    <label>Language</label>
                    <input type="text" name="language" value="<?= htmlspecialchars($book['language']) ?>">
                </div>
                <div class="input-group">
                    <label>Publication Year</label>
                    <input type="number" name="publication_year" value="<?= $book['publication_year'] ?>">
                </div>
                <div class="input-group">
                    <label>Number of Pages</label>
                    <input type="number" name="pages" value="<?= $book['pages'] ?>">
                </div>

                <div class="input-group full-width">
                    <label>Category</label>
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
                </div>

                <div class="input-group">
                    <label>Brief Summary</label>
                    <textarea name="summary"><?= htmlspecialchars($book['summary']) ?></textarea>
                </div>
                <div class="input-group">
                    <label>Detailed Description</label>
                    <textarea name="description"><?= htmlspecialchars($book['description']) ?></textarea>
                </div>

                <h3>Physical Inventory & NFC</h3>
                
                <div class="input-group">
                    <label>NFC UID (Scan to Update)</label>
                    <input type="text" name="nfc_uid" value="<?= htmlspecialchars($copy['nfc_uid']) ?>" style="border-color: #386641; font-weight: bold;" required>
                </div>
                <div class="input-group">
                    <label>Shelf Location (Row ID)</label>
                    <input type="number" name="row_id" value="<?= $copy['row_id'] ?>" required>
                </div>

                <div class="btn-container">
                    <a href="books.php" class="btn-back">Cancel</a>
                    <button type="submit" name="update_book">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>