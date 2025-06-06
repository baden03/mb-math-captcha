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

        // add a demo metabox to test
        add_filter( 'rwmb_meta_boxes', [$this, 'add_demo_meta_box'], 99, 1 );

	}


    // Validate the math captcha
    public function validate_math_captcha() {
        check_ajax_referer( 'mb_math_captcha_nonce', 'nonce' );
        
        $token = sanitize_text_field( $_POST['token'] );
        $value = sanitize_text_field( $_POST['value'] );
        
        // Get the stored answer from transient
        $stored_answer = get_transient( 'mb_math_captcha_' . $token );

        if ( $stored_answer !== false && (int) $value === (int) $stored_answer ) {
            echo 'true';
        } else {
            wp_send_json_success( __( 'Incorrect answer. Please try again. Blerp bloop.', 'mb-math-captcha' ) );
        }
        die();
    }

    // Add a demo metabox to test
    public function add_demo_meta_box( $meta_boxes ) {        
        $meta_boxes[] = [
            'title'      => 'Are you human?',
            'id'         => 'math_captcha_box',
            'context'    => 'normal',
            'priority'   => 'high',
            'fields'     => [
                [
                    'id'   => 'math_challenge',
                    'name' => 'Are you human?',
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
