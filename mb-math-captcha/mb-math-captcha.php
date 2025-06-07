<?php
/**
 * Plugin Name: Meta Box Math CAPTCHA
 * Description: Adds a GDPR-compliant Math CAPTCHA field for use in Meta Box forms.
 * Version: 1.1.0
 * Author: Twinpictures
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'MB_MATH_CAPTCHA_VERSION', '1.0.0' );
define( 'MB_MATH_CAPTCHA_DIR', plugin_dir_path( __FILE__ ) );
define( 'MB_MATH_CAPTCHA_URL', plugin_dir_url( __FILE__ ) );

// Trigger plugin loading
add_action( 'plugins_loaded', 'mb_math_captcha_load_files' );
// Trigger initialization
add_action( 'init', 'mb_math_captcha_init' );

// Initialize the plugin
function mb_math_captcha_init() {
    /*
    if ( ! class_exists( 'RWMB_Field' ) ) {
        return;
    }
    */

    // Load text domain
    load_plugin_textdomain( 'mb-math-captcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Load custom field
    require MB_MATH_CAPTCHA_DIR . 'includes/field-mb-math-captcha.php';

    // Add hooks for enqueuing scripts
    add_action('admin_enqueue_scripts', array('RWMB_Math_Captcha_Field', 'admin_enqueue_scripts'));
    add_action('wp_enqueue_scripts', array('RWMB_Math_Captcha_Field', 'frontend_enqueue_scripts'));

}

// Load the plugin
function mb_math_captcha_load_files() {
    require MB_MATH_CAPTCHA_DIR . 'includes/class-mb-math-captcha.php';
}

// Register activation hook
register_activation_hook( __FILE__, function() {
    if ( ! class_exists( 'RWMB_Field' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and activate Meta Box plugin first.', 'mb-math-captcha' ) );
    }
}); 