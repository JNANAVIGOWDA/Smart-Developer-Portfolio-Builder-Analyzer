<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Default XAMPP password is empty
$dbname = 'portfolio_analyzer';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}
?>
