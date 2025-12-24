$(document).ready(function() {
    $('#new-user-form').submit(function(e) {
        e.preventDefault();

        const form = $(this);
        const data = form.serialize();

        // Basic client-side validation for password
        const password = form.find('input[name="password"]').val();
        const pwPattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
        if (!pwPattern.test(password)) {
            showMessage('Password must be at least 8 characters and include an uppercase letter and a number', true);
            return;
        }

        $('.btn-primary').prop('disabled', true).text('Saving...');

        $.ajax({
            url: 'api/add_user.php',
            method: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(resp) {
            if (resp.success) {
                showMessage(resp.message || 'User created', false);
                form[0].reset();
            } else {
                showMessage(resp.error || 'Error creating user', true);
            }
        }).fail(function(xhr) {
            let msg = 'Server error';
            try { msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : xhr.responseText; } catch(e){}
            showMessage(msg, true);
        }).always(function() {
            $('.btn-primary').prop('disabled', false).text('Save');
        });
    });

    function showMessage(message, isError) {
        const el = $('#new-user-message');
        el.removeClass('success error').addClass(isError ? 'error' : 'success');
        el.text(message).show();
        setTimeout(function() { el.fadeOut(); }, 4000);
    }
});
