<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
if ($contact_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid contact id']);
    exit;
}

// Ensure user can view this contact (owner or admin)
$stmt = $conn->prepare("SELECT user_id FROM contacts WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $contact_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Contact not found']);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'Member');
if ($row['user_id'] != $userId && $userRole !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

// Fetch notes with author name
$stmt = $conn->prepare("SELECT n.id, n.comment, n.created_at, CONCAT(u.firstname, ' ', u.lastname) as author FROM notes n LEFT JOIN Users u ON n.user_id = u.id WHERE n.contact_id = ? ORDER BY n.created_at DESC");
$stmt->bind_param('i', $contact_id);
$stmt->execute();
$result = $stmt->get_result();

$notes = [];
while ($r = $result->fetch_assoc()) {
    $notes[] = $r;
}

echo json_encode(['success' => true, 'notes' => $notes]);
?>
