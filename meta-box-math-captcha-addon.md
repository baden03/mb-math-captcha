# AI Agent Task Prompt: Create Math CAPTCHA Add-On for Meta Box Frontend Submission

## Goal
Create a **standalone plugin** that adds a new `math_captcha` field type to the Meta Box ecosystem. This field will provide a privacy-compliant, AJAX-validated Math CAPTCHA for use in both backend and frontend submission forms.

---

## Features to Implement

### 1. Custom Field Type: `math_captcha`
- Register a new Meta Box field type called `math_captcha`.
- On render:
  - Generate a random math challenge (e.g., `3 + 5`)
  - Store the result in a WordPress transient tied to a unique token (valid for 5 minutes)
  - Show the challenge in the field's description text
  - Include a hidden field or token ID to link to the stored solution

### 2. Manual Field Usage
- Developers can manually add the `math_captcha` field to any form:
  ```php
  [
      'id'   => 'my_math_captcha',
      'name' => 'Are you human?',
      'type' => 'math_captcha',
  ]
  ```

### 3. Remote Validation via AJAX
- Integrate with [Meta Box remote validation](https://docs.metabox.io/validation/#remote-validation)
- Create a custom AJAX endpoint (both `wp_ajax_` and `wp_ajax_nopriv_`) to validate the user's answer

### 4. Failure Handling
- If answer is incorrect:
  - Trigger a regeneration of a new math challenge and update the transient
  - Send back the new challenge via AJAX to update the field description
  - Return feedback like: "Incorrect. Try again."

### 5. Rate Limiting
- Track failed attempts by IP using transients or a custom log
- After 3 failed attempts:
  - Block validation from that IP for 15 minutes
  - Return message: "Too many failed attempts. Please wait 15 minutes and try again."

---

## Implementation Steps

1. **Register Field Type**
   - Hook into `rwmb_field_types` and define rendering logic for `math_captcha`
   - Include token generation + transient logic

2. **Render Description**
   - Output dynamic description with the current math challenge
   - Include a hidden token field to reference the transient-stored answer

3. **Add Remote Validation Callback**
   - Add AJAX endpoint `/wp-admin/admin-ajax.php?action=validate_math_captcha`
   - Accept the token and user input
   - Check the transient, validate answer, return JSON response

4. **Track and Limit Attempts**
   - Use `wp_transient_{ip}_captcha_fails` for counting attempts
   - On 3 failed attempts, set a lock transient per IP for 15 minutes

5. **JavaScript Integration**
   - On validation failure, update the field description with the new challenge using JS
   - Optionally fade in/out error message

6. **i19n**
   - Add localization support for multilingual challenge rendering
   - ues text domain for translating all strings
   - create a .pot file for all translated strings
---

## Testing Strategy

- Add the `math_captcha` field to a test form
- Test correct and incorrect input via AJAX
- Confirm description updates with new math challenge
- Simulate multiple failures and confirm rate limiting kicks in

---

## Optional Future Enhancements
- Allow token to expire early on successful submission

---

## Packaging

This should be structured as a **standalone add-on plugin**, for example:

```
/mb-math-captcha/
├── mb-math-captcha.php
├── js/
│   └── captcha.js
├── includes/
│   └── class-math-captcha-field.php
├── languages/
│   └── mb-math-captcha.pot
```

Plugin header should declare dependency on Meta Box.

---

## Suggested Plugin Header

```php
/*
Plugin Name: Meta Box Math CAPTCHA
Description: Adds a GDPR-compliant Math CAPTCHA field for use in Meta Box forms.
Version: 1.0.0
Author: Twinpictures
Requires at least: 6.0
Requires PHP: 7.4
*/
```

---

## Security Notes

- Sanitize all input and output
- Validate transient keys strictly
- Do not store any personally identifiable information

## Database Handling

### Preventing Field Storage
- Hook into `rwmb_before_save_post` to unset the math_captcha field value before database insertion
- Example implementation:
  ```php
  add_filter( 'rwmb_before_save_post', function( $post_id, $post, $update, $meta_boxes ) {
      foreach ( $meta_boxes as $meta_box ) {
          foreach ( $meta_box['fields'] as $field ) {
              if ( $field['type'] === 'math_captcha' ) {
                  unset( $_POST[$field['id']] );
              }
          }
      }
      return $post_id;
  }, 10, 4 );
  ```

### Cleanup
- Implement transient cleanup on successful form submission
- Remove the math challenge transient after validation passes
- This prevents potential replay attacks and reduces database bloat

---

## Additional Suggestions

1. **Accessibility**
   - Add ARIA labels for screen readers
   - Ensure the math challenge is properly announced
   - Consider adding a refresh button for users who need a new challenge

2. **Error Handling**
   - Add specific error messages for different failure scenarios
   - Implement graceful fallback if JavaScript is disabled
   - Add logging for debugging purposes (optional)

3. **Performance**
   - Consider caching the math challenge generation
   - Implement cleanup of expired transients

4. **Documentation**
   - Add inline code documentation
   - Create usage examples for different scenarios
   - Document all available filters and actions

5. **Security Enhancements**
   - Add nonce verification for AJAX requests
   - Implement IP-based rate limiting per form
   - Add option to configure maximum attempts
