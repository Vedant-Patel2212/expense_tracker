<?php
include 'db_connect.php';

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$username || !$email || !$password || !$confirm_password) {
    exit("All fields are required.");
}

if ($password !== $confirm_password) {
    exit("Passwords do not match.");
}

$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password);

if ($stmt->execute()) {
    header("Location: index.php");
    exit();
} else {
    exit("Error creating account.");
}

$stmt->close();
$conn->close();
?>
