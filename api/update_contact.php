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

$action = $_POST['action'] ?? null;
// also support JSON body
if (!$action) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($input['id']) ? intval($input['id']) : 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id']);
    exit;
}

// Fetch contact to check permissions
$stmt = $conn->prepare("SELECT user_id FROM contacts WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
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

// Only owner or admin can update
if ($row['user_id'] != $userId && $userRole !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

if ($action === 'assign') {
    // Assign to current user
    $stmt = $conn->prepare("UPDATE contacts SET user_id = ? WHERE id = ?");
    $stmt->bind_param('ii', $userId, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assigned to you']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
    exit;
} elseif ($action === 'switch_type') {
    $newType = $_POST['type'] ?? ($input['type'] ?? '');
    $allowed = ['Sales Lead', 'Support'];
    if (!in_array($newType, $allowed)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid type']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE contacts SET type = ? WHERE id = ?");
    $stmt->bind_param('si', $newType, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Type updated']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
    exit;
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}
?>
