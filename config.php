<?php
// Database configuration
// XAMPP Default: root user with empty password
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dolphin_crm');

// Create connection
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Show a helpful message but don't expose credentials
    die("Database connection failed: " . $conn->connect_error . ".\nPlease ensure:\n1. MySQL service is running in XAMPP\n2. Database 'dolphin_crm' exists\n3. schema.sql has been imported\n\nFor help, see INSTALL.md");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
