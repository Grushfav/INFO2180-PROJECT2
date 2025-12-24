<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Only Admins may create users
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - admin only']);
    exit();
}

$firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastname  = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$email     = isset($_POST['email']) ? trim($_POST['email']) : '';
$password  = isset($_POST['password']) ? $_POST['password'] : '';
$role      = isset($_POST['role']) ? trim($_POST['role']) : 'Member';

$errors = [];
if ($firstname === '') $errors[] = 'First name is required';
if ($lastname === '')  $errors[] = 'Last name is required';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';

// Password rules: at least 8 chars, at least one uppercase, one lowercase, one digit
$pwPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\\d).{8,}$/';
if (!preg_match($pwPattern, $password)) {
    $errors[] = 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number';
}

if (!in_array($role, ['Admin','Member'])) $errors[] = 'Invalid role';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode('; ', $errors)]);
    exit();
}

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param('sssss', $firstname, $lastname, $email, $hash, $role);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'message' => 'User created successfully']);
} else {
    if (strpos($stmt->error, 'Duplicate entry') !== false) {
        http_response_code(400);
        echo json_encode(['error' => 'Email already exists']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }
}

$stmt->close();
?>
