<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    // Not authorized to view this fragment
    http_response_code(401);
    echo '<div class="page-load-error">Unauthorized</div>';
    exit;
}

$contactId = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<div class="page-header">
    <h2>Contact Details</h2>
    <div class="detail-actions">
        <button id="assign-me-btn" class="btn-primary">Assign to me</button>
        <button id="toggle-type-btn" class="btn-secondary">Switch Type</button>
        <button id="back-to-list" class="btn-secondary">Back</button>
    </div>
</div>

<div class="contact-detail">
    <div class="contact-meta">
        <h3 id="contact-name">Loading...</h3>
        <p id="contact-title" class="muted"></p>
        <p><strong>Email:</strong> <span id="contact-email"></span></p>
        <p><strong>Telephone:</strong> <span id="contact-telephone"></span></p>
        <p><strong>Company:</strong> <span id="contact-company"></span></p>
        <p><strong>Type:</strong> <span id="contact-type"></span></p>
        <p><strong>Assigned To:</strong> <span id="contact-assigned"></span></p>
        <p><strong>Created:</strong> <span id="contact-created"></span> &nbsp; <strong>Updated:</strong> <span id="contact-updated"></span></p>
    </div>

    <div class="notes-section">
        <h4>Notes</h4>
        <div id="notes-list" class="notes-list">Loading notes...</div>

        <form id="add-note-form">
            <div class="form-group">
                <label>Add a note</label>
                <textarea name="comment" placeholder="Enter note..." required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Add Note</button>
            </div>
        </form>
        <div id="add-note-message" class="form-message hidden"></div>
    </div>
</div>

<script>
    (function() {
        const contactId = <?php echo $contactId; ?>;
        let currentContactType = '';

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function loadContact() {
            $.ajax({
                url: 'api/get_contact.php',
                method: 'GET',
                data: { id: contactId },
                dataType: 'json',
                success: function(res) {
                    if (!res.success) {
                        $('#content-area').html('<div class="page-load-error">' + (res.error || 'Unable to load contact') + '</div>');
                        return;
                    }

                    const c = res.contact;
                    currentContactType = c.type;
                    const fullName = (c.title ? c.title + ' ' : '') + c.firstname + ' ' + c.lastname;
                    $('#contact-name').text(fullName);
                    $('#contact-title').text(c.title || '');
                    $('#contact-email').text(c.email || '-');
                    $('#contact-telephone').text(c.telephone || '-');
                    $('#contact-company').text(c.company || '-');
                    
                    // Display contact type with badge styling
                    const typeBadgeClass = c.type === 'Sales Lead' ? 'type-badge lead' : 'type-badge support';
                    $('#contact-type').html('<span class="' + typeBadgeClass + '">' + escapeHtml(c.type || '-') + '</span>');
                    
                    // Update switch type button text dynamically
                    updateSwitchTypeButton(c.type);
                    
                    $('#contact-assigned').text(c.owner_fullname || '-');
                    $('#contact-created').text(c.created_at || '-');
                    $('#contact-updated').text(c.updated_at || '-');
                },
                error: function(xhr) {
                    $('#content-area').html('<div class="page-load-error">Error loading contact details</div>');
                    console.error('get_contact error', xhr.status, xhr.responseText);
                }
            });
        }

        function loadNotes() {
            $.ajax({
                url: 'api/get_notes.php',
                method: 'GET',
                data: { contact_id: contactId },
                dataType: 'json',
                success: function(res) {
                    if (!res.success) {
                        $('#notes-list').html('<div class="empty">' + (res.error || 'Unable to load notes') + '</div>');
                        return;
                    }

                    if (!res.notes || res.notes.length === 0) {
                        $('#notes-list').html('<div class="empty">No notes yet.</div>');
                        return;
                    }

                    let html = '';
                    res.notes.forEach(function(n) {
                        html += '<div class="note-item">';
                        html += '<div class="note-meta"><strong>' + escapeHtml(n.author) + '</strong> <span class="muted">' + n.created_at + '</span></div>';
                        html += '<div class="note-body">' + escapeHtml(n.comment) + '</div>';
                        html += '</div>';
                    });
                    $('#notes-list').html(html);
                },
                error: function(xhr) {
                    $('#notes-list').html('<div class="error">Error loading notes</div>');
                    console.error('get_notes error', xhr.status, xhr.responseText);
                }
            });
        }

        // Assign to me
        $('#assign-me-btn').on('click', function() {
            const data = { action: 'assign', id: contactId };
            $.ajax({
                url: 'api/update_contact.php',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        loadContact();
                    } else {
                        alert('Error: ' + (res.error || 'Unable to assign'));
                    }
                },
                error: function(xhr) {
                    alert('Error assigning contact');
                    console.error('assign error', xhr.status, xhr.responseText);
                }
            });
        });

        // Back button
        $('#back-to-list').on('click', function() {
            try {
                window.history.back();
            } catch (e) {
                loadPage('contacts');
            }
        });

        // Switch type button
        $('#toggle-type-btn').on('click', function() {
            toggleContactType();
        });


        // Function to update switch type button text
        function updateSwitchTypeButton(currentType) {
            const newType = currentType === 'Sales Lead' ? 'Support' : 'Sales Lead';
            $('#toggle-type-btn').text('Switch to ' + newType);
        }

        // Add note
        $('#add-note-form').on('submit', function(e) {
            e.preventDefault();
            const comment = $(this).find('textarea[name="comment"]').val().trim();
            if (!comment) {
                $('#add-note-message').html('Note cannot be empty').removeClass('hidden').addClass('error');
                return;
            }

            $.ajax({
                url: 'api/add_note.php',
                method: 'POST',
                data: { contact_id: contactId, comment: comment },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#add-note-form')[0].reset();
                        $('#add-note-message').html('Note added').removeClass('hidden').removeClass('error').addClass('success');
                        setTimeout(function(){ $('#add-note-message').addClass('hidden'); }, 2500);
                        loadNotes();
                    } else {
                        $('#add-note-message').html(res.error || 'Unable to add note').removeClass('hidden').removeClass('success').addClass('error');
                    }
                },
                error: function(xhr) {
                    $('#add-note-message').html('Error adding note').removeClass('hidden').removeClass('success').addClass('error');
                    console.error('add_note error', xhr.status, xhr.responseText);
                }
            });
        });

        $(document).ready(function() {
            if (!contactId || contactId <= 0) {
                $('#content-area').html('<div class="page-load-error">Invalid contact id</div>');
                return;
            }
            loadContact();
            loadNotes();
        });
    })();

    // Global function to toggle contact type
    function toggleContactType() {
        const contactId = <?php echo intval($contactId); ?>;
        const currentTypeElement = document.getElementById('contact-type');
        if (!currentTypeElement) {
            alert('Error: Could not determine current contact type');
            return;
        }
        
        // Get the text content, handling both plain text and badge spans
        let currentType = currentTypeElement.textContent.trim();
        // If it's in a badge, extract just the type text
        const badgeSpan = currentTypeElement.querySelector('.type-badge');
        if (badgeSpan) {
            currentType = badgeSpan.textContent.trim();
        }
        
        const newType = currentType === 'Sales Lead' ? 'Support' : 'Sales Lead';
        
        $.ajax({
            url: 'api/update_contact.php',
            method: 'POST',
            data: { action: 'switch_type', id: contactId, type: newType },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // Reload contact data to update the display
                    // This will also update the button text via the loadContact callback
                    const typeEl = document.getElementById('contact-type');
                    if (typeEl) {
                        // Update the type display immediately
                        const typeBadgeClass = newType === 'Sales Lead' ? 'type-badge lead' : 'type-badge support';
                        typeEl.innerHTML = '<span class="' + typeBadgeClass + '">' + newType + '</span>';
                        
                        // Update button text
                        const nextType = newType === 'Sales Lead' ? 'Support' : 'Sales Lead';
                        $('#toggle-type-btn').text('Switch to ' + nextType);
                    }
                    
                    // Reload full contact to ensure consistency
                    loadContact();
                } else {
                    alert('Error: ' + (res.error || 'Unable to change type'));
                }
            },
            error: function(xhr) {
                alert('Error changing type');
                console.error('switch_type error', xhr.status, xhr.responseText);
            }
        });
    }

</script>


