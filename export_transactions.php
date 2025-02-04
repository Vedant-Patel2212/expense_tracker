<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['userId'])) {
    header('Location: index.html');
    exit();
}

$user_id = $_SESSION['userId'];
$query = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "SELECT 'Income' as type, amount, description, date, category FROM income WHERE user_id = ? AND date BETWEEN ? AND ?
              UNION ALL
              SELECT 'Expense' as type, amount, description, date, category FROM expenses WHERE user_id = ? AND date BETWEEN ? AND ?
              ORDER BY date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", $user_id, $start_date, $end_date, $user_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();


    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, array('Type', 'Amount', 'Description', 'Date', 'Category'));

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Export Transactions - Expense Tracker</title>
    <link rel="stylesheet" href="mycss.css">
</head>
<body>
    <div class="sidebar">
        <div class="profile-section">
            <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
            <div class="user-subtitle">Your Money</div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="add_income.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    Add Income
                </a>
            </li>
            <li class="nav-item">
                <a href="add_expense.php" class="nav-link">
                    <i class="fas fa-minus-circle"></i>
                    Add Expense
                </a>
            </li>
            <li class="nav-item">
                <a href="export_transactions.php" class="nav-link active">
                    <i class="fas fa-file-export"></i>
                    Export Transactions
                </a>
            </li>
        </ul>
        <a href="logout.php" class="sign-out">
            <i class="fas fa-sign-out-alt"></i>
            Sign Out
        </a>
    </div>

    <div class="main-content">
        <div class="content-wrapper">
            <h1>Export Transactions</h1>
            <form action="export_transactions.php" method="POST" class="export-form">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                <button type="submit" class="btn-submit">Export Transactions</button>
            </form>
            <div class="export-info">
                <h2>Export Information</h2>
                <p>This feature allows you to export your transactions as a CSV file. The exported file will include the following information for each transaction:</p>
                <ul>
                    <li>Type (Income or Expense)</li>
                    <li>Amount</li>
                    <li>Description</li>
                    <li>Date</li>
                    <li>Category</li>
                </ul>
                <p>Select a date range to export transactions from that period. The exported file will be named "transactions_YYYY-MM-DD.csv" where YYYY-MM-DD is the current date.</p>
            </div>
        </div>
    </div>
</body>
</html>

