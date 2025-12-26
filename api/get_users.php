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

// Get all users
$query = "SELECT id, CONCAT(firstname, ' ', lastname) as fullname, email, role, created_at FROM Users ORDER BY created_at DESC";
$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return under the key 'users' to match frontend expectations
echo json_encode(['success' => true, 'users' => $users]);
?>
