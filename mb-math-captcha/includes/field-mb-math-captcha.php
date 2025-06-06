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
                'solve_math_problem' => __('Please solve this math problem: %s', 'mb-math-captcha'),
                //'rate_limited' => __('Too many failed attempts. Please try again in 5 minutes.', 'mb-math-captcha')
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

        // Generate challenge and token
        $challenge = self::generate_challenge();
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

		return $output;
    }

    /**
     * Generate a random math challenge
     *
     * @return array
     */
    public static function generate_challenge() {
        // Generate a single digit answer (0-9)
        $answer = rand(0, 9);
        
        // Generate first number (0-9)
        $num1 = rand(0, $answer);
        
        // Calculate second number to ensure the sum equals our desired answer
        $num2 = $answer - $num1;
        
        $question = sprintf('%d + %d', $num1, $num2);

        return [
            'question' => $question,
            'answer'   => $answer,
        ];
    }
}