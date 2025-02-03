<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['userId'])) {
    header('Location: index.php');
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $category = $_POST['category'];

    $query = "INSERT INTO income (user_id, title, amount, description, date, category) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdsss", $user_id, $title, $amount, $description, $date, $category);
    
    if ($stmt->execute()) {
        $success_message = "Income added successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Income - Expense Tracker</title>
    <link rel="stylesheet" href="mycss.css">
</head>
<body>
    <div class="sidebar">
        <div class="profile-section">
            <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="add_income.php" class="nav-link active">
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
                <a href="export_transactions.php" class="nav-link">
                    <i class="fas fa-list"></i>
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
            <h1>Add Income</h1>
            <?php
            if (isset($success_message)) {
                echo "<div class='alert success'>$success_message</div>";
            }
            if (isset($error_message)) {
                echo "<div class='alert error'>$error_message</div>";
            }
            ?>
            <form action="add_income.php" method="POST" class="add-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" id="amount" name="amount" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" ></textarea>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" required>
                </div>
                <button type="submit" class="btn-submit">Add Income</button>
            </form>
        </div>
    </div>
</body>
</html>