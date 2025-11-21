<?php
// Database configuration
$host = "localhost";
$dbname = "personal_website";
$username = "root";
$password = "Guru@123";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default admin user if not exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $pdo->prepare("INSERT INTO users (username, password) VALUES ('admin', 'admin123')")->execute();
}
?>