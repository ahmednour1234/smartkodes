# Scribe API Documentation Setup

## Overview
Scribe has been successfully installed and configured for the SmartKodes API documentation.

## Installation Status
✅ Scribe package installed (`knuckleswtf/scribe`)
✅ Configuration file published (`config/scribe.php`)
✅ Configured for Laravel Sanctum authentication
✅ Configured to document all `/api/*` routes

## Configuration

### Authentication
- **Type**: Bearer Token (Laravel Sanctum)
- **Header**: `Authorization: Bearer {token}`
- **Default**: Most endpoints require authentication
- **Public Endpoints**: Login, Forgot Password, Reset Password

### Documentation Access
Once generated, documentation will be available at:
- **HTML Docs**: `/docs`
- **Postman Collection**: `/docs.postman`
- **OpenAPI Spec**: `/docs.openapi`

## Generating Documentation

### Generate Documentation
```bash
php artisan scribe:generate
```

This will:
1. Scan all API routes matching `/api/*`
2. Extract endpoint information from controllers
3. Generate HTML documentation
4. Generate Postman collection
5. Generate OpenAPI specification

### View Documentation
After generation, visit: `http://your-app-url/docs`

## Adding Documentation to Controllers

### Basic Example
```php
/**
 * @group Authentication
 * 
 * Login to get authentication token
 * 
 * @bodyParam email string required The user's email address. Example: user@example.com
 * @bodyParam password string required The user's password. Example: password123
 * 
 * @response 200 {
 *   "success": true,
 *   "message": "Login successful",
 *   "data": {
 *     "user": {...},
 *     "token": "1|..."
 *   }
 * }
 */
public function login(LoginRequest $request) { ... }
```

### With Authentication
```php
/**
 * @group Users
 * @authenticated
 * 
 * Get list of users
 * 
 * @queryParam page integer Page number. Example: 1
 * @queryParam per_page integer Items per page. Example: 15
 * 
 * @response 200 {
 *   "success": true,
 *   "data": [...]
 * }
 */
public function index(Request $request) { ... }
```

### Public Endpoint
```php
/**
 * @group Authentication
 * @unauthenticated
 * 
 * Public endpoint that doesn't require authentication
 */
public function publicEndpoint() { ... }
```

## Documentation Groups

Organize endpoints into groups using `@group`:
- `@group Authentication` - Login, logout, password reset
- `@group Users` - User management endpoints
- `@group Work Orders` - Work order endpoints
- `@group Forms` - Form management endpoints

## Response Examples

### Success Response
```php
/**
 * @response 200 {
 *   "success": true,
 *   "message": "Operation successful",
 *   "data": {...},
 *   "meta": {
 *     "timestamp": "2025-01-15T10:30:00Z"
 *   }
 * }
 */
```

### Error Response
```php
/**
 * @response 422 {
 *   "success": false,
 *   "message": "Validation failed",
 *   "errors": {
 *     "email": ["The email field is required."]
 *   }
 * }
 */
```

## Advanced Features

### URL Parameters
```php
/**
 * @urlParam id string required The work order ID. Example: 01HXYZ123ABC
 */
public function show(string $id) { ... }
```

### Query Parameters
```php
/**
 * @queryParam status integer Filter by status. Example: 1
 * @queryParam latitude float Filter by latitude. Example: 30.0444
 * @queryParam longitude float Filter by longitude. Example: 31.2357
 * @queryParam distance integer Distance in km. Example: 5
 */
public function index(Request $request) { ... }
```

### Body Parameters
```php
/**
 * @bodyParam form_id string required The form ID. Example: 01HXYZ456DEF
 * @bodyParam work_order_id string required The work order ID. Example: 01HXYZ123ABC
 * @bodyParam field_name string The field value. Example: "Value"
 * @bodyParam photo_field file The photo file
 */
public function submitForm(SubmitFormRequest $request) { ... }
```

## Customization

### Update Description
Edit `config/scribe.php`:
```php
'description' => 'Your custom API description',
```

### Change Base URL
```php
'base_url' => config("app.url"),
```

### Add Logo
```php
'logo' => 'img/logo.png', // For laravel type
```

## Tips

1. **Add Documentation Comments**: Use PHPDoc comments in controllers to document endpoints
2. **Use Groups**: Organize endpoints with `@group` tags
3. **Provide Examples**: Include example values in parameter descriptions
4. **Document Responses**: Use `@response` tags to show example responses
5. **Mark Public Endpoints**: Use `@unauthenticated` for public endpoints

## Regenerating Documentation

After making changes to controllers or routes:
```bash
php artisan scribe:generate
```

## Troubleshooting

### Documentation Not Updating
- Clear config cache: `php artisan config:clear`
- Regenerate: `php artisan scribe:generate`

### Missing Endpoints
- Check route prefix matches `api/*` in config
- Verify routes are defined in `routes/api.php`
- Check for `@hideFromApiDocumentation` tags

### Authentication Issues
- Set `SCRIBE_AUTH_KEY` in `.env` for testing
- Verify Sanctum is properly configured
- Check middleware is applied correctly

## Next Steps

1. Add documentation comments to all API controllers
2. Organize endpoints into logical groups
3. Generate initial documentation: `php artisan scribe:generate`
4. Review and refine documentation
5. Share documentation URL with team/consumers

## Resources

- [Scribe Documentation](https://scribe.knuckles.wtf/laravel)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [OpenAPI Specification](https://swagger.io/specification/)

