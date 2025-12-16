# Smart Kodes - Developer Documentation

**Version:** 1.0  
**Last Updated:** January 2025  
**Framework:** Laravel 12.31.1  
**PHP Version:** 8.2.27  
**Database:** MariaDB

---

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Development Setup](#development-setup)
5. [Database Schema](#database-schema)
6. [Multi-Tenancy Architecture](#multi-tenancy-architecture)
7. [Core Modules](#core-modules)
8. [Authentication & Authorization](#authentication--authorization)
9. [Routing Structure](#routing-structure)
10. [Form Builder System](#form-builder-system)
11. [Work Orders & Records](#work-orders--records)
12. [File Management](#file-management)
13. [API Development](#api-development)
14. [Common Development Tasks](#common-development-tasks)
15. [Security Considerations](#security-considerations)
16. [Performance Optimization](#performance-optimization)
17. [Testing](#testing)
18. [Troubleshooting](#troubleshooting)

---

## Overview

**Smart Kodes** (also branded as "Smart Site") is a multi-tenant SaaS application that provides:

- **Dynamic Form Building**: Visual drag-and-drop form builder with 20+ field types
- **Work Order Management**: Create, assign, and track work orders with associated forms
- **Data Collection**: Collect, store, and manage form submissions (records)
- **Multi-Tenant Isolation**: Complete data isolation between tenants
- **Role-Based Access Control**: Granular permissions system
- **Project Management**: Organize forms and work orders by projects

### Key Features

- ✅ Multi-tenant architecture with complete data isolation
- ✅ Advanced form builder with conditional logic and calculated fields
- ✅ Work order workflow management
- ✅ Record submission and approval system
- ✅ File upload and management
- ✅ Audit logging for all operations
- ✅ Export functionality (CSV/Excel)
- ✅ Subscription and billing management

---

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Smart Kodes Platform                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────┐         ┌──────────────┐             │
│  │ System Admin │         │ Tenant Admin │             │
│  │   Portal     │         │    Portal    │             │
│  └──────┬───────┘         └──────┬───────┘             │
│         │                        │                      │
│         └──────────┬─────────────┘                      │
│                    │                                    │
│         ┌──────────▼──────────┐                         │
│         │  Tenant Middleware  │                         │
│         │  (Isolation Layer) │                         │
│         └──────────┬──────────┘                         │
│                    │                                    │
│         ┌──────────▼──────────┐                         │
│         │   Application Layer │                         │
│         │  (Controllers/Models)                         │
│         └──────────┬──────────┘                         │
│                    │                                    │
│         ┌──────────▼──────────┐                         │
│         │   Database Layer   │                         │
│         │  (Shared DB + tenant_id)                      │
│         └────────────────────┘                         │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Architectural Patterns

1. **Multi-Tenancy**: Shared database with `tenant_id` column isolation
2. **Repository Pattern**: Controller → Model → Database
3. **Middleware-Based Security**: TenantMiddleware enforces tenant isolation
4. **Service-Oriented**: Separation between admin and tenant namespaces
5. **Event-Driven**: Audit logging with `created_by`/`updated_by` tracking

---

## Technology Stack

### Backend

- **Framework**: Laravel 12.31.1
- **PHP**: 8.2.27
- **Database**: MariaDB
- **Authentication**: Laravel Sanctum (session + API tokens)
- **Queue**: Database (recommended: Redis for production)
- **Cache**: Database (recommended: Redis for production)
- **Session**: Database

### Frontend

- **CSS Framework**: Tailwind CSS (via CDN)
- **JavaScript**: Vanilla JavaScript (no framework)
- **Icons**: Inline SVG
- **Form Builder**: Custom drag-and-drop implementation

### Key Dependencies

```json
{
  "laravel/framework": "^12.0",
  "laravel/sanctum": "^4.2",
  "maatwebsite/excel": "^3.1",
  "mews/captcha": "^3.4",
  "symfony/expression-language": "^7.3"
}
```

---

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- MariaDB/MySQL
- Node.js (for asset compilation, if needed)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd smartkodes.syscomdemos.com
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smartkodes
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed database (optional)**
   ```bash
   php artisan db:seed
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

### Development Commands

```bash
# Run tests
php artisan test

# Code formatting
./vendor/bin/pint

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Monitor logs
php artisan pail
```

---

## Database Schema

### Core Tables

#### Tenants
```sql
tenants
├── id (ULID, Primary Key)
├── name
├── slug (unique)
├── domain (nullable)
├── plan_id (FK to plans)
├── status (0=inactive, 1=active, 2=suspended)
├── storage_quota
├── api_rate_limit
├── created_by, updated_by (FK to users)
├── deleted_at (soft delete)
└── timestamps
```

#### Users
```sql
users
├── id (ULID, Primary Key)
├── tenant_id (nullable - null = super admin)
├── name
├── email (unique)
├── password
├── email_verified_at
├── remember_token
├── created_by, updated_by
├── deleted_at (soft delete)
└── timestamps
```

#### Forms
```sql
forms
├── id (ULID, Primary Key)
├── tenant_id (FK to tenants)
├── project_id (FK to projects, nullable)
├── name
├── description (nullable)
├── schema_json (JSON)
├── version (integer)
├── status (0=draft, 1=live, 2=archived)
├── created_by, updated_by
├── deleted_at (soft delete)
└── timestamps
```

#### Form Fields
```sql
form_fields
├── id (ULID, Primary Key)
├── tenant_id (FK to tenants)
├── form_id (FK to forms)
├── name
├── type
├── config_json (JSON)
├── order
├── is_sensitive (boolean)
├── default_value
├── placeholder
├── regex_pattern
├── visibility_rules (JSON)
├── conditional_logic (JSON)
├── min_value, max_value
├── options (JSON)
├── currency_symbol
├── calculation_formula
├── deleted_at (soft delete)
└── timestamps
```

#### Work Orders
```sql
work_orders
├── id (ULID, Primary Key)
├── tenant_id (FK to tenants)
├── project_id (FK to projects)
├── assigned_to (FK to users, nullable)
├── status (0=draft, 1=assigned, 2=in_progress, 3=completed)
├── due_date (nullable)
├── created_by, updated_by
├── deleted_at (soft delete)
└── timestamps
```

#### Records
```sql
records
├── id (ULID, Primary Key)
├── tenant_id (FK to tenants)
├── project_id (FK to projects)
├── form_id (FK to forms)
├── form_version_id (FK to form_versions)
├── work_order_id (FK to work_orders, nullable)
├── submitted_by (FK to users)
├── submitted_at
├── location (JSON - GPS coordinates)
├── ip_address
├── status (0=draft, 1=submitted, 2=approved, 3=rejected)
├── created_by, updated_by
├── deleted_at (soft delete)
└── timestamps
```

### Relationships

```
Tenant
├── hasMany Users
├── hasMany Projects
├── hasMany Forms
├── hasMany WorkOrders
├── hasMany Records
├── hasMany Roles
├── hasMany Permissions
└── hasOne Subscription

Form
├── belongsTo Tenant
├── belongsTo Project
├── hasMany FormFields
├── hasMany FormVersions
├── hasMany Records
└── belongsToMany WorkOrders

WorkOrder
├── belongsTo Tenant
├── belongsTo Project
├── belongsTo AssignedUser
├── hasMany Records
└── belongsToMany Forms

Record
├── belongsTo Tenant
├── belongsTo Project
├── belongsTo Form
├── belongsTo FormVersion
├── belongsTo WorkOrder
├── belongsTo SubmittedBy
├── hasMany RecordFields
└── hasMany Files
```

---

## Multi-Tenancy Architecture

### Tenant Isolation Strategy

Smart Kodes uses a **shared database with tenant_id column isolation** approach:

- All tenant-scoped tables include a `tenant_id` column
- Middleware enforces tenant context on every request
- Super admins (`tenant_id = NULL`) can access all tenants
- Regular users are restricted to their tenant's data

### Tenant Middleware

**File**: `app/Http/Middleware/TenantMiddleware.php`

The middleware performs the following:

1. **Checks authentication**: Only applies to authenticated users
2. **Identifies user type**:
   - Super Admin (`tenant_id = NULL`): No tenant filtering
   - Regular User: Enforces tenant isolation
3. **Validates tenant status**: Only active tenants (status = 1) are allowed
4. **Sets tenant context**: Stores current tenant in session

```php
// Tenant context is stored in session
session(['tenant_context.current_tenant' => $tenant]);
```

### Accessing Tenant Context

In controllers and models:

```php
// Get current tenant from session
$currentTenant = session('tenant_context.current_tenant');

// Query with tenant isolation
$forms = Form::where('tenant_id', $currentTenant->id)->get();

// Or use a scope (if defined in model)
$forms = Form::forTenant($currentTenant->id)->get();
```

### Super Admin Access

Super admins can:
- Access `/admin/*` routes
- View all tenants
- Impersonate tenants
- Manage system-wide settings

Super admins are identified by `tenant_id = NULL` in the users table.

---

## Core Modules

### 1. Forms Module

**Controller**: `App\Http\Controllers\Admin\FormController`  
**Model**: `App\Models\Form`  
**Routes**: `/tenant/forms/*` and `/admin/forms/*`

#### Features

- **Form Builder**: Visual drag-and-drop interface
- **Field Types**: 20+ field types (text, email, date, signature, GPS, etc.)
- **Form Versioning**: Track form changes over time
- **Status Management**: Draft → Live → Archived
- **Template System**: Import/export form templates as JSON
- **Form Cloning**: Duplicate existing forms

#### Form Builder Workflow

1. Create form → `/tenant/forms/create`
2. Redirect to builder → `/tenant/forms/{id}/builder`
3. Drag and drop fields
4. Configure field properties
5. Save builder → `/tenant/forms/{id}/save-builder`
6. Publish form → `/tenant/forms/{id}/publish`

#### Supported Field Types

- **Basic**: text, textarea, email, phone, url, number, currency
- **Selection**: select, multiselect, radio, checkbox
- **Date/Time**: date, time, datetime
- **Special**: signature, barcode, qr, photo, video, audio, file
- **Advanced**: gps, calculated, section, pagebreak

### 2. Projects Module

**Controller**: `App\Http\Controllers\Admin\ProjectController`  
**Model**: `App\Models\Project`  
**Routes**: `/tenant/projects/*`

Projects organize forms and work orders. Each project belongs to a tenant.

### 3. Work Orders Module

**Controller**: `App\Http\Controllers\Admin\WorkOrderController`  
**Model**: `App\Models\WorkOrder`  
**Routes**: `/tenant/work-orders/*`

#### Work Order Statuses

- `0` = Draft
- `1` = Assigned
- `2` = In Progress
- `3` = Completed

#### Work Order Workflow

1. Create work order with project and forms
2. Assign to user
3. User completes forms
4. Records are created from submissions
5. Work order status updates

### 4. Records Module

**Controller**: `App\Http\Controllers\Admin\RecordController`  
**Model**: `App\Models\Record`  
**Routes**: `/tenant/records/*`

Records store form submission data. Each record:
- Links to a form and form version
- Can be associated with a work order
- Contains field values in `record_fields` table
- Supports comments and approvals

#### Record Statuses

- `0` = Draft
- `1` = Submitted
- `2` = Approved
- `3` = Rejected

### 5. Users Module

**Controller**: `App\Http\Controllers\Admin\UserController`  
**Model**: `App\Models\User`  
**Routes**: `/tenant/users/*` and `/admin/users/*`

Manages users within tenants. Supports:
- User CRUD operations
- Role assignment
- Permission management
- Export functionality

### 6. Reports Module

**Controller**: `App\Http\Controllers\Admin\ReportController`  
**Routes**: `/tenant/reports/*`

Provides analytics and reporting:
- Submissions by status
- Submissions over time
- Form analytics
- Custom report generation

---

## Authentication & Authorization

### Authentication

Uses **Laravel Sanctum** for:
- Session-based authentication (web)
- API token authentication (mobile/external)

### Authorization

**Role-Based Access Control (RBAC)**:

- **Roles**: Group of permissions
- **Permissions**: Granular access controls
- **Role-User**: Many-to-many relationship
- **Permission-Role**: Many-to-many relationship

#### Default Roles

1. **System Administrator** (`tenant_id = NULL`)
   - Full system access
   - Can manage all tenants
   - Can impersonate tenants

2. **Tenant Admin**
   - Full access within tenant
   - Can manage users, forms, projects
   - Can view all tenant data

3. **Manager**
   - Read/write within assigned projects
   - Can create work orders
   - Can view assigned records

4. **Mobile User**
   - Mobile-only functionality
   - Can view assigned work orders
   - Can submit records

### Permission Checking

```php
// Check if user has permission
if ($user->hasPermission('forms.create')) {
    // Allow action
}

// Check if user has role
if ($user->hasRole('admin')) {
    // Allow action
}
```

---

## Routing Structure

### Route Organization

Routes are organized by context:

1. **Admin Routes** (`/admin/*`): System administrator access
2. **Tenant Routes** (`/tenant/*`): Tenant-scoped operations
3. **Shared Routes**: Profile, authentication

### Key Route Groups

```php
// Super Admin Routes
Route::middleware(['auth:web', 'tenant'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('tenants', TenantController::class);
    Route::resource('users', UserController::class);
    // ...
});

// Tenant Routes
Route::middleware(['auth:web', 'tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::resource('forms', FormController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('work-orders', WorkOrderController::class);
    // ...
});
```

### Important Routes

| Route | Purpose |
|-------|---------|
| `/tenant/forms` | List all forms |
| `/tenant/forms/create` | Create new form |
| `/tenant/forms/{id}/builder` | Form builder interface |
| `/tenant/forms/{id}/save-builder` | Save form builder data |
| `/tenant/work-orders` | List work orders |
| `/tenant/records` | List records/submissions |
| `/admin/tenants` | Manage tenants (super admin) |

---

## Form Builder System

### Architecture

The form builder uses:
- **Backend**: Laravel controllers handle form schema storage
- **Frontend**: Vanilla JavaScript with HTML5 drag-and-drop API
- **Storage**: JSON schema in `forms.schema_json` and `form_fields` table

### Form Schema Structure

```json
{
  "fields": [
    {
      "key": "field_1",
      "type": "text",
      "label": "First Name",
      "placeholder": "Enter your first name",
      "required": true,
      "order": 0
    }
  ]
}
```

### Field Configuration

Each field in `form_fields` table stores:
- **Basic**: name, type, label, placeholder
- **Validation**: required, regex_pattern, min_value, max_value
- **Advanced**: visibility_rules, conditional_logic, calculation_formula
- **Sensitivity**: is_sensitive flag for data masking

### Adding New Field Types

1. **Add to form builder UI** (`resources/views/tenant/forms/builder.blade.php`)
2. **Add field rendering logic** (when implementing form renderer)
3. **Add validation rules** (in form submission handler)
4. **Update field type list** in controller/model

---

## Work Orders & Records

### Work Order Lifecycle

```
Create Work Order
    ↓
Assign to User
    ↓
User Opens Work Order
    ↓
User Fills Associated Forms
    ↓
Records Created from Submissions
    ↓
Work Order Status Updated
    ↓
Manager Reviews Records
    ↓
Work Order Completed
```

### Record Submission Flow

1. **Form Submission**: User submits form data
2. **Record Creation**: System creates `Record` entry
3. **Field Storage**: Field values stored in `record_fields` table
4. **File Upload**: Media files stored via `files` table
5. **Status Update**: Record status set to "submitted"
6. **Notifications**: Relevant users notified
7. **Webhooks**: External systems notified (if configured)

### Record Field Storage

Field values are stored in `record_fields` table:
- Text values: stored as text
- JSON values: stored as JSON (for complex data)
- File references: stored as file IDs

---

## File Management

### File Storage

Files are stored using Laravel's filesystem:
- **Local**: `storage/app/public/`
- **Production**: Should use S3 or similar

### File Model

**Model**: `App\Models\File`

Files use polymorphic relationships:
- Can be attached to records, forms, work orders, etc.

### File Upload Handling

```php
// Store uploaded file
$file = $request->file('photo');
$path = $file->store('uploads', 'public');

// Create file record
File::create([
    'tenant_id' => $currentTenant->id,
    'fileable_id' => $record->id,
    'fileable_type' => Record::class,
    'name' => $file->getClientOriginalName(),
    'path' => $path,
    'mime_type' => $file->getMimeType(),
    'size' => $file->getSize(),
    'uploaded_by' => Auth::id(),
]);
```

---

## API Development

### API Authentication

Uses **Laravel Sanctum** for API token authentication:

```php
// Generate token
$token = $user->createToken('api-token')->plainTextToken;

// Use token in requests
Authorization: Bearer {token}
```

### API Routes

API routes should be defined in `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/forms', [Api\FormController::class, 'index']);
    Route::post('/forms/{id}/submit', [Api\FormController::class, 'submit']);
});
```

### API Rate Limiting

Tenant-specific rate limits stored in `tenants.api_rate_limit`:
- Should be enforced via middleware
- Currently stored but not enforced

---

## Common Development Tasks

### Creating a New Module

1. **Create Migration**
   ```bash
   php artisan make:migration create_example_table
   ```

2. **Create Model**
   ```bash
   php artisan make:model Example
   ```

3. **Create Controller**
   ```bash
   php artisan make:controller Admin/ExampleController --resource
   ```

4. **Add Routes**
   ```php
   Route::resource('examples', ExampleController::class);
   ```

5. **Create Views**
   - `index.blade.php`
   - `create.blade.php`
   - `edit.blade.php`
   - `show.blade.php`

### Adding Tenant Isolation

Always include `tenant_id` in:
- Migration: `$table->foreignUlid('tenant_id')->constrained('tenants')`
- Model: Add to `$fillable` array
- Controller: Filter queries by `tenant_id`

```php
$currentTenant = session('tenant_context.current_tenant');
$items = Example::where('tenant_id', $currentTenant->id)->get();
```

### Adding Audit Fields

Include `created_by` and `updated_by`:

```php
// Migration
$table->foreignUlid('created_by')->nullable()->constrained('users');
$table->foreignUlid('updated_by')->nullable()->constrained('users');

// Controller
Model::create([
    'created_by' => Auth::id(),
    'updated_by' => Auth::id(),
]);
```

### Exporting Data

Uses **Maatwebsite Excel** package:

```php
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExampleExport;

return Excel::download(new ExampleExport, 'examples.xlsx');
```

---

## Security Considerations

### Critical Security Practices

1. **Tenant Isolation**: Always filter by `tenant_id`
2. **Input Validation**: Use Form Requests for validation
3. **CSRF Protection**: Enabled by default in Laravel
4. **SQL Injection**: Use Eloquent ORM (parameterized queries)
5. **XSS Protection**: Blade templates escape by default

### Security Vulnerabilities to Address

1. **Calculated Field Formula Execution** 🔴
   - Current: Uses `Function()` which allows arbitrary JS execution
   - Fix: Use safe expression parser (e.g., `math.js`, `expr-eval`)

2. **File Upload Validation** ⚠️
   - Add file size limits
   - Validate MIME types
   - Scan for viruses

3. **API Rate Limiting** ⚠️
   - Implement tenant-specific rate limits
   - Use Laravel's rate limiter

---

## Performance Optimization

### Database Optimization

1. **Eager Loading**: Prevent N+1 queries
   ```php
   Form::with(['project', 'creator', 'formFields'])->get();
   ```

2. **Database Indexes**: Add indexes on frequently queried columns
   ```php
   $table->index(['tenant_id', 'status']);
   ```

3. **Query Optimization**: Use select() to limit columns
   ```php
   Form::select('id', 'name', 'status')->get();
   ```

### Caching Strategy

1. **Configuration Cache**: `php artisan config:cache`
2. **Route Cache**: `php artisan route:cache`
3. **View Cache**: `php artisan view:cache`
4. **Application Cache**: Use Redis for production

### Recommended Production Setup

- **Cache**: Redis
- **Queue**: Redis
- **Session**: Redis
- **File Storage**: S3 or similar
- **Database**: Read replicas for scaling

---

## Testing

### Running Tests

```bash
php artisan test
```

### Test Structure

```
tests/
├── Feature/
│   ├── FormTest.php
│   ├── WorkOrderTest.php
│   └── ...
└── Unit/
    ├── FormModelTest.php
    └── ...
```

### Writing Tests

```php
public function test_user_can_create_form()
{
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->post('/tenant/forms', [
        'name' => 'Test Form',
    ]);
    
    $response->assertStatus(302);
    $this->assertDatabaseHas('forms', ['name' => 'Test Form']);
}
```

---

## Troubleshooting

### Common Issues

#### 1. Tenant Context Not Set

**Problem**: `session('tenant_context.current_tenant')` returns null

**Solution**: 
- Check `TenantMiddleware` is applied
- Verify user has `tenant_id` set
- Check tenant status is active (1)

#### 2. N+1 Query Problems

**Problem**: Too many database queries

**Solution**: Use eager loading
```php
$forms = Form::with(['project', 'creator'])->get();
```

#### 3. Form Builder Not Saving

**Problem**: Form builder changes not persisting

**Solution**:
- Check CSRF token
- Verify JavaScript console for errors
- Check `saveBuilder` route is accessible
- Verify form belongs to current tenant

#### 4. File Upload Fails

**Problem**: Files not uploading

**Solution**:
- Check `storage/app/public` is writable
- Verify `php.ini` upload limits
- Check filesystem configuration
- Verify tenant storage quota

#### 5. Permission Denied Errors

**Problem**: Users can't access resources

**Solution**:
- Check user has correct role
- Verify role has required permissions
- Check tenant context is set
- Verify resource belongs to user's tenant

---

## Additional Resources

### Documentation Files

- `PROJECT_ANALYSIS.md` - Detailed system analysis
- `FORM_BUILDER_GUIDE.md` - Form builder usage
- `QUICK_START_GUIDE.md` - Quick start instructions
- `DETAILED_SCOPE_GAP_ANALYSIS.md` - Feature gap analysis

### Laravel Documentation

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel Eloquent](https://laravel.com/docs/eloquent)

### Key Packages

- [Maatwebsite Excel](https://docs.laravel-excel.com/)
- [Mews Captcha](https://github.com/mewebstudio/captcha)

---

## Support & Contribution

### Getting Help

1. Check this documentation
2. Review existing code examples
3. Check Laravel documentation
4. Review project analysis documents

### Contributing

1. Follow Laravel coding standards
2. Use PSR-12 code style
3. Write tests for new features
4. Update documentation
5. Use meaningful commit messages

---

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Maintained By**: Development Team

