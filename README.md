# Meta Box Math CAPTCHA

A standalone WordPress plugin that adds a privacy-compliant Math CAPTCHA field type to the Meta Box ecosystem. This plugin provides a secure, AJAX-validated Math CAPTCHA that works seamlessly with both backend and frontend forms, including those created with the Meta Box Frontend Submission addon.

## Features

- Privacy-compliant CAPTCHA solution
- AJAX-based validation
- Multilingual support
- Security-focused implementation
- Seamless Meta Box integration
- Works with both backend and frontend forms

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Meta Box plugin
- Meta Box Frontend Submission addon (for frontend forms)

## Installation

1. Download the plugin
2. Upload the `mb-math-captcha` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

The Math CAPTCHA field can be added to any form managed by the Meta Box plugin, including both backend forms and frontend forms created with the [Meta Box Frontend Submission](https://metabox.io/plugins/meta-box-frontend-submission/) addon.

There are two primary methods for integrating the Math CAPTCHA:

### 1. Adding to a Field Group

You can directly embed the `math_captcha` field type into any of your Meta Box field group definitions. This is the most flexible method and gives you full control over its placement.

The field requires a custom validation rule to function correctly. Make sure to set `'save_field' => false` to prevent the answer from being stored in the database.

**Example Field Group Configuration:**
```php
$meta_boxes[] = [
    'title'  => 'Contact Form',
    'id'     => 'contact_form',
    'fields' => [
        // ... your other form fields ...
        [
            'id'         => 'math_challenge',
            'name'       => 'Are you human?',
            'type'       => 'math_captcha',
            'save_field' => false, // Important: prevents saving the value
            'required'   => true,
        ],
    ],
    // Add the validation rule for the captcha
    'validation' => [
        'rules' => [
            'math_challenge' => [
                'math_captcha' => true,
            ],
        ],
    ],
];
```

### 2. Using the Included math_captcha_box Meta Box with Frontend Forms

For quick integration into frontend forms, the plugin provides a pre-configured demo meta box with the ID `math_captcha_box`. You can append this ID to the `mb_frontend_form` shortcode.

This method is ideal for quickly adding a CAPTCHA without creating a new field group definition.

**Example Shortcode Usage:**
```shortcode
[mb_frontend_form id="your_field_group_id,math_captcha_box"]
```
This will display your form fields from `your_field_group_id` followed by the math captcha.

## Security Features

- Transient-based challenge storage
- Automatic cleanup of used challenges
- Nonce verification for AJAX requests

## Database Handling

The plugin ensures that:
- Transients are automatically cleaned up after successful validation
- No personally identifiable information is stored

## Localization

The plugin is fully translatable:
- Uses WordPress text domain for all strings
- Includes .pot file for translations
- Supports multilingual challenge rendering
- Includes formal & informal German translations

## Development

### Plugin Structure

```
/mb-math-captcha/
├── mb-math-captcha.php
├── js/
│   └── captcha.js
├── includes/
│   ├── class-mb-math-captcha.php
│   └── field-mb-math-captcha.php
├── languages/
│   ├── mb-math-captcha.pot
│   ├── mb-math-captcha-de_DE.po
│   ├── mb-math-captcha-de_DE.mo
│   ├── mb-math-captcha-de_DE_formal.po
│   └── mb-math-captcha-de_DE_formal.mo
```

## License

This plugin is licensed under the GPL v2 or later.