jQuery(document).ready(function($) {
    // Add a custom method to handle the remote validation data
    $.validator.addMethod('math_captcha', function(value, element) {
        var $element = $(element);
        var token = $element.data('token');
        var attempts = $element.data('attempts');
        
        // Check if the user has exceeded the maximum number of attempts
        if (attempts >= 3) {
            $.validator.messages.math_captcha = mbMathCaptcha.i18n.max_attempts_reached;
            return false;
        }

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
                if(response.success === true){
                    result = true;
                    $element.data('attempts', 0);
                }
                else{
                    result = false;
                    errorMessage = response.data;
                    
                    var newAttempts = attempts + 1;
                    $element.data('attempts', newAttempts);

                    var $challenge = $('#' + $element.attr('id') + '-challenge');

                    // Get a new challenge on failed attempt, only if attempts are left
                    if (newAttempts < 3) {
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
                    }
                }
            },
        });
        
        // Set the error message for this validation
        $.validator.messages.math_captcha = errorMessage;
        
        return result;
    });
});