<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Default XAMPP/WAMP password

// 1. Create connection to MySQL
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS portfolio_analyzer";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db('portfolio_analyzer');

// 3. Define the full schema (Combined from all SQL files)
$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        github VARCHAR(255) NOT NULL,
        resume_path VARCHAR(255),
        profile_image VARCHAR(255) DEFAULT NULL,
        tagline VARCHAR(255) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        location VARCHAR(255) DEFAULT NULL,
        career_objective TEXT DEFAULT NULL,
        linkedin VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS skills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        skill_name VARCHAR(100) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        project_title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        technologies VARCHAR(255) DEFAULT NULL,
        github_link VARCHAR(255) DEFAULT NULL,
        live_demo VARCHAR(255) DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS analysis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        score INT NOT NULL,
        suggestions TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS analysis_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        score INT NOT NULL,
        suggestions TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table/Query executed successfully.<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

echo "<br><strong>Setup Complete!</strong> You can now <a href='form.html'>Create a Portfolio</a>.";
$conn->close();
?>
