<?php
require_once __DIR__ . '/../config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

// Check if user is admin
if ($_SESSION['user_role'] !== 'Admin') {
    die('Only administrators can view this page');
}
?>

<div class="page-header">
    <h2>Manage Users</h2>
    <button class="btn-primary" onclick="showAddUserForm()" style="float:right;">+ Add User</button>
</div>

<div style="clear:both; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin-top: 20px;">
    
    <div id="add-user-form-container" style="display:none; margin-bottom: 30px; padding: 20px; background: #f5f5f5; border-radius: 8px;">
        <h3>Add New User</h3>
        <form id="add-user-form">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" placeholder="Last Name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" placeholder="Email address" required>
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="Admin">Admin</option>
                        <option value="Member">Member</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Min 8 chars (upper, lower, digit)" required>
                    <small style="color: #999; display: block; margin-top: 5px;">Minimum 8 characters with at least 1 uppercase letter, 1 lowercase letter, and 1 digit</small>
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Add User</button>
                <button type="button" class="btn-secondary" onclick="hideAddUserForm()">Cancel</button>
            </div>
        </form>
        <div id="add-user-message" class="form-message" style="display:none;"></div>
    </div>

    <table class="users-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #ddd;">
                <th style="text-align: left; padding: 12px; font-weight: bold;">Name</th>
                <th style="text-align: left; padding: 12px; font-weight: bold;">Email</th>
                <th style="text-align: left; padding: 12px; font-weight: bold;">Role</th>
                <th style="text-align: left; padding: 12px; font-weight: bold;">Created</th>
                <th style="text-align: left; padding: 12px; font-weight: bold;">Actions</th>
            </tr>
        </thead>
        <tbody id="users-list">
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: #999;">Loading users...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
function showAddUserForm() {
    $('#add-user-form-container').slideDown();
    $('html, body').animate({scrollTop: 0}, 'slow');
}

function hideAddUserForm() {
    $('#add-user-form-container').slideUp();
    $('#add-user-form')[0].reset();
    $('#add-user-message').hide();
}

function loadUsers() {
    $.ajax({
        url: 'api/get_users.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success && data.users.length > 0) {
                let html = '';
                data.users.forEach(function(user) {
                    const roleColor = user.role === 'Admin' ? '#0277bd' : '#e65100';
                    const roleLabel = user.role === 'Admin' ? 'Admin' : 'Member';
                    const createdDate = new Date(user.created_at).toLocaleDateString();
                    
                    html += '<tr style="border-bottom: 1px solid #eee;">';
                    html += '<td style="padding: 12px;">' + escapeHtml(user.fullname) + '</td>';
                    html += '<td style="padding: 12px;">' + escapeHtml(user.email) + '</td>';
                    html += '<td style="padding: 12px;"><span style="background: ' + roleColor + '; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">' + roleLabel + '</span></td>';
                    html += '<td style="padding: 12px;">' + createdDate + '</td>';
                    html += '<td style="padding: 12px;"><button class="btn-small" onclick="deleteUser(' + user.id + ')" style="background: #d32f2f; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;">Delete</button></td>';
                    html += '</tr>';
                });
                $('#users-list').html(html);
            } else {
                $('#users-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #999;">No users found</td></tr>');
            }
        },
        error: function() {
            $('#users-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #d32f2f;">Error loading users</td></tr>');
        }
    });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: 'api/delete_user.php',
            method: 'POST',
            data: {id: userId},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadUsers();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error deleting user');
            }
        });
    }
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

$(document).ready(function() {
    loadUsers();

    $('#add-user-form').submit(function(e) {
        e.preventDefault();

        // Validate passwords match
        if ($('input[name="password"]').val() !== $('input[name="confirm_password"]').val()) {
            $('#add-user-message').html('Passwords do not match').removeClass('success').addClass('error').show();
            return;
        }

        const formData = {
            firstname: $('input[name="firstname"]').val(),
            lastname: $('input[name="lastname"]').val(),
            email: $('input[name="email"]').val(),
            role: $('select[name="role"]').val(),
            password: $('input[name="password"]').val()
        };

        $.ajax({
            type: 'POST',
            url: 'api/add_user.php',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                $('#add-user-form')[0].reset();
                $('#add-user-message').html('User added successfully!').removeClass('error').addClass('success').show();
                loadUsers();
                setTimeout(function() {
                    hideAddUserForm();
                }, 2000);
            },
            error: function(xhr) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    $('#add-user-message').html(response.error).removeClass('success').addClass('error').show();
                } catch(e) {
                    $('#add-user-message').html('Error adding user').removeClass('success').addClass('error').show();
                }
            }
        });
    });
});
</script>
