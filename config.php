<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'admin_project2');
define('DB_PASSWORD', 'Password123');
define('DB_NAME', 'dolphin_crm');

// Create connection
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
   
    die("Database connection failed: " . $conn->connect_error . ".\nPlease ensure:\n1. MySQL service is running in XAMPP\n2. Database 'dolphin_crm' exists\n3. schema.sql has been imported\n\nFor help, see INSTALL.md");
}


$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
