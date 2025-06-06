jQuery(document).ready(function($) {
    // Add a custom method to handle the remote validation data
    $.validator.addMethod('math_captcha', function(value, element) {
        var $element = $(element);
        var token = $element.data('token');
        var $challenge = $('#' + $element.attr('id') + '-challenge');
        
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
                if (response.success === false) {
                    result = false;
                    errorMessage = response.data;
                    
                    // Get a new challenge on failed attempt
                    $.ajax({
                        url: mbMathCaptcha.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_new_challenge',
                            nonce: mbMathCaptcha.nonce
                        },
                        async: false,
                        success: function(newChallenge) {
                            if (newChallenge.success) {
                                // Update the token
                                $element.data('token', newChallenge.data.token);
                                
                                // Update the challenge text
                                $challenge.text(
                                    mbMathCaptcha.i18n.solve_math_problem.replace('%s', newChallenge.data.question)
                                );
                                
                                // Clear the input
                                $element.val('');
                            }
                        }
                    });
                } else {
                    result = response === 'true';
                }
            }
        });
        
        // Set the error message for this validation
        $.validator.messages.math_captcha = errorMessage;
        
        return result;
    });
});