<?php
/**
 * Database Migration Script
 * Run this file once to set up or fix the database schema
 * Then delete it for security
 */

require_once __DIR__ . '/config.php';

echo "=== Dolphin CRM Database Migration ===\n\n";

// Check if contacts table exists and has user_id column
$result = $conn->query("DESCRIBE contacts");
if (!$result) {
    echo "ERROR: contacts table does not exist. Please import schema.sql first.\n";
    exit(1);
}

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[$row['Field']] = $row;
}

echo "Current contacts table structure:\n";
print_r(array_keys($columns));
echo "\n";

// Check if user_id column exists
if (!isset($columns['user_id'])) {
    echo "ERROR: 'user_id' column is missing from contacts table!\n\n";
    echo "Attempting to fix by adding the column...\n";
    
    // Add user_id column
    $sql = "ALTER TABLE `contacts` ADD COLUMN `user_id` INT NOT NULL DEFAULT 1 AFTER `id`";
    if ($conn->query($sql)) {
        echo "✓ Added user_id column\n";
    } else {
        echo "✗ Failed to add user_id: " . $conn->error . "\n";
        exit(1);
    }
} else {
    echo "✓ user_id column exists\n";
}

// Check if foreign key exists
$fk_result = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'contacts' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'Users'");
if ($fk_result && $fk_result->num_rows === 0) {
    echo "Adding foreign key constraint...\n";
    // Drop existing constraint if any
    $conn->query("ALTER TABLE `contacts` DROP FOREIGN KEY `fk_contacts_user_id`");
    
    $sql = "ALTER TABLE `contacts` ADD CONSTRAINT `fk_contacts_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE";
    if ($conn->query($sql)) {
        echo "✓ Added foreign key constraint\n";
    } else {
        echo "✗ Failed to add foreign key: " . $conn->error . "\n";
    }
}

// Check for unique constraint
$uk_result = $conn->query("SHOW INDEXES FROM `contacts` WHERE Key_name = 'uq_contacts_email_user'");
if ($uk_result && $uk_result->num_rows === 0) {
    echo "Adding unique constraint...\n";
    $conn->query("ALTER TABLE `contacts` DROP INDEX `uq_contacts_email_user`");
    
    $sql = "ALTER TABLE `contacts` ADD UNIQUE KEY `uq_contacts_email_user` (`email`, `user_id`)";
    if ($conn->query($sql)) {
        echo "✓ Added unique constraint\n";
    } else {
        echo "✗ Failed to add unique constraint: " . $conn->error . "\n";
    }
}

echo "\n✓ Database migration complete!\n";
echo "You can now safely use the Add Contact feature.\n";
?>
