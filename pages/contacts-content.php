<?php
require_once __DIR__ . '/../config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <button class="btn-primary" onclick="showAddContactForm()" style="float:right;">+ Add Contact</button>
</div>

<div style="clear:both;">
    <div class="filter-section">
        <span class="filter-label">Filter by:</span>
        <button class="filter-btn active" onclick="filterContacts('all')">All</button>
        <button class="filter-btn" onclick="filterContacts('sales')">Sales Leads</button>
        <button class="filter-btn" onclick="filterContacts('support')">Support</button>
        <button class="filter-btn" onclick="filterContacts('assigned')">Assigned to me</button>
    </div>
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
                    <option value="Client">Client</option>
                    <option value="Lead">Lead</option>
                </select>
            </div>
        </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Add Contact</button>
                <button type="button" class="btn-secondary" onclick="hideAddContactForm()">Cancel</button>
            </div>
        </form>
        <div id="contact-form-message" class="form-message" style="display:none;"></div>
    </div>

    <div id="add-contact-form-container" style="display:none; margin-bottom: 30px; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin-top: 20px;">
        <h3 style="margin-top: 0;">Add New Contact</h3>
        <form id="contact-form">
        <table class="contacts-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="text-align: left; padding: 12px; font-weight: bold;">Name</th>
                    <th style="text-align: left; padding: 12px; font-weight: bold;">Email</th>
                    <th style="text-align: left; padding: 12px; font-weight: bold;">Company</th>
                    <th style="text-align: left; padding: 12px; font-weight: bold;">Type</th>
                    <th style="text-align: left; padding: 12px; font-weight: bold;"></th>
                </tr>
            </thead>
            <tbody id="contacts-list">
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #999;">Loading contacts...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
let currentFilter = 'all';

function showAddContactForm() {
    $('#add-contact-form-container').slideDown();
    $('html, body').animate({scrollTop: 0}, 'slow');
}

function hideAddContactForm() {
    $('#add-contact-form-container').slideUp();
    $('#contact-form')[0].reset();
    $('#contact-form-message').hide();
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
                        if (currentFilter === 'sales') return contact.type === 'Lead';
                        if (currentFilter === 'support') return contact.type === 'Support';
                        return true;
                    });
                }
                
                if (filteredContacts.length === 0) {
                    $('#contacts-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #999;">No contacts found.</td></tr>');
                    return;
                }
                
                filteredContacts.forEach(function(contact) {
                    const typeColor = contact.type === 'Lead' ? '#fbbc04' : (contact.type === 'Support' ? '#5f6368' : '#1f71ed');
                    const typeTextColor = contact.type === 'Lead' ? '#202124' : 'white';
                    const fullName = (contact.title ? contact.title + ' ' : '') + contact.firstname + ' ' + contact.lastname;
                    
                    html += '<tr style="border-bottom: 1px solid #eee;">';
                    html += '<td style="padding: 16px;">' + escapeHtml(fullName) + '</td>';
                    html += '<td style="padding: 16px;">' + escapeHtml(contact.email) + '</td>';
                    html += '<td style="padding: 16px;">' + escapeHtml(contact.company || '-') + '</td>';
                    html += '<td style="padding: 16px;"><span style="background: ' + typeColor + '; color: ' + typeTextColor + '; padding: 6px 12px; border-radius: 3px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">' + escapeHtml(contact.type) + '</span></td>';
                    html += '<td style="padding: 16px; text-align: right;"><button class="btn-view" onclick="viewContact(' + contact.id + ')" style="background-color: transparent; color: #1f71ed; border: 1px solid #dadce0; padding: 5px 12px; font-size: 12px; border-radius: 4px; cursor: pointer;">View</button> <button class="btn-small" onclick="deleteContact(' + contact.id + ')" style="background: #d32f2f; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-left: 8px;">Delete</button></td>';
                    html += '</tr>';
                });
                $('#contacts-list').html(html);
            } else {
                $('#contacts-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #999;">No contacts yet. Add one to get started!</td></tr>');
            }
        },
        error: function() {
            $('#contacts-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #d32f2f;">Error loading contacts</td></tr>');
        }
    });
}

function viewContact(contactId) {
    alert('Contact details view coming soon!');
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
    loadContacts();

    $('#contact-form').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: 'api/add_contact.php',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Clear form
                $('#contact-form')[0].reset();

                // Show success message
                const messageDiv = $('#contact-form-message');
                messageDiv.html('Contact added successfully!').removeClass('error').addClass('success').show();

                // Hide message after 3 seconds
                setTimeout(function() {
                    messageDiv.hide();
                }, 3000);

                // Reload contacts list
                loadContacts();
                hideAddContactForm();
            },
            error: function(xhr) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    const messageDiv = $('#contact-form-message');
                    messageDiv.html(response.error).removeClass('success').addClass('error').show();
                } catch(e) {
                    alert('Error adding contact');
                }
            }
        });
    });
});
</script>
