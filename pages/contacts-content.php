<?php
require_once __DIR__ . '/../config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}
?>

<div class="page-header">
    <h2>New Contacts</h2>
    <button id="toggle-view-btn" class="btn-primary" onclick="toggleContactsView()">View Contacts List</button>
</div>


<div id="add-contact-form-container" class="add-contact-form-container">
    <h3>Add New Contact</h3>
    <form id="contact-form">
        <div class="form-row">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="Mr., Ms., Dr., etc.">
            </div>
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
                <label>Telephone</label>
                <input type="tel" name="telephone" placeholder="Phone number">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Company</label>
                <input type="text" name="company" placeholder="Company name">
            </div>
            <div class="form-group">
                <label>Contact Type *</label>
                <select name="type" required>
                    <option value="">-- Select Type --</option>
                    <option value="Sales Lead">Sales Lead</option>
                    <option value="Support">Support</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save</button>
            <button type="button" class="btn-secondary" onclick="hideAddContactForm()">Cancel</button>
        </div>
    </form>
    <div id="contact-form-message" class="form-message hidden"></div>
</div>

<div class="contacts-container hidden">
    <table class="contacts-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Type</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="contacts-list">
            <tr>
                <td colspan="5" class="text-center loading">Loading contacts...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
window.currentFilter = window.currentFilter || 'all';

function showAddContactForm() {
    $('#add-contact-form-container').removeClass('hidden').slideDown();
    $('html, body').animate({scrollTop: 0}, 'slow');
}

function hideAddContactForm() {
    $('#add-contact-form-container').slideUp(function() {
        $(this).addClass('hidden');
    });
    $('#contacts-container').slideDown();
    $('#toggle-view-btn').text('View Contacts List');
    $('#contact-form')[0].reset();
    $('#contact-form-message').addClass('hidden');
}

function toggleContactsView() {
    const form = $('#add-contact-form-container');
    const list = $('.contacts-container');
    
    if (form.hasClass('hidden')) {
        // Show form, hide list
        form.removeClass('hidden').slideDown();
        list.slideUp(function() {
            $(this).addClass('hidden');
        });
        $('#toggle-view-btn').text('View Contacts List');
    } else {
        // Hide form, show list
        form.slideUp(function() {
            $(this).addClass('hidden');
        });
        list.removeClass('hidden').slideDown();
        loadContacts();
        $('#toggle-view-btn').text('Add New Contact');
    }
}

function filterContacts(filterType) {
    currentFilter = filterType;
    
    // Update active button
    $('.filter-btn').removeClass('active');
    $('[onclick="filterContacts(\'' + filterType + '\')"]').addClass('active');
    
    loadContacts();
}

function loadContacts() {
    $.ajax({
        url: 'api/get_contacts.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success && data.contacts.length > 0) {
                let html = '';
                let filteredContacts = data.contacts;
                
                // Apply filter
                if (currentFilter !== 'all') {
                    filteredContacts = data.contacts.filter(function(contact) {
                        if (currentFilter === 'sales') return contact.type === 'Sales Lead';
                        if (currentFilter === 'support') return contact.type === 'Support';
                        if (currentFilter === 'assigned') return contact.user_id == data.current_user_id;
                        return true;
                    });
                }
                
                if (filteredContacts.length === 0) {
                    $('#contacts-list').html('<tr><td colspan="5" class="text-center empty">No contacts found.</td></tr>');
                    return;
                }
                
                filteredContacts.forEach(function(contact) {
                    const fullName = (contact.title ? contact.title + ' ' : '') + contact.firstname + ' ' + contact.lastname;
                    const typeBadgeClass = contact.type === 'Sales Lead' ? 'type-badge lead' : 'type-badge support';
                    
                    html += '<tr>';
                    html += '<td>' + escapeHtml(fullName) + '</td>';
                    html += '<td>' + escapeHtml(contact.email) + '</td>';
                    html += '<td>' + escapeHtml(contact.company || '-') + '</td>';
                    html += '<td><span class="' + typeBadgeClass + '">' + escapeHtml(contact.type) + '</span></td>';
                    html += '<td class="action-cell">';
                    html += '<button class="btn-view" onclick="viewContact(' + contact.id + ')">View</button> ';
                    html += '<button class="btn-delete" onclick="deleteContact(' + contact.id + ')">Delete</button>';
                    html += '</td>';
                    html += '</tr>';
                });
                $('#contacts-list').html(html);
            } else {
                $('#contacts-list').html('<tr><td colspan="5" class="text-center empty">No contacts yet. Add one to get started!</td></tr>');
            }
        },
        error: function() {
            $('#contacts-list').html('<tr><td colspan="5" class="text-center error">Error loading contacts</td></tr>');
        }
    });
}

function viewContact(contactId) {
    // Load the contact detail fragment into the main content area
    $.ajax({
        url: 'pages/contact-detail.php',
        method: 'GET',
        data: { id: contactId },
        dataType: 'html',
        success: function(html) {
            $('#content-area').html(html);
            // Push to history so back button works
            try {
                window.history.pushState({page: 'contact', id: contactId}, 'Contact', '#contact-' + contactId);
            } catch (e) {
                console.warn('History pushState failed:', e);
            }
        },
        error: function(xhr) {
            alert('Error loading contact details');
            console.error('Load contact detail error:', xhr.status, xhr.responseText);
        }
    });
}

function deleteContact(contactId) {
    if (confirm('Are you sure you want to delete this contact?')) {
        $.ajax({
            url: 'api/delete_contact.php',
            method: 'POST',
            data: {id: contactId},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadContacts();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error deleting contact');
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
    // Load contacts in the background (they're hidden initially)
    loadContacts();

    $('#contact-form').submit(function(e) {
        e.preventDefault();

        // Validate required fields
        const firstname = $('input[name="firstname"]').val().trim();
        const lastname = $('input[name="lastname"]').val().trim();
        const email = $('input[name="email"]').val().trim();
        const type = $('select[name="type"]').val();

        if (!firstname || !lastname || !email || !type) {
            $('#contact-form-message').html('First name, last name, email, and type are required').removeClass('success').addClass('error').removeClass('hidden');
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $('#contact-form-message').html('Invalid email format').removeClass('success').addClass('error').removeClass('hidden');
            return;
        }

        const formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: 'api/add_contact.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                // Clear form
                $('#contact-form')[0].reset();

                // Show success message
                const messageDiv = $('#contact-form-message');
                messageDiv.html('Contact added successfully!').removeClass('error').addClass('success').removeClass('hidden');

                // Hide message after 3 seconds
                setTimeout(function() {
                    messageDiv.addClass('hidden');
                }, 3000);

                // Reload contacts list
                loadContacts();
                // Show form, hide list
                $('#add-contact-form-container').removeClass('hidden');
                $('.contacts-container').addClass('hidden');
                $('#toggle-view-btn').text('View Contacts List');
            },
            error: function(xhr) {
                let errorMessage = 'Error adding contact';
                console.log('Contact API Error - Status:', xhr.status, 'Response:', xhr.responseText);
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch(e) {
                    console.error('Failed to parse error response:', e);
                    if (xhr.status) {
                        errorMessage = 'Error ' + xhr.status + ': ' + xhr.statusText;
                    }
                }
                const messageDiv = $('#contact-form-message');
                messageDiv.html(errorMessage).removeClass('success').addClass('error').removeClass('hidden');
                console.error('Add contact error:', errorMessage);
            }
        });
    });
});
</script>
