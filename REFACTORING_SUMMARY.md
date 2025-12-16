# Code Refactoring Summary

## Overview
Refactored controllers to move private functions to services and replaced hardcoded status values with constants.

## Changes Made

### 1. Status Constants Created

#### `app/Constants/WorkOrderStatus.php`
- Constants: `DRAFT = 0`, `ASSIGNED = 1`, `IN_PROGRESS = 2`, `COMPLETED = 3`
- Methods: `all()`, `getLabel()`, `allWithLabels()`

#### `app/Constants/FormStatus.php`
- Constants: `DRAFT = 0`, `LIVE = 1`, `ARCHIVED = 2`
- Methods: `all()`, `getLabel()`, `allWithLabels()`

#### `app/Constants/RecordStatus.php`
- Constants: `DRAFT = 0`, `SUBMITTED = 1`, `APPROVED = 2`, `REJECTED = 3`
- Methods: `all()`, `getLabel()`, `allWithLabels()`

### 2. Services Created

#### `app/Services/WorkOrderService.php`
Moved from `WorkOrderController` private methods:
- `handleFileUpload()` - Handles file uploads for form fields
- `evaluateFormula()` - Evaluates calculated field formulas
- `getGoogleMapsUrl()` - Generates Google Maps URL
- `getGoogleMapsDirectionsUrl()` - Generates Google Maps directions URL
- `registerMathFunctions()` - Registers math functions for expression language

#### `app/Services/FormService.php`
Moved from `FormController` private methods:
- `handleFileUpload()` - Handles file uploads for form fields
- `evaluateFormula()` - Evaluates calculated field formulas
- `registerMathFunctions()` - Registers math functions for expression language

### 3. Controllers Refactored

#### `app/Http/Controllers/Api/WorkOrderController.php`
**Removed:**
- `private function handleFileUpload()`
- `private function evaluateFormula()`
- `private function getGoogleMapsUrl()`
- `private function getGoogleMapsDirectionsUrl()`

**Added:**
- Dependency injection for `WorkOrderService`
- Usage of `WorkOrderStatus` and `RecordStatus` constants
- Service method calls instead of private methods

**Before:**
```php
private function handleFileUpload($file, $formField, $record, $user) { ... }
$fieldValue = $this->handleFileUpload($file, $formField, $record, $user);
```

**After:**
```php
// Injected WorkOrderService
protected WorkOrderService $workOrderService;

// Using service method
$fieldValue = $this->workOrderService->handleFileUpload($file, $formField, $record, $user);
```

#### `app/Http/Controllers/Api/FormController.php`
**Removed:**
- `private function handleFileUpload()`
- `private function evaluateFormula()`

**Added:**
- Dependency injection for `FormService`
- Service method calls instead of private methods

### 4. Resources Updated

#### `app/Http/Resources/Api/WorkOrderResource.php`
**Before:**
```php
private function getStatusLabel(): string
{
    return match($this->status) {
        0 => 'draft',
        1 => 'assigned',
        2 => 'in_progress',
        3 => 'completed',
        default => 'unknown',
    };
}
```

**After:**
```php
use App\Constants\WorkOrderStatus;

'status_label' => WorkOrderStatus::getLabel($this->status),
```

#### `app/Http/Resources/Api/FormResource.php`
**Before:**
```php
private function getStatusLabel(): string
{
    return match($this->status) {
        0 => 'draft',
        1 => 'live',
        2 => 'archived',
        default => 'unknown',
    };
}
```

**After:**
```php
use App\Constants\FormStatus;

'status_label' => FormStatus::getLabel($this->status),
```

#### `app/Http/Resources/Api/RecordResource.php`
**Before:**
```php
private function getStatusLabel(): string
{
    return match($this->status) {
        0 => 'draft',
        1 => 'submitted',
        2 => 'approved',
        3 => 'rejected',
        default => 'unknown',
    };
}
```

**After:**
```php
use App\Constants\RecordStatus;

'status_label' => RecordStatus::getLabel($this->status),
```

## Benefits

1. **Separation of Concerns**: Business logic moved from controllers to services
2. **Reusability**: Services can be used across multiple controllers
3. **Testability**: Services can be easily unit tested
4. **Maintainability**: Status values centralized in constants
5. **Consistency**: Status labels use the same source of truth
6. **DRY Principle**: No code duplication between controllers

## File Structure

```
app/
├── Constants/
│   ├── WorkOrderStatus.php
│   ├── FormStatus.php
│   └── RecordStatus.php
├── Services/
│   ├── WorkOrderService.php
│   └── FormService.php
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── WorkOrderController.php (refactored)
│           └── FormController.php (refactored)
└── Http/
    └── Resources/
        └── Api/
            ├── WorkOrderResource.php (updated)
            ├── FormResource.php (updated)
            └── RecordResource.php (updated)
```

## Usage Examples

### Using Status Constants
```php
use App\Constants\WorkOrderStatus;

// Check status
if ($workOrder->status === WorkOrderStatus::IN_PROGRESS) {
    // ...
}

// Get label
$label = WorkOrderStatus::getLabel($workOrder->status);

// Get all statuses
$allStatuses = WorkOrderStatus::all();
```

### Using Services
```php
use App\Services\WorkOrderService;

// In controller constructor
public function __construct(WorkOrderService $workOrderService)
{
    $this->workOrderService = $workOrderService;
}

// Use service methods
$url = $this->workOrderService->getGoogleMapsUrl($lat, $lon);
$fieldValue = $this->workOrderService->handleFileUpload($file, $formField, $record, $user);
```

## Notes

- All private methods removed from controllers
- All status values now use constants
- Services follow single responsibility principle
- Code is cleaner and more maintainable
- No breaking changes to API responses

