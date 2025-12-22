<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'admin_project2');
define('DB_PASSWORD', 'Password123');
define('DB_NAME', 'dolphin_crm');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
