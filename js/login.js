$(document).ready(function() {
    // Form submission handling
    $('#login-form').submit(function(e) {
        // Optional: Add client-side validation
        const email = $('#email').val().trim();
        const password = $('#password').val();

        // Basic validation
        if (!email) {
            e.preventDefault();
            showError('Please enter your email address');
            return false;
        }

        if (!password) {
            e.preventDefault();
            showError('Please enter your password');
            return false;
        }

        // Disable button during submission
        $('.btn-login').prop('disabled', true).text('Logging in...');
    });

    // Focus effects
    $('#email, #password').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });

    // Clear error message on input
    $('#email, #password').on('input', function() {
        $('.error-message').fadeOut(300);
    });
});

function showError(message) {
    const errorDiv = '<div class="error-message">' + message + '</div>';
    $('.login-header').after(errorDiv);
    
    setTimeout(function() {
        $('.error-message').fadeOut(300);
    }, 3000);
}
