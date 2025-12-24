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
    <title>Users - Dolphin CRM</title>
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
                <li><a href="users.php" class="active">👥 Users</a></li>
                <li><a href="logout.php" class="logout">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h2>Users</h2>
                <a href="new_user.php" class="btn-primary" style="text-decoration:none; display:inline-block;">+ Add User</a>
            </div>
            
            <div id="users-table-container" style="overflow-x:auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 20px;">
                <p style="text-align:center; color:#999;">Loading users...</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            loadUsers();
        });

        function loadUsers() {
            $.ajax({
                url: 'api/get_users.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayUsers(response.data);
                    } else {
                        showError(response.error || 'Failed to load users');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error loading users';
                    showError(msg);
                }
            });
        }

        function displayUsers(users) {
            if (users.length === 0) {
                $('#users-table-container').html('<p style="text-align:center; color:#999; padding:40px;">No users found.</p>');
                return;
            }

            let html = '<table style="width:100%; border-collapse:collapse;">';
            html += '<thead><tr style="background:#f5f5f5; border-bottom:2px solid #ddd;">';
            html += '<th style="padding:12px; text-align:left; font-weight:bold;">Name</th>';
            html += '<th style="padding:12px; text-align:left; font-weight:bold;">Email</th>';
            html += '<th style="padding:12px; text-align:left; font-weight:bold;">Role</th>';
            html += '<th style="padding:12px; text-align:left; font-weight:bold;">Created</th>';
            html += '</tr></thead><tbody>';

            users.forEach(function(user) {
                const createdDate = new Date(user.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += '<tr style="border-bottom:1px solid #eee;">';
                html += '<td style="padding:12px;">' + escapeHtml(user.fullname) + '</td>';
                html += '<td style="padding:12px;">' + escapeHtml(user.email) + '</td>';
                html += '<td style="padding:12px;"><span style="background:' + (user.role === 'Admin' ? '#e8f4f8' : '#fff3e0') + '; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold; color:' + (user.role === 'Admin' ? '#0277bd' : '#e65100') + ';">' + escapeHtml(user.role) + '</span></td>';
                html += '<td style="padding:12px; font-size:12px; color:#666;">' + createdDate + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            $('#users-table-container').html(html);
        }

        function showError(msg) {
            $('#users-table-container').html('<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:4px; border:1px solid #f5c6cb;">' + escapeHtml(msg) + '</div>');
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    </script>
</body>
</html>
