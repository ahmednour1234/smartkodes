# Captcha System - mews/captcha Implementation

## Overview
Replaced the custom captcha implementation with the robust **mews/captcha** Laravel package for better security and reliability.

## What Changed

### 1. Package Installation
**Installed:** `mews/captcha` v3.4.6
- Includes `intervention/image` for image manipulation
- Provides multiple captcha styles and configurations
- Built-in validation rules

### 2. Configuration
**File:** `config/captcha.php`

**Default Configuration:**
```php
'default' => [
    'length' => 5,              // 5 characters
    'width' => 160,             // Image width in pixels
    'height' => 50,             // Image height in pixels
    'quality' => 90,            // Image quality (0-100)
    'math' => false,            // Use text captcha (not math)
    'expire' => 60,             // Expires in 60 seconds
    'encrypt' => false,         // No encryption
    'sensitive' => false,       // Case insensitive
]
```

**Available Styles:**
- `default` - Standard text captcha (5 characters, case insensitive)
- `math` - Math problem captcha (e.g., "2 + 3 = ?")
- `flat` - Flat design with custom colors
- `mini` - Small 3-character captcha
- `inverse` - Inverted colors with distortion

### 3. Routes
**File:** `routes/web.php`

```php
// Display captcha image
GET /captcha/{config?}
Route name: captcha

// API endpoint for AJAX refresh (returns JSON)
GET /captcha/api/{config?}
Route name: captcha.api
```

**Usage Examples:**
```php
// Display default captcha
{{ route('captcha') }}

// Display math captcha
{{ route('captcha', 'math') }}

// Get captcha via API (for AJAX refresh)
{{ route('captcha.api', 'default') }}
```

### 4. Controller Updates
**File:** `app/Http/Controllers/Auth/RegisteredUserController.php`

**Validation Rule:**
```php
'captcha' => ['required', 'captcha']
```

The `'captcha'` validation rule is provided by the mews/captcha package and:
- Automatically validates the user input against the session-stored captcha
- Is case-insensitive by default (configurable)
- Expires after 60 seconds (configurable)
- Provides clear error messages

**Removed:**
- Custom `CaptchaController::validate()` method
- Manual session checking
- Import of `CaptchaController`

### 5. View Updates
**File:** `resources/views/auth/register.blade.php`

**Captcha Display:**
```blade
<!-- Display captcha image -->
<div class="border-2 border-gray-300 rounded-lg shadow-sm overflow-hidden">
    {!! captcha_img('default') !!}
</div>
```

**Input Field:**
```blade
<input
    type="text"
    id="captcha"
    name="captcha"
    required
    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg"
    placeholder="Enter the captcha code"
    autocomplete="off"
/>
```

**JavaScript Refresh Function:**
```javascript
function refreshCaptcha() {
    fetch("/captcha/api/default")
        .then(response => response.json())
        .then(data => {
            const captchaContainer = document.querySelector('.border-2.border-gray-300.rounded-lg.shadow-sm.overflow-hidden');
            if (captchaContainer && data.img) {
                captchaContainer.innerHTML = '<img src="' + data.img + '" alt="captcha">';
            }
        })
        .catch(error => {
            console.error('Error refreshing captcha:', error);
            location.reload();
        });
}
```

### 6. Deleted Files
**Removed:** `app/Http/Controllers/CaptchaController.php`
- No longer needed
- Functionality replaced by mews/captcha package

## How It Works

### Captcha Generation
1. User loads registration page
2. `{!! captcha_img('default') !!}` generates a captcha image
3. Captcha code is stored in the session (encrypted)
4. Image is displayed to the user

### Captcha Validation
1. User submits form with captcha input
2. Laravel validates using `'captcha'` rule
3. Package compares input with session value
4. Case-insensitive by default
5. Returns error if incorrect or expired

### Captcha Refresh
1. User clicks refresh button
2. JavaScript fetches new captcha via AJAX
3. API returns JSON: `{"sensitive": false, "key": "...", "img": "data:image/png;base64,..."}`
4. JavaScript replaces the image with new captcha
5. Old captcha is invalidated

## Testing the Captcha

### Manual Test
1. Visit `/register`
2. Scroll to "Security Verification" section
3. You should see a distorted text image
4. Enter the text you see (case doesn't matter)
5. Click the refresh button to get a new captcha
6. Try submitting with wrong captcha - should show error
7. Submit with correct captcha - should proceed to payment

### Test Routes
```bash
# View captcha directly in browser
open http://localhost:8000/captcha

# Test API endpoint
curl http://localhost:8000/captcha/api/default
```

### Test Different Styles
```blade
<!-- Math captcha (e.g., "3 + 5 = ?") -->
{!! captcha_img('math') !!}

<!-- Flat design captcha -->
{!! captcha_img('flat') !!}

<!-- Mini captcha (3 characters) -->
{!! captcha_img('mini') !!}
```

## Customization Options

### Change Captcha Length
Edit `config/captcha.php`:
```php
'default' => [
    'length' => 6,  // Change from 5 to 6 characters
    // ...
]
```

### Make Case Sensitive
```php
'default' => [
    'sensitive' => true,  // Now case matters
    // ...
]
```

### Use Math Captcha Instead
In view, change:
```blade
{!! captcha_img('math') !!}
```

### Adjust Expiration Time
```php
'default' => [
    'expire' => 120,  // Expires in 2 minutes instead of 1
    // ...
]
```

### Custom Colors
```php
'default' => [
    'length' => 5,
    'width' => 160,
    'height' => 50,
    'bgColor' => '#ffffff',
    'fontColors' => ['#2c3e50', '#c0392b', '#16a085'],
    'lines' => 3,
    // ...
]
```

## Troubleshooting

### Captcha Image Not Showing
**Check GD Library:**
```bash
php -m | grep gd
```

**Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Validation Always Fails
**Check session driver:**
- Ensure `SESSION_DRIVER` in `.env` is set to `file` or `database`
- Session must persist between page load and form submission

**Clear expired sessions:**
```bash
php artisan session:clear
```

### Captcha Refresh Not Working
**Check browser console:**
- Look for JavaScript errors
- Verify API endpoint returns JSON

**Test API directly:**
```bash
curl -X GET http://localhost:8000/captcha/api/default
# Should return JSON with 'img' key
```

### "Captcha validation failed" Error
**Common causes:**
1. Captcha expired (>60 seconds)
2. Session not persisting
3. User entered wrong text
4. Case sensitivity issue (check config)

**Debug:**
```php
// In RegisteredUserController
dd(
    $request->captcha,           // User input
    session()->get('captcha'),   // Session value (encrypted)
);
```

## Security Features

### Built-in Protection
- ✅ **Session-based validation** - Can't bypass without valid session
- ✅ **Time expiration** - Old captchas automatically expire
- ✅ **One-time use** - Each captcha can only be validated once
- ✅ **Encrypted storage** - Captcha value encrypted in session (optional)
- ✅ **Distorted text** - Hard for OCR to read
- ✅ **Random characters** - Excludes similar-looking characters (0/O, 1/l, etc.)

### Additional Security
To enable encryption (stronger security):
```php
'default' => [
    'encrypt' => true,  // Encrypt captcha in session
    // ...
]
```

## API Reference

### Blade Helpers
```blade
{!! captcha_img() !!}                  <!-- Default captcha -->
{!! captcha_img('math') !!}            <!-- Math captcha -->
{!! captcha_src() !!}                  <!-- Get captcha URL -->
{!! captcha_src('flat') !!}            <!-- Get flat captcha URL -->
```

### Validation Rules
```php
// In controller
$request->validate([
    'captcha' => 'required|captcha',         // Basic validation
    'captcha' => 'required|captcha:default', // Specific config
    'captcha' => 'required|captcha:math',    // Math captcha
]);
```

### JavaScript API
```javascript
// Refresh captcha via AJAX
fetch('/captcha/api/default')
    .then(response => response.json())
    .then(data => {
        // data.img contains base64 image
        // data.sensitive indicates if case-sensitive
        // data.key is the captcha key
    });
```

## Migration from Old System

### What Was Changed
1. ✅ Removed custom `CaptchaController.php`
2. ✅ Updated routes to use package controllers
3. ✅ Changed view to use `{!! captcha_img() !!}`
4. ✅ Updated validation to use `'captcha'` rule
5. ✅ Improved refresh function with AJAX

### What Stayed The Same
- ✅ Same user experience
- ✅ Same form field name (`captcha`)
- ✅ Same UI/UX (refresh button, error messages)
- ✅ Same registration flow

## Benefits of New System

### Reliability
- ✅ Well-maintained package (1M+ downloads)
- ✅ Battle-tested in production
- ✅ Regular security updates
- ✅ Laravel 12 compatible

### Features
- ✅ Multiple captcha styles
- ✅ Math captcha option
- ✅ Configurable difficulty
- ✅ API endpoint for AJAX
- ✅ Built-in validation rule

### Developer Experience
- ✅ Simple configuration
- ✅ Clear documentation
- ✅ Blade helpers
- ✅ No custom code to maintain

## Further Reading
- Package Repository: https://github.com/mewebstudio/captcha
- Intervention Image: https://image.intervention.io/
- Laravel Validation: https://laravel.com/docs/validation
