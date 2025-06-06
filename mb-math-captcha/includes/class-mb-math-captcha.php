<?php
/**
 * Class MathCaptcha
 * @package MathCaptcha
 * @category WordPress Plugins
 */

class MathCaptcha {

	/**
	 * PHP5 constructor
	 */
	public function __construct() {
        // register ajax handlers
        add_action( 'wp_ajax_validate_math_captcha', [$this,'validate_math_captcha']);
        add_action( 'wp_ajax_nopriv_validate_math_captcha', [$this,'validate_math_captcha']);
        add_action( 'wp_ajax_get_new_challenge', [$this,'get_new_challenge']);
        add_action( 'wp_ajax_nopriv_get_new_challenge', [$this,'get_new_challenge']);
        
        // add a demo metabox to test
        add_filter( 'rwmb_meta_boxes', [$this, 'add_demo_meta_box'], 99, 1 );

	}

    // Get a new math challenge
    public function get_new_challenge() {
        check_ajax_referer( 'mb_math_captcha_nonce', 'nonce' );
        
        $token = wp_generate_password( 32, false );
        $challenge = $this->generate_challenge();
        
        // Store the answer in a transient
        set_transient( 'mb_math_captcha_' . $token, $challenge['answer'], 5 * MINUTE_IN_SECONDS );
        
        wp_send_json_success([
            'token' => $token,
            'question' => $challenge['question']
        ]);
        die();
    }

    // Generate a random math challenge
    public function generate_challenge() {
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
    // Validate the math captcha
    public function validate_math_captcha() {
        check_ajax_referer( 'mb_math_captcha_nonce', 'nonce' );
        
        $token = sanitize_text_field( $_POST['token'] );
        $value = sanitize_text_field( $_POST['value'] );
        
        // Get the stored answer from transient
        $stored_answer = get_transient( 'mb_math_captcha_' . $token );

        if ( $stored_answer !== false && (int) $value === (int) $stored_answer ) {
            wp_send_json_success('true');
        } else {
            wp_send_json_error( __( 'Incorrect answer. Please try again. Blerp bloop.', 'mb-math-captcha' ) );
        }
        die();
    }

    // Add a demo metabox to test
    public function add_demo_meta_box( $meta_boxes ) {        
        $meta_boxes[] = [
            'title'      => __( 'A simple math challenge', 'mb-math-captcha' ),
            'id'         => 'math_captcha_box',
            'context'    => 'normal',
            'priority'   => 'high',
            'fields'     => [
                [
                    'id'   => 'math_challenge',
                    'name' => __( 'Are you human?', 'mb-math-captcha' ),
                    'type' => 'math_captcha', // math_captcha
                    'save_field' => false,
                    'required' => true,
                ]
            ],
            'validation' => [
                'rules' => [
                    'math_challenge' => [
                        'math_captcha' => true
                    ],
                ],
            ],
        ];
        return $meta_boxes;
    }

} // end class MathCaptcha


/**
 * Create instance
 */
$MathCaptcha = new MathCaptcha;
