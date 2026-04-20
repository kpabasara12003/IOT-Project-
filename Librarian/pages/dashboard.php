<?php include('../components/auth_check.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include('../components/navbar.php'); ?>
    
    <div class="content">
        <h2>Welcome, <?= $_SESSION['name'] ?> 👋</h2>

        <p>Select an option for the sidebar</p>
    </div>
</body>
</html>