<?php
require_once __DIR__ . '/config.php';

// Check notes table structure
$result = $conn->query("DESCRIBE notes");
echo "<h3>Notes Table Structure:</h3><pre>";
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "</pre>";

// Check notes data
$result = $conn->query("SELECT id, contact_id, comment, user_id, created_at FROM notes ORDER BY id DESC LIMIT 5");
echo "<h3>Recent Notes (last 5):</h3><pre>";
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "</pre>";

// Check column existence
$checkStmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='notes' AND COLUMN_NAME='user_id'");
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$hasUserIdColumn = ($checkResult->num_rows > 0);
echo "<h3>Has user_id column: " . ($hasUserIdColumn ? "YES" : "NO") . "</h3>";

$checkStmt->close();
?>
