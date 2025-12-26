<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Security check
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

$stmt = $conn->prepare("SELECT id, firstname, lastname, email, telephone, company, title, type, user_id, created_at FROM contacts ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

$contacts = [];
while ($row = $result->fetch_assoc()) {
    $contacts[] = $row;
}

echo json_encode(['success' => true, 'contacts' => $contacts, 'current_user_id' => $_SESSION['user_id']]);
$stmt->close();
?>