<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Security check
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

$contact_id = $_POST['id'] ?? null;

if (!$contact_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Contact ID is required']);
    exit;
}

// Verify contact belongs to current user
$verifyStmt = $conn->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
$verifyStmt->bind_param("ii", $contact_id, $_SESSION['user_id']);
$verifyStmt->execute();
$verifyStmt->store_result();

if ($verifyStmt->num_rows === 0) {
    $verifyStmt->close();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this contact']);
    exit;
}
$verifyStmt->close();

// Delete the contact
$stmt = $conn->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $contact_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Contact deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error deleting contact']);
}

$stmt->close();
?>