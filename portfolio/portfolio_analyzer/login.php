<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Email found, create session
        $_SESSION['user_id'] = $row['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        // Email not found
        header("Location: login.html?error=notfound");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>
