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
    <button class="btn-primary" onclick="showAddUserForm()">+ Add User</button>
</div>

<div class="add-user-form-container hidden" id="add-user-form-container">
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
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
        </div>

        <div class="password-hint">Minimum 8 characters with at least 1 uppercase letter, 1 lowercase letter, and 1 digit</div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Add User</button>
            <button type="button" class="btn-secondary" onclick="hideAddUserForm()">Cancel</button>
        </div>
    </form>
    <div id="add-user-message" class="form-message hidden"></div>
</div>

<div class="users-container">
    <table class="users-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="users-list">
            <tr>
                <td colspan="5" class="text-center loading">Loading users...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
function showAddUserForm() {
    // ensure any running animations are stopped, then show
    $('#add-user-form-container').stop(true, true).removeClass('hidden').slideDown();
    $('html, body').animate({scrollTop: 0}, 'slow');
}

function hideAddUserForm() {
    // stop any animation and hide, then add hidden class
    $('#add-user-form-container').stop(true, true).slideUp(function() {
        $(this).addClass('hidden');
    });
    $('#add-user-form')[0].reset();
    $('#add-user-message').addClass('hidden');
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
                    const roleClass = user.role === 'Admin' ? 'role-badge admin' : 'role-badge member';
                    const createdDate = new Date(user.created_at).toLocaleDateString();
                    
                    html += '<tr>';
                    html += '<td>' + escapeHtml(user.fullname) + '</td>';
                    html += '<td>' + escapeHtml(user.email) + '</td>';
                    html += '<td><span class="' + roleClass + '">' + user.role + '</span></td>';
                    html += '<td>' + createdDate + '</td>';
                    html += '<td class="action-cell"><button class="btn-delete" onclick="deleteUser(' + user.id + ')">Delete</button></td>';
                    html += '</tr>';
                });
                $('#users-list').html(html);
            } else {
                $('#users-list').html('<tr><td colspan="5" class="text-center empty">No users found</td></tr>');
            }
        },
        error: function() {
            $('#users-list').html('<tr><td colspan="5" class="text-center error">Error loading users</td></tr>');
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
    // enforce hidden state on load to avoid race conditions
    $('#add-user-form-container').addClass('hidden').hide();
    loadUsers();

    $('#add-user-form').submit(function(e) {
        e.preventDefault();

        const password = $('input[name="password"]').val();
        const confirmPassword = $('input[name="confirm_password"]').val();
        const firstname = $('input[name="firstname"]').val();
        const lastname = $('input[name="lastname"]').val();
        const email = $('input[name="email"]').val();
        const role = $('select[name="role"]').val();

        // Validate required fields
        if (!firstname || !lastname || !email || !role) {
            $('#add-user-message').html('All fields are required').removeClass('success').addClass('error').removeClass('hidden');
            return;
        }

        // Validate passwords match
        if (password !== confirmPassword) {
            $('#add-user-message').html('Passwords do not match').removeClass('success').addClass('error').removeClass('hidden');
            return;
        }

        // Validate password strength
        const pwPattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
        if (!pwPattern.test(password)) {
            $('#add-user-message').html('Password must be at least 8 characters and include uppercase, lowercase, and a number').removeClass('success').addClass('error').removeClass('hidden');
            return;
        }

        const formData = {
            firstname: firstname,
            lastname: lastname,
            email: email,
            role: role,
            password: password
        };

        $.ajax({
            type: 'POST',
            url: 'api/add_user.php',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                $('#add-user-form')[0].reset();
                $('#add-user-message').html('User added successfully!').removeClass('error').addClass('success').removeClass('hidden');
                loadUsers();
                setTimeout(function() {
                    hideAddUserForm();
                }, 2000);
            },
            error: function(xhr) {
                let errorMessage = 'Error adding user';
                console.log('XHR Status:', xhr.status);
                console.log('XHR Response Text:', xhr.responseText);
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch(e) {
                    // If JSON parse fails, try to get status text
                    if (xhr.status && xhr.statusText) {
                        errorMessage = xhr.status + ': ' + xhr.statusText;
                    }
                }
                $('#add-user-message').html(errorMessage).removeClass('success').addClass('error').removeClass('hidden');
                console.error('Add user error:', errorMessage, xhr);
            }
        });
    });
});
</script>
