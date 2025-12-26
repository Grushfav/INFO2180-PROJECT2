<?php
require_once __DIR__ . '/config.php';

echo "<h2>Debugging Notes user_id Issue</h2>";

// 1. Check if user_id column exists in notes table
echo "<h3>1. Column Existence Check:</h3>";
$checkStmt = $conn->prepare("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='notes' AND COLUMN_NAME='user_id'");
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
if ($checkResult->num_rows > 0) {
    $colInfo = $checkResult->fetch_assoc();
    echo "<pre>";
    print_r($colInfo);
    echo "</pre>";
} else {
    echo "<strong style='color:red'>ERROR: user_id column does NOT exist in notes table!</strong>";
}
$checkStmt->close();

// 2. Check notes table full structure
echo "<h3>2. Full Notes Table Structure:</h3>";
$result = $conn->query("DESCRIBE notes");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Check sample notes
echo "<h3>3. Sample Notes (first 5):</h3>";
$result = $conn->query("SELECT id, contact_id, user_id, SUBSTRING(comment, 1, 50) as comment_preview, created_at FROM notes LIMIT 5");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Contact ID</th><th>User ID</th><th>Comment</th><th>Created At</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['contact_id'] . "</td>";
    echo "<td>" . ($row['user_id'] ?? '<span style="color:red">NULL</span>') . "</td>";
    echo "<td>" . htmlspecialchars($row['comment_preview']) . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Check NULL user_id count
echo "<h3>4. Notes with NULL user_id:</h3>";
$result = $conn->query("SELECT COUNT(*) as null_count FROM notes WHERE user_id IS NULL");
$row = $result->fetch_assoc();
echo "<p><strong style='color:" . ($row['null_count'] > 0 ? 'red' : 'green') . "'>" . $row['null_count'] . " notes have NULL user_id</strong></p>";

// 5. Test get_notes query
echo "<h3>5. Test get_notes Query (for contact_id=1):</h3>";
$result = $conn->query("SELECT n.id, n.comment, n.created_at, COALESCE(CONCAT(u.firstname, ' ', u.lastname), 'Unknown') as author FROM notes n LEFT JOIN Users u ON n.user_id = u.id WHERE n.contact_id = 1 ORDER BY n.created_at DESC LIMIT 3");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Comment</th><th>Author</th><th>Created At</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['comment'], 0, 30)) . "...</td>";
    echo "<td><strong>" . $row['author'] . "</strong></td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

?>
