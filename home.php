<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Home - Dolphin CRM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <div class="sidebar-nav-header">
                <h1>🐬 Dolphin CRM</h1>
            </div>
            <ul>
                <li><a href="home.php" class="active">🏠 Home</a></li>
                <li><a href="javascript:alert('Contact features coming soon!');">📋 New Contact</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="logout.php" class="logout">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-wrapper">
                <div class="page-header">
                    <h2>Welcome to Dolphin CRM</h2>
                </div>

                <div style="background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">Dashboard</h3>
                    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                        Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>! 
                        You are logged in as <span style="background: <?php echo $_SESSION['user_role'] === 'Admin' ? '#e8f4f8' : '#fff3e0'; ?>; padding: 4px 8px; border-radius: 4px; color: <?php echo $_SESSION['user_role'] === 'Admin' ? '#0277bd' : '#e65100'; ?>; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>.
                    </p>

                    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
                        <h4 style="color: #2c3e50; margin-top: 0;">Quick Stats</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                <strong>Account:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                            </li>
                            <li style="padding: 10px 0;">
                                <strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['user_role']); ?>
                            </li>
                        </ul>
                    </div>

                    <h4 style="color: #2c3e50; margin-top: 30px;">Getting Started</h4>
                    <ul style="color: #666;">
                        <li style="margin-bottom: 10px;">
                            <strong>Add Contacts:</strong> Click "New Contact" in the sidebar to add client or lead information
                        </li>
                        <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                        <li style="margin-bottom: 10px;">
                            <strong>Manage Users:</strong> Visit the <a href="users.php" style="color: #667eea; text-decoration: none;">Users page</a> to manage system accounts
                        </li>
                        <li>
                            <strong>Create Users:</strong> Click "New Contact" above to add new team members
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
