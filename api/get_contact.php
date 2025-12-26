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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id']);
    exit;
}

// Fetch contact and owner info
$stmt = $conn->prepare("SELECT c.id, c.firstname, c.lastname, c.email, c.telephone, c.company, c.title, c.type, c.created_at, c.updated_at, c.user_id, CONCAT(u.firstname, ' ', u.lastname) as owner_fullname FROM contacts c LEFT JOIN Users u ON c.user_id = u.id WHERE c.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();
$stmt->close();

if (!$contact) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Contact not found']);
    exit;
}

// Authorization: owner or admin only
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? 'Member');
if ($contact['user_id'] != $userId && $userRole !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

// Return contact
echo json_encode(['success' => true, 'contact' => $contact]);
?>
