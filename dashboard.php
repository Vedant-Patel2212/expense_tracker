<?php
ob_start(); 
require_once 'db_connect.php';

if (!isset($_SESSION['userId'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['userId']; 

$query_income = "SELECT SUM(amount) as total_income FROM income WHERE user_id = $user_id";
$query_expense = "SELECT SUM(amount) as total_expense FROM expenses WHERE user_id = $user_id";

$result_income = mysqli_query($conn, $query_income);
$result_expense = mysqli_query($conn, $query_expense);

$total_income = mysqli_fetch_assoc($result_income)['total_income'] ?? 0;
$total_expense = mysqli_fetch_assoc($result_expense)['total_expense'] ?? 0;

$query = "SELECT date, 'income' as type, amount FROM income WHERE user_id = $user_id
          UNION ALL
          SELECT date, 'expense' as type, amount FROM expenses WHERE user_id = $user_id
          ORDER BY date DESC LIMIT 10";
$result = mysqli_query($conn, $query);
$transactions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expense Tracker - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .container {
            display: flex;
            width: 100%;
        }
        .sidebar {
            width: 250px;
            background: #ffffff;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .nav-links {
            flex-grow: 1;
        }
        .nav-links a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: black;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .nav-links a:hover, .nav-links .active {
            background: #007bff;
            color: white;
        }
        .sign-out {
            margin-top: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1;
        }
        .chart-container {
            flex-grow: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="profile-section">
                <h3><?php echo $_SESSION['username'] ?? 'User'; ?></h3>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="add_income.php">Add Income</a>
                <a href="add_expense.php">Add Expense</a>
                <a href="export_transactions.php">Export Transactions</a>
            </div>
            <div class="sign-out">
                <a href="logout.php" style="background: red; color: white;">Sign Out</a>
            </div>
        </div>

        <div class="main-content">
            <h1>Dashboard</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Income</h3>
                    <p>₹<?php echo number_format($total_income, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Expense</h3>
                    <p>₹<?php echo number_format($total_expense, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Balance</h3>
                    <p>₹<?php echo number_format($total_income - $total_expense, 2); ?></p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="transactionChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        var dates = [<?php
            $dateArray = array_map(fn($t) => "'" . $t['date'] . "'", $transactions);
            echo implode(',', $dateArray);
        ?>];
        var incomes = [<?php
            $incomeArray = array_map(fn($t) => $t['type'] == 'income' ? $t['amount'] : 0, $transactions);
            echo implode(',', $incomeArray);
        ?>];
        var expenses = [<?php
            $expenseArray = array_map(fn($t) => $t['type'] == 'expense' ? $t['amount'] : 0, $transactions);
            echo implode(',', $expenseArray);
        ?>];

        const ctx = document.getElementById('transactionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Income',
                    data: incomes,
                    borderColor: 'green',
                    fill: false
                }, {
                    label: 'Expense',
                    data: expenses,
                    borderColor: 'red',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Income and Expense Trend'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Amount'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
