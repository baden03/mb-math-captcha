=== Meta Box Math CAPTCHA ===
Contributors: twinpictures
Tags: meta-box, captcha, math, security, form, validation
Requires at least: 6.0
Tested up to: 6.8.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a privacy-compliant Math CAPTCHA field type to Meta Box forms, with AJAX validation.

== Description ==

Meta Box Math CAPTCHA adds a new field type to the Meta Box ecosystem that provides a secure, privacy-compliant CAPTCHA solution. This plugin works seamlessly with both backend and frontend forms created with Meta Box.

= Key Features =

* Privacy-compliant CAPTCHA solution
* AJAX-based validation
* Multilingual support
* Security-focused implementation
* Seamless Meta Box integration
* Works with both backend and frontend forms

= Security Features =

* Transient-based challenge storage
* Automatic cleanup of used challenges
* Prevention of database storage for CAPTCHA values
* Nonce verification for AJAX requests

== Installation ==

1. Upload the `mb-math-captcha` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure Meta Box plugin is installed and activated

== Usage ==

Add the Math CAPTCHA field to your Meta Box configuration:

[code]
[
    'id'   => 'math_challenge',
    'name' => 'Are you human?',
    'type' => 'math_captcha',
    'save_field' => false,
    'required' => true,
],
'validation' => [
    'rules' => [
        'math_challenge' => [
            'math_captcha' => true
        ],
    ],
],
[/code]

The field will be rendered and validated using the custom validation method.

== Frequently Asked Questions ==

= Does this plugin work with Meta Box Frontend Submission? =

Yes, the Math CAPTCHA field works automatically with both backend and frontend forms created with Meta Box Frontend Submission.

= Is this plugin GDPR compliant? =

Yes, the plugin is designed to be privacy-friendly:
* No personal data is stored
* Uses simple math problems instead of image-based CAPTCHAs
* No external services are required

== Screenshots ==

1. Math CAPTCHA field in a Meta Box form
2. Validation error message

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release 