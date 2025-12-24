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

$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$company = $_POST['company'] ?? '';
$title = $_POST['title'] ?? '';
$type = $_POST['type'] ?? '';

// Validate required fields
if (empty($firstname) || empty($lastname) || empty($email) || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'First name, last name, email, and type are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit;
}

// Validate type
if (!in_array($type, ['Client', 'Lead'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid contact type']);
    exit;
}

// Check if contact with this email already exists for this user
$checkStmt = $conn->prepare("SELECT id FROM contacts WHERE email = ? AND user_id = ?");
$checkStmt->bind_param("si", $email, $_SESSION['user_id']);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A contact with this email already exists']);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Insert contact
$stmt = $conn->prepare("INSERT INTO contacts (user_id, firstname, lastname, email, telephone, company, title, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssss", $_SESSION['user_id'], $firstname, $lastname, $email, $telephone, $company, $title, $type);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Contact added successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error adding contact']);
}

$stmt->close();
