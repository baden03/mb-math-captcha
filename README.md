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
The Math CAPTCHA field works automatically with Meta Box forms. Add the field to your form configuration:

```php
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
```

The field will be rendered and validated using the custom validation method.

## Security Features

- Transient-based challenge storage
- Automatic cleanup of used challenges
- Prevention of database storage for CAPTCHA values
- Nonce verification for AJAX requests

## Database Handling

The plugin ensures that:
- Math CAPTCHA values are never stored in the database
- Transients are automatically cleaned up after successful validation
- No personally identifiable information is stored

## Localization

The plugin is fully translatable:
- Uses WordPress text domain for all strings
- Includes .pot file for translations
- Supports multilingual challenge rendering
- Includes German (formal) translation

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
│   ├── mb-math-captcha-de_DE_formal.po
│   └── mb-math-captcha-de_DE_formal.mo
```

## License

This plugin is licensed under the GPL v2 or later.