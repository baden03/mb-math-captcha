jQuery(document).ready(function($) {
    // Add a custom method to handle the remote validation data
    $.validator.addMethod('math_captcha', function(value, element) {
        var $element = $(element);
        var token = $element.data('token');
        
        // Make the AJAX call
        var result = false;
        var errorMessage;
        
        $.ajax({
            url: mbMathCaptcha.ajaxurl,
            type: 'POST',
            data: {
                action: 'validate_math_captcha',
                nonce: mbMathCaptcha.nonce,
                token: token,
                value: value
            },
            async: false,
            success: function(response) {
                result = response === 'true';
                if (!result && response.data) {
                    errorMessage = response.data;
                }
            }
        });
        
        // Set the error message for this validation
        $.validator.messages.math_captcha = errorMessage;
        
        return result;
    });
});