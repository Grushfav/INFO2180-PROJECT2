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

// Validate required fields
if ($contact_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'contact_id is required']);
    exit;
}

if ($comment === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Comment is required']);
    exit;
}

// Sanitize comment (strip HTML tags, but preserve line breaks for notes)
$comment = strip_tags($comment);
// Limit comment length (TEXT field in MySQL can be large, but we'll limit to 65535 chars)
if (strlen($comment) > 65535) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Comment is too long. Maximum 65535 characters allowed']);
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

// Prefer storing creator in `created_by` column. Fall back to `user_id` if necessary.
$colCheck = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='notes' AND (COLUMN_NAME='created_by' OR COLUMN_NAME='user_id')");
$colCheck->execute();
$colRes = $colCheck->get_result();
$cols = [];
while ($c = $colRes->fetch_assoc()) {
    $cols[] = $c['COLUMN_NAME'];
}
$colCheck->close();

$hasCreatedBy = in_array('created_by', $cols, true);
$hasUserIdColumn = in_array('user_id', $cols, true);

if ($hasCreatedBy) {
    $stmt = $conn->prepare("INSERT INTO notes (contact_id, comment, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param('isi', $contact_id, $comment, $userId);
} elseif ($hasUserIdColumn) {
    // Backward compatibility: store in user_id if created_by not present
    $stmt = $conn->prepare("INSERT INTO notes (contact_id, comment, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param('isi', $contact_id, $comment, $userId);
} else {
    $stmt = $conn->prepare("INSERT INTO notes (contact_id, comment) VALUES (?, ?)");
    $stmt->bind_param('is', $contact_id, $comment);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Note added']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}
$stmt->close();
?>
