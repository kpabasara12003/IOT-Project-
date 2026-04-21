<?php 
include('../components/auth_check.php');
include('../config/db.php');

$student_details = null;
$active_borrows = [];
$past_history = [];

// SEARCH LOGIC
if (isset($_GET['search_student'])) {
    $search = $_GET['search_student'];
    
    $stmt = $conn->prepare("
        SELECT s.*, sa.balance, sa.type 
        FROM students s 
        LEFT JOIN student_accounts sa ON s.student_id = sa.student_id 
        WHERE s.student_number = ?
    ");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $student_details = $stmt->get_result()->fetch_assoc();

    if ($student_details) {
        $sid = $student_details['student_id'];
        
        $active_stmt = $conn->prepare("
            SELECT b.*, bk.title 
            FROM borrows b 
            JOIN book_copies bc ON b.copy_id = bc.copy_id 
            JOIN books bk ON bc.book_id = bk.book_id 
            WHERE b.student_id = ? AND b.returned_at IS NULL
        ");
        $active_stmt->bind_param("i", $sid);
        $active_stmt->execute();
        $active_borrows = $active_stmt->get_result();

        $history_stmt = $conn->prepare("
        SELECT b.*, bk.title 
        FROM borrows b 
        JOIN book_copies bc ON b.copy_id = bc.copy_id 
        JOIN books bk ON bc.book_id = bk.book_id 
        WHERE b.student_id = ? AND b.returned_at IS NOT NULL
        ORDER BY b.returned_at DESC
    ");
        $history_stmt->bind_param("i", $sid); 
        $history_stmt->execute();             
        $past_history = $history_stmt->get_result(); 
    }
}

// MANAGE OVERDUE CHARGES & LOG TRANSACTION
if (isset($_POST['collect_payment'])) {
    $student_id = $_POST['student_id'];
    $payment = (float)$_POST['payment_amount'];

    $acc_query = $conn->prepare("SELECT balance, type FROM student_accounts WHERE student_id = ?");
    $acc_query->bind_param("i", $student_id);
    $acc_query->execute();
    $acc = $acc_query->get_result()->fetch_assoc();

    if ($acc) {
        $current_bal = (float)$acc['balance'];
        $new_balance = 0;
        $new_type = 'credit';

        if ($acc['type'] == 'fine') {
            if ($payment >= $current_bal) {
                $new_balance = $payment - $current_bal;
                $new_type = 'credit';
            } else {
                $new_balance = $current_bal - $payment;
                $new_type = 'fine';
            }
        } else {
            $new_balance = $current_bal + $payment;
            $new_type = 'credit';
        }

        $update = $conn->prepare("UPDATE student_accounts SET balance = ?, type = ? WHERE student_id = ?");
        $update->bind_param("dsi", $new_balance, $new_type, $student_id);
        $update->execute();

        $log_stmt = $conn->prepare("INSERT INTO transactions (student_id, amount, type) VALUES (?, ?, 'credit')");
        $log_stmt->bind_param("id", $student_id, $payment);
        $log_stmt->execute();

        echo "<script>alert('Payment Successful and Logged!'); window.location='borrows.php?search_student=".$_POST['student_no_hidden']."';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library | Student Records</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #F2E8CF; color: #386641; }
        .content { max-width: 1100px; margin: 40px auto; padding: 20px; }
        h3 { border-left: 6px solid #A7C957; padding-left: 15px; text-transform: uppercase; margin: 30px 0 20px; }

        .search-box { background: #fff; padding: 20px; border-radius: 15px; display: flex; gap: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .search-box input { flex: 1; padding: 12px; border: 2px solid #F2E8CF; border-radius: 8px; outline: none; }
        .btn-search { background: #386641; color: white; border: none; padding: 0 25px; border-radius: 8px; cursor: pointer; }

        .student-card { background: #fff; padding: 25px; border-radius: 15px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; border-top: 5px solid #386641; }
        .balance-badge { padding: 10px 20px; border-radius: 10px; font-weight: bold; text-align: center; }
        .bg-fine { background: #fee2e2; color: #BC4749; }
        .bg-credit { background: #ecfdf5; color: #386641; }

        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { background: #386641; color: #F2E8CF; padding: 15px; text-align: left; font-size: 13px; }
        td { background: #fff; padding: 15px; border-top: 1px solid #F2E8CF; border-bottom: 1px solid #F2E8CF; }
        .status-pill { padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: bold; }
        .pill-red { background: #BC4749; color: white; }
        .pill-green { background: #A7C957; color: #386641; }
    </style>
</head>
<body>
    <?php include('../components/navbar.php'); ?>

    <div class="content">
        <h3>Search Student Records</h3>
        <form method="GET" class="search-box">
            <input type="text" name="search_student" placeholder="Enter Student Number (e.g., ST12345)" value="<?= $_GET['search_student'] ?? '' ?>">
            <button type="submit" class="btn-search">View Records</button>
        </form>

        <?php if ($student_details): ?>
            <div class="student-card">
                <div>
                    <h2><?= htmlspecialchars($student_details['full_name']) ?></h2>
                    <p>Student No: <?= htmlspecialchars($student_details['student_number']) ?></p>
                </div>
                <div class="balance-badge <?= ($student_details['type'] ?? 'fine') == 'fine' ? 'bg-fine' : 'bg-credit' ?>">
                    <small><?= strtoupper($student_details['type'] ?? 'fine') ?> BALANCE</small><br>
                    Rs. <?= number_format((float)($student_details['balance'] ?? 0), 2) ?>
            </div>
            </div>

            <h3>Settle Fines / Add Credit</h3>
            <form action="borrows.php" method="POST" class="search-box" style="border-top: 5px solid #BC4749;">
                <input type="hidden" name="student_id" value="<?= $student_details['student_id'] ?>">
                <input type="hidden" name="student_no_hidden" value="<?= htmlspecialchars($student_details['student_number']) ?>">              
                <input type="number" step="0.01" name="payment_amount" placeholder="Amount (Rs.)" required>
                <button type="submit" name="collect_payment" class="btn-search" style="background:#BC4749;">Update Account</button>
            </form>

                    <h3>Transaction History (Payments & Fines)</h3>
<table>
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th>Amount (Rs.)</th>
            <th>Type</th>
            <th>Date & Time</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $trans_stmt = $conn->prepare("SELECT * FROM transactions WHERE student_id = ? ORDER BY created_at DESC");
        $trans_stmt->bind_param("i", $student_details['student_id']);
        $trans_stmt->execute();
        $transactions = $trans_stmt->get_result();

        if ($transactions->num_rows > 0) {
            while ($t = $transactions->fetch_assoc()) {
                $is_credit = ($t['type'] == 'credit');
                ?>
                <tr>
                    <td>#<?= $t['transaction_id'] ?></td>
                    <td style="font-weight: bold; color: <?= $is_credit ? '#386641' : '#BC4749' ?>;">
                        <?= $is_credit ? '+ ' : '- ' ?> Rs. <?= number_format($t['amount'], 2) ?>
                    </td>
                    <td>
                        <span class="status-pill <?= $is_credit ? 'pill-green' : 'pill-red' ?>">
                            <?= strtoupper($t['type']) ?>
                        </span>
                    </td>
                    <td style="font-size: 13px; color: #666;">
                        <?= date('M d, Y | h:i A', strtotime($t['created_at'])) ?>
                    </td>
                </tr>
                <?php 
            }
        } else {
            echo "<tr><td colspan='4' style='text-align:center;'>No transaction records found for this student.</td></tr>";
        }
        ?>
    </tbody>
</table>

            <h3>Active Borrows</h3>
            <table>
                <thead><tr><th>Book Title</th><th>Copy ID</th><th>Due Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while ($row = $active_borrows->fetch_assoc()): 
                        $overdue = strtotime($row['due_date']) < time(); ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                            <td>#<?= $row['copy_id'] ?></td>
                            <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            <td><span class="status-pill <?= $overdue ? 'pill-red' : 'pill-green' ?>"><?= $overdue ? 'OVERDUE' : 'ON TIME' ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h3>Returned History</h3>
            <table>
                <thead><tr><th>Book Title</th><th>Borrowed</th><th>Returned</th></tr></thead>
                <tbody>
                    <?php while ($row = $past_history->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['borrowed_at'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['returned_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif(isset($_GET['search_student'])): ?>
            <p style="margin-top:20px;">No student found with that number.</p>
        <?php endif; ?>
    </div>
</body>
</html>