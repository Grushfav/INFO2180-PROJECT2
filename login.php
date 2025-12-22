<?php
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email and password are required.';
    } else {
        // Query to get user
        $stmt = $conn->prepare("SELECT id, firstname, lastname, email, password, role FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (for now, direct comparison; in production, use password_hash)
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = 'Invalid email or password.';
            }
        } else {
            $_SESSION['error'] = 'Invalid email or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dolphin CRM - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🐬 Dolphin CRM</h1>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <h2>Login</h2>
            <form method="POST" action="login.php" id="login-form">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="login-footer">
                <p>Copyright © 2022 Dolphin CRM</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
