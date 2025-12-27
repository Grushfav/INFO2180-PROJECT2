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

// Sanitize and trim input
$firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : '';

// Sanitize text fields (strip HTML tags and encode special characters)
$firstname = htmlspecialchars(strip_tags($firstname), ENT_QUOTES, 'UTF-8');
$lastname = htmlspecialchars(strip_tags($lastname), ENT_QUOTES, 'UTF-8');
$company = htmlspecialchars(strip_tags($company), ENT_QUOTES, 'UTF-8');
$title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');
$telephone = htmlspecialchars(strip_tags($telephone), ENT_QUOTES, 'UTF-8');

// Validate required fields
$errors = [];
if (empty($firstname)) $errors[] = 'First name is required';
if (empty($lastname)) $errors[] = 'Last name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($type)) $errors[] = 'Type is required';

// Validate field lengths (matching database schema)
if (strlen($firstname) > 50) $errors[] = 'First name must be 50 characters or less';
if (strlen($lastname) > 50) $errors[] = 'Last name must be 50 characters or less';
if (strlen($email) > 100) $errors[] = 'Email must be 100 characters or less';
if (strlen($telephone) > 20) $errors[] = 'Telephone must be 20 characters or less';
if (strlen($company) > 100) $errors[] = 'Company must be 100 characters or less';
if (strlen($title) > 10) $errors[] = 'Title must be 10 characters or less';

// Validate email format
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Validate telephone format (if provided)
if (!empty($telephone) && !preg_match('/^[\d\s\-\+\(\)]+$/', $telephone)) {
    $errors[] = 'Invalid telephone format. Only digits, spaces, hyphens, plus signs, and parentheses are allowed';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode('; ', $errors)]);
    exit;
}

// Validate type
if (!in_array($type, ['Sales Lead', 'Support'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid contact type. Must be "Sales Lead" or "Support"']);
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
?>