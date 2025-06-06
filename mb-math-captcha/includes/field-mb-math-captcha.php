<?php
class RWMB_Math_Captcha_Field extends RWMB_Input_Field {
    
    /**
     * Enqueue scripts and styles
     */
    private static function enqueue_scripts() {
        // Enqueue the script
        wp_enqueue_script(
            'mb-math-captcha',
            MB_MATH_CAPTCHA_URL . 'js/captcha.js',
            array('jquery'),
            MB_MATH_CAPTCHA_VERSION,
            true
        );

        // Localize the script with necessary data
        wp_localize_script('mb-math-captcha', 'mbMathCaptcha', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mb_math_captcha_nonce'),
            'i18n' => array(
                'solve_math_problem' => __('Please solve this math problem: %s', 'mb-math-captcha')
            )
        ));
    }

    /**
     * Enqueue scripts and styles for admin
     */
    public static function admin_enqueue_scripts() {
        self::enqueue_scripts();
    }

    /**
     * Enqueue scripts and styles for frontend
     */
    public static function frontend_enqueue_scripts() {
        self::enqueue_scripts();
    }

    /**
     * Get field HTML
     *
     * @param mixed $meta  Meta value
     * @param array $field Field parameters
     * @return string
     */
    public static function html( $meta, $field ) {
        $field['type'] = 'text';
        $output = '';

        try {
            // Get the MathCaptcha instance
            $math_captcha = MathCaptcha::get_instance();
            
            // Generate challenge and token
            $challenge = $math_captcha->generate_challenge();
            $token = wp_generate_password( 32, false );
            
            // Store the answer in a transient
            set_transient( 'mb_math_captcha_' . $token, $challenge['answer'], 5 * MINUTE_IN_SECONDS );

            if ( $field['prepend'] || $field['append'] ) {
                $output = '<div class="rwmb-input-group">';
            }

            if ( $field['prepend'] ) {
                $output .= '<span class="rwmb-input-group-text">' . $field['prepend'] . '</span>';
            }

            // Add the challenge description
            $output .= sprintf(
                '<p id="' . $field['id'] . '-challenge" class="description">%s</p>',
                sprintf( __( 'Please solve this math problem: %s', 'mb-math-captcha' ), $challenge['question'] )
            );

            $field['attributes']['data-token'] = $token;

            $attributes = static::get_attributes( $field, $meta );
            $output .= sprintf( '<input %s>%s', self::render_attributes( $attributes ), self::datalist( $field ) );

            if ( $field['append'] ) {
                $output .= '<span class="rwmb-input-group-text">' . $field['append'] . '</span>';
            }

            if ( $field['prepend'] || $field['append'] ) {
                $output .= '</div>';
            }
        } catch (Exception $e) {
            // Log the error
            error_log('Math Captcha Error: ' . $e->getMessage());
            
            // Return a simple error message
            $output = '<p class="description">' . __('Error generating math challenge. Please try again.', 'mb-math-captcha') . '</p>';
        }

        return $output;
    }

    /**
     * Generate a random math challenge
     *
     * @return array
     */
    private static function generate_challenge() {
        // Generate numbers that will result in a single digit answer (0-9)
        $answer = rand(0, 9);
        $num1 = rand(0, 9);
        
        // Calculate num2 to ensure the answer is what we want
        $num2 = $answer - $num1;
        
        // If num2 would be negative, swap the numbers
        if ($num2 < 0) {
            $temp = $num1;
            $num1 = abs($num2);
            $num2 = $temp;
        }

        $question = sprintf('%d + %d', $num1, $num2);

        $challenge = array(
            'question' => $question,
            'answer'   => $answer,
        );

        return $challenge;
    }
}