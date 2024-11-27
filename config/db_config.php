<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'farmcs');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
if (!$conn->select_db(DB_NAME)) {
    die("Error selecting database: " . $conn->error);
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    farm_type VARCHAR(100) NOT NULL,
    farm_size DECIMAL(10,2) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    session_token VARCHAR(64) NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_session_token (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Create admin_credentials table if not exists
$sql = "CREATE TABLE IF NOT EXISTS admin_credentials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === FALSE) {
    die("Error creating admin table: " . $conn->error);
}

// Check if admin exists, if not create default admin
$checkAdmin = $conn->query("SELECT COUNT(*) as count FROM admin_credentials");
$row = $checkAdmin->fetch_assoc();

if ($row['count'] == 0) {
    // Create default admin account (username: admin, password: admin123)
    $defaultUsername = 'admin';
    $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admin_credentials (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $defaultUsername, $defaultPassword);
    
    if ($stmt->execute() === FALSE) {
        die("Error creating default admin: " . $conn->error);
    }
    
    $stmt->close();
}
?>
