<?php
require_once __DIR__ . '/../config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}
?>

<div class="page-header">
    <h2>Welcome to Dolphin CRM</h2>
</div>

<div style="background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
    <h3 style="color: #2c3e50; margin-bottom: 15px;">Dashboard</h3>
    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
        Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>! 
        You are logged in as <span style="background: <?php echo $_SESSION['user_role'] === 'Admin' ? '#e8f4f8' : '#fff3e0'; ?>; padding: 4px 8px; border-radius: 4px; color: <?php echo $_SESSION['user_role'] === 'Admin' ? '#0277bd' : '#e65100'; ?>; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>.
    </p>

    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin-bottom: 30px;">
        <h4 style="color: #2c3e50; margin-top: 0;">Account Information</h4>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?>
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
            <strong>Manage Users:</strong> Click "Users" in the sidebar to manage system accounts
        </li>
        <li>
            <strong>Create Users:</strong> Go to the Users page and click the "Add User" button
        </li>
        <?php endif; ?>
    </ul>
</div>
