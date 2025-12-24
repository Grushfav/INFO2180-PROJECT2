<?php
require_once 'config.php';

// Only allow logged-in Admins
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>New User - Dolphin CRM</title>
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
                <li><a href="home.php">🏠 Home</a></li>
                <li><a href="javascript:alert('Contact features coming soon!');">📋 New Contact</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="logout.php" class="logout">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h2>Add New User</h2>
            </div>

            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 30px; max-width: 700px;">
                <form id="new-user-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="firstname" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="lastname" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role">
                                <option value="Member">Member</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save</button>
                        <a href="users.php" class="btn-secondary" style="text-decoration:none; display:inline-block;">Back</a>
                    </div>
                </form>
                <div id="new-user-message" class="form-message" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/new_user.js"></script>
</body>
</html>
