<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Only Admins can view all users
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - admin only']);
    exit();
}

// Get all users using prepared statement for consistency
$stmt = $conn->prepare("SELECT id, CONCAT(firstname, ' ', lastname) as fullname, email, role, created_at FROM Users ORDER BY created_at DESC");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    $stmt->close();
    exit();
}

$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Return under the key 'users' to match frontend expectations
echo json_encode(['success' => true, 'users' => $users]);
?>
