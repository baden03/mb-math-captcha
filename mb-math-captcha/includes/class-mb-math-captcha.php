<?php
/**
 * Class MathCaptcha
 * @package MathCaptcha
 * @category WordPress Plugins
 */

class MathCaptcha {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Get instance of this class.
	 *
	 * @return object
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

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

        // Initialize session if not started
        if (!session_id()) {
            session_start();
        }
	}

    // Get client IP address
    private function get_client_ip() {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    // Validate the math captcha
    public function validate_math_captcha() {
        check_ajax_referer( 'mb_math_captcha_nonce', 'nonce' );
        
        $token = sanitize_text_field( $_POST['token'] );
        $value = sanitize_text_field( $_POST['value'] );
        $ip = $this->get_client_ip();
        
        // Get failed attempts data
        $failed_attempts_key = 'mb_math_captcha_failed_' . md5($ip);
        $failed_attempts_data = get_transient($failed_attempts_key);
        
        if ($failed_attempts_data === false) {
            $failed_attempts_data = [
                'count' => 0,
                'last_attempt' => 0
            ];
        }
        
        // Check if user is rate limited
        if ($failed_attempts_data['count'] >= 3) {
            $time_since_last_attempt = time() - $failed_attempts_data['last_attempt'];
            if ($time_since_last_attempt < 5 * MINUTE_IN_SECONDS) {
                wp_send_json_error(__('Too many failed attempts. Please try again in 5 minutes.', 'mb-math-captcha'));
                die();
            } else {
                // Reset attempts if 15 minutes have passed
                $failed_attempts_data['count'] = 0;
            }
        }
        
        // Get the stored answer from transient
        $stored_answer = get_transient( 'mb_math_captcha_' . $token );

        if ( $stored_answer !== false && (int) $value === (int) $stored_answer ) {
            // Clean up all captcha-related transients
            delete_transient($failed_attempts_key);
            delete_transient('mb_math_captcha_' . $token);
            
            // Also clean up any other tokens that might have been generated
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like('_transient_mb_math_captcha_') . '%'
                )
            );
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like('_transient_timeout_mb_math_captcha_') . '%'
                )
            );
            
            echo 'true';
        } else {
            // Increment failed attempts
            $failed_attempts_data['count']++;
            $failed_attempts_data['last_attempt'] = time();
            set_transient($failed_attempts_key, $failed_attempts_data, 15 * MINUTE_IN_SECONDS);
            
            $remaining_attempts = 3 - $failed_attempts_data['count'];
            if ($remaining_attempts <= 0) {
                wp_send_json_error(__('Too many failed attempts. Please try again in 15 minutes.', 'mb-math-captcha'));
            } else {
                wp_send_json_error(sprintf(
                    __('Incorrect answer. Please try again. You have %d more attempt(s) remaining.', 'mb-math-captcha'),
                    $remaining_attempts
                ));
            }
        }
        die();
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
    }

    // Generate a random math challenge
    public function generate_challenge() {
        // Generate a single digit answer (0-9)
        $answer = rand(0, 9);
        
        // Generate first number (0-9)
        $num1 = rand(0, 9);
        
        // Calculate second number to ensure the sum equals our desired answer
        // If num1 is already larger than the answer, we'll use subtraction
        if ($num1 > $answer) {
            $num2 = $num1 - $answer;
            $question = sprintf('%d - %d', $num1, $num2);
        } else {
            $num2 = $answer - $num1;
            $question = sprintf('%d + %d', $num1, $num2);
        }

        // Double check that our answer is correct and single digit
        $calculated_answer = eval('return ' . $question . ';');
        if ($calculated_answer !== $answer || $calculated_answer < 0 || $calculated_answer > 9) {
            // If something went wrong, generate a simpler addition problem
            $num1 = rand(0, $answer);
            $num2 = $answer - $num1;
            $question = sprintf('%d + %d', $num1, $num2);
        }

        return [
            'question' => $question,
            'answer'   => $answer,
        ];
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
