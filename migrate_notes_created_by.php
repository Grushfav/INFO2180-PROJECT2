<?php
/**
 * Migration script to add `created_by` column to `notes` and populate it.
 * Run once via browser or CLI: php migrate_notes_created_by.php
 */

require_once __DIR__ . '/config.php';

echo "<h2>Migrate notes: add created_by</h2>";

// 1. Check if created_by exists
$check = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='notes' AND COLUMN_NAME='created_by'");
$check->execute();
$res = $check->get_result();
$exists = ($res->num_rows > 0);
$check->close();

if (!$exists) {
    echo "<p>Adding `created_by` column...</p>";
    $sql = "ALTER TABLE `notes` ADD COLUMN `created_by` INT NULL AFTER `comment`";
    if ($conn->query($sql)) {
        echo "<p style='color:green'>✓ created_by column added</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to add column: " . htmlspecialchars($conn->error) . "</p>";
        exit;
    }
} else {
    echo "<p style='color:green'>✓ created_by column already exists</p>";
}

// 2. Populate created_by from user_id if present
$hasUserId = false;
$c = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='notes' AND COLUMN_NAME='user_id'");
$c->execute();
$cr = $c->get_result();
if ($cr->num_rows > 0) $hasUserId = true;
$c->close();

if ($hasUserId) {
    echo "<p>Populating created_by from existing user_id values where created_by IS NULL...</p>";
    $uSql = "UPDATE notes SET created_by = user_id WHERE created_by IS NULL AND user_id IS NOT NULL";
    if ($conn->query($uSql)) {
        echo "<p style='color:green'>✓ Populated created_by from user_id for " . $conn->affected_rows . " rows</p>";
    } else {
        echo "<p style='color:red'>✗ Failed populating from user_id: " . htmlspecialchars($conn->error) . "</p>";
    }
} else {
    echo "<p>No existing user_id column found. Attempting to populate from contact owner.</p>";
    // Populate from contact owner
    $mSql = "UPDATE notes n JOIN contacts c ON n.contact_id = c.id SET n.created_by = c.user_id WHERE n.created_by IS NULL";
    if ($conn->query($mSql)) {
        echo "<p style='color:green'>✓ Populated created_by from contacts for " . $conn->affected_rows . " rows</p>";
    } else {
        echo "<p style='color:red'>✗ Failed populating from contacts: " . htmlspecialchars($conn->error) . "</p>";
    }
}

// 3. Add foreign key constraint if not present
$fkCheck = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'notes' AND COLUMN_NAME = 'created_by' AND REFERENCED_TABLE_NAME = 'Users'");
if ($fkCheck && $fkCheck->num_rows === 0) {
    echo "<p>Adding foreign key constraint on created_by...</p>";
    $sql = "ALTER TABLE `notes` ADD CONSTRAINT `fk_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `Users`(`id`) ON DELETE SET NULL";
    if ($conn->query($sql)) {
        echo "<p style='color:green'>✓ Foreign key added</p>";
    } else {
        echo "<p style='color:orange'>⚠ Could not add foreign key: " . htmlspecialchars($conn->error) . "</p>";
    }
} else {
    echo "<p style='color:green'>✓ Foreign key on created_by already exists</p>";
}

echo "<p>Migration complete.</p>";

$conn->close();

?>
