<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // Start output buffering
session_start();
include 'db_connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    exit("Please provide email and password.");
}

$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($password === $row['password']) {  
        $_SESSION['userId'] = $row['id'];
        $_SESSION['username'] = $row['username'];

        header("Location: dashboard.php");
        exit();
    } else {
        exit("Invalid password.");
    }
} else {
    exit("No account found with this email.");
}

$stmt->close();
$conn->close();
ob_end_flush(); // End output buffering
?>
