<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$contact_id = isset($input['contact_id']) ? intval($input['contact_id']) : (isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0);
$comment = isset($input['comment']) ? trim($input['comment']) : (isset($_POST['comment']) ? trim($_POST['comment']) : '');

if ($contact_id <= 0 || $comment === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'contact_id and comment are required']);
    exit;
}

// Ensure user can add note to this contact (owner or admin)
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

// Insert note
$stmt = $conn->prepare("INSERT INTO notes (contact_id, comment, user_id) VALUES (?, ?, ?)");
$stmt->bind_param('isi', $contact_id, $comment, $userId);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Note added']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}
$stmt->close();
?>
