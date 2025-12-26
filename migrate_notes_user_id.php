<?php
/**
 * Migration script to populate user_id for existing notes
 * This assigns each note to the user who owns the contact
 */

require_once __DIR__ . '/config.php';

// Check if user_id column exists in notes table
$checkStmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='notes' AND COLUMN_NAME='user_id'");
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$hasUserIdColumn = ($checkResult->num_rows > 0);
$checkStmt->close();

if (!$hasUserIdColumn) {
    echo json_encode(['success' => false, 'error' => 'user_id column does not exist in notes table. Run migrate_db.php first.']);
    exit;
}

// Update all notes with NULL user_id to have the contact owner's user_id
$updateStmt = $conn->prepare("
    UPDATE notes n 
    JOIN contacts c ON n.contact_id = c.id 
    SET n.user_id = c.user_id 
    WHERE n.user_id IS NULL
");

if ($updateStmt->execute()) {
    $affectedRows = $conn->affected_rows;
    echo json_encode([
        'success' => true, 
        'message' => "Migration completed. Updated $affectedRows notes with user_id from their contact owner."
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Migration failed: ' . $conn->error]);
}

$updateStmt->close();
$conn->close();
?>
