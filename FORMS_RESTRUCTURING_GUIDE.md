# Forms & Work Orders Restructuring - Implementation Guide

## 🎯 Objective

Restructure the relationship between Forms, Projects, and Work Orders to match the correct business logic:

### ❌ OLD (Incorrect) Architecture:
```
Forms → belong to Projects (project_id FK)
Work Orders → belong to Project + ONE Form (form_id FK)
```

### ✅ NEW (Correct) Architecture:
```
Forms → Standalone templates (NO project_id)
Work Orders → belong to Projects
Work Orders ↔ Forms → Many-to-Many relationship (pivot table)
```

---

## 📊 Business Logic Flow

### Form Creation:
1. **Tenant Admin** creates form templates
2. Forms are **tenant-wide** resources (not tied to projects)
3. Forms can be reused across multiple projects/work orders

### Work Order Creation:
1. **Users** create projects
2. Users create work orders within projects
3. Users assign **one or multiple forms** to each work order
4. Each work order tracks which forms need to be completed

### Record Submission:
1. Users fill out assigned forms
2. Records are linked to both work_order and specific form
3. Work order tracks completion based on required forms

---

## 🗄️ Database Changes

### Migration Created:
**File:** `2025_10_05_123541_restructure_forms_and_work_orders_relationships.php`

### Changes:

#### 1. New Pivot Table: `form_work_order`
```sql
CREATE TABLE form_work_order (
    id ULID PRIMARY KEY,
    work_order_id ULID FK → work_orders.id,
    form_id ULID FK → forms.id,
    order INT DEFAULT 0,  -- Order of forms in work order
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(work_order_id, form_id)
);
```

#### 2. Drop from `work_orders` table:
```sql
ALTER TABLE work_orders
DROP COLUMN form_id;  -- Remove single form relationship
```

#### 3. Drop from `forms` table:
```sql
ALTER TABLE forms
DROP COLUMN project_id;  -- Forms no longer belong to projects
DROP INDEX forms_tenant_id_project_id_name_deleted_at_unique;
DROP INDEX forms_tenant_id_project_id_status_index;
ADD UNIQUE INDEX forms_tenant_id_name_deleted_at_unique;
ADD INDEX forms_tenant_id_status_index;
```

---

## 🔧 Model Changes

### ✅ Form.php

#### Remove:
```php
'project_id' from fillable
public function project(): BelongsTo
```

#### Change:
```php
// OLD:
public function workOrders(): HasMany
{
    return $this->hasMany(WorkOrder::class);
}

// NEW:
public function workOrders(): BelongsToMany
{
    return $this->belongsToMany(WorkOrder::class, 'form_work_order')
                ->withTimestamps()
                ->withPivot('order')
                ->orderByPivot('order', 'asc');
}
```

### ✅ WorkOrder.php

#### Remove:
```php
'form_id' from fillable
```

#### Change:
```php
// OLD:
public function form(): BelongsTo
{
    return $this->belongsTo(Form::class);
}

// NEW:
public function forms(): BelongsToMany
{
    return $this->belongsToMany(Form::class, 'form_work_order')
                ->withTimestamps()
                ->withPivot('order')
                ->orderByPivot('order', 'asc');
}
```

### ✅ Project.php

#### Remove:
```php
public function forms(): HasMany
{
    return $this->hasMany(Form::class);
}
```

---

## 🎮 Controller Changes Needed

### 1. FormController

#### `index()` - Remove project filter:
```php
// OLD:
$forms = Form::where('tenant_id', $currentTenant->id)
             ->where('project_id', $request->project_id)
             ->get();

// NEW:
$forms = Form::where('tenant_id', $currentTenant->id)->get();
```

#### `create()` - Remove project selection:
```php
// OLD:
$projects = Project::where('tenant_id', $currentTenant->id)->get();
return view('admin.forms.create', compact('projects'));

// NEW:
// No projects needed
return view('admin.forms.create');
```

#### `store()` - Remove project_id validation:
```php
// OLD:
$request->validate([
    'project_id' => 'required|exists:projects,id',
    'name' => 'required',
    ...
]);

// NEW:
$request->validate([
    'name' => 'required|unique:forms,name,NULL,id,tenant_id,'.$currentTenant->id,
    ...
]);
```

### 2. WorkOrderController

#### `create()` - Keep as is (still loads forms)

#### `store()` - Change to handle multiple forms:
```php
// OLD:
$request->validate([
    'project_id' => 'required|exists:projects,id',
    'form_id' => 'required|exists:forms,id',
    ...
]);

$workOrder = WorkOrder::create([
    'tenant_id' => $currentTenant->id,
    'project_id' => $request->project_id,
    'form_id' => $request->form_id,
    ...
]);

// NEW:
$request->validate([
    'project_id' => 'required|exists:projects,id',
    'form_ids' => 'required|array|min:1',
    'form_ids.*' => 'exists:forms,id',
    ...
]);

$workOrder = WorkOrder::create([
    'tenant_id' => $currentTenant->id,
    'project_id' => $request->project_id,
    ...
]);

// Attach forms with order
foreach ($request->form_ids as $index => $formId) {
    $workOrder->forms()->attach($formId, ['order' => $index]);
}
```

#### `show()` - Change eager loading:
```php
// OLD:
$workOrder = WorkOrder::with(['project', 'form', 'assignedUser'])->find($id);

// NEW:
$workOrder = WorkOrder::with(['project', 'forms', 'assignedUser'])->find($id);
```

#### `update()` - Handle form sync:
```php
// NEW: Add form sync logic
$workOrder->update([...]);

// Sync forms
if ($request->has('form_ids')) {
    $formData = [];
    foreach ($request->form_ids as $index => $formId) {
        $formData[$formId] = ['order' => $index];
    }
    $workOrder->forms()->sync($formData);
}
```

### 3. RecordController

#### Keep `work_order_id` but may need to add `form_id` if not present:
```php
// Records should track which specific form they're filling out
'work_order_id' => 'required|exists:work_orders,id',
'form_id' => 'required|exists:forms,id',  // Which form template was used
```

---

## 🎨 View Changes Needed

### 1. Forms Views

#### `index.blade.php`:
- ❌ Remove project filter dropdown
- ❌ Remove project column from table
- ✅ Show tenant-wide forms

#### `create.blade.php`:
- ❌ Remove project selection dropdown
- ✅ Show form creation with just name and schema

#### `show.blade.php`:
- ❌ Remove "Project" info card
- ✅ Add "Used in Work Orders" section (show which work orders use this form)

### 2. Work Order Views

#### `create.blade.php`:
```blade
<!-- OLD: Single form dropdown -->
<select name="form_id" required>
    <option value="">Select Form</option>
    @foreach($forms as $form)
        <option value="{{ $form->id }}">{{ $form->name }}</option>
    @endforeach
</select>

<!-- NEW: Multiple form selection -->
<label>Assign Forms (select one or more)</label>
<select name="form_ids[]" multiple required class="...">
    @foreach($forms as $form)
        <option value="{{ $form->id }}">{{ $form->name }} (v{{ $form->version }})</option>
    @endforeach
</select>
<p class="text-xs">Hold Ctrl/Cmd to select multiple forms</p>

<!-- OR: Use checkboxes for better UX -->
<div class="space-y-2">
    @foreach($forms as $form)
        <label class="flex items-center">
            <input type="checkbox" name="form_ids[]" value="{{ $form->id }}" 
                   class="mr-2">
            {{ $form->name }} (v{{ $form->version }})
        </label>
    @endforeach
</div>
```

#### `show.blade.php`:
```blade
<!-- OLD: Single form display -->
<div>
    <label>Form Template</label>
    <p>{{ $workOrder->form->name }}</p>
</div>

<!-- NEW: Multiple forms list -->
<div>
    <h3>Assigned Forms ({{ $workOrder->forms->count() }})</h3>
    <ul>
        @foreach($workOrder->forms as $form)
            <li>
                {{ $loop->iteration }}. 
                <a href="{{ route('admin.forms.show', $form->id) }}">
                    {{ $form->name }} (v{{ $form->version }})
                </a>
            </li>
        @endforeach
    </ul>
</div>
```

#### `index.blade.php`:
```blade
<!-- OLD: -->
<td>{{ $workOrder->form->name ?? 'N/A' }}</td>

<!-- NEW: -->
<td>
    @if($workOrder->forms->count() > 0)
        <div class="flex flex-wrap gap-1">
            @foreach($workOrder->forms->take(2) as $form)
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                    {{ $form->name }}
                </span>
            @endforeach
            @if($workOrder->forms->count() > 2)
                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                    +{{ $workOrder->forms->count() - 2 }} more
                </span>
            @endif
        </div>
    @else
        <span class="text-gray-400 italic">No forms assigned</span>
    @endif
</td>
```

### 3. Project Views

#### `show.blade.php`:
- ❌ Remove "Forms" section
- ✅ Keep "Work Orders" section
- ✅ Work orders will show their assigned forms

---

## 🧪 Testing Checklist

### Forms Module:
- [ ] Create form WITHOUT selecting project
- [ ] Form name must be unique per tenant (not per project)
- [ ] View all forms (tenant-wide list)
- [ ] Edit form
- [ ] Delete form (check cascade to form_work_order pivot)

### Work Orders Module:
- [ ] Create work order with ONE form
- [ ] Create work order with MULTIPLE forms
- [ ] View work order → see all assigned forms in order
- [ ] Edit work order → change forms (sync)
- [ ] Delete work order (check pivot records deleted)

### Integration:
- [ ] Create form → create work order → assign that form
- [ ] Create 3 forms → create work order → assign all 3 forms
- [ ] View form → see "Used in X work orders"
- [ ] View project → see work orders (not forms directly)

---

## 📝 Migration Steps

### Step 1: Run Migration
```bash
php artisan migrate
```

This will:
1. Create `form_work_order` pivot table
2. Migrate existing data (work_orders.form_id → pivot table)
3. Drop `form_id` from `work_orders`
4. Drop `project_id` from `forms`
5. Update indexes

### Step 2: Clear Caches
```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

### Step 3: Update Controllers
- FormController (remove project references)
- WorkOrderController (handle multiple forms)
- ProjectController (remove forms relationship)

### Step 4: Update Views
- Forms views (remove project dropdowns)
- Work orders views (multiple form selection)
- Project views (remove forms section)

### Step 5: Test Thoroughly
- Create new forms
- Create new work orders with multiple forms
- Verify existing data migrated correctly

---

## ⚠️ Important Notes

### Data Migration:
- ✅ Existing work orders will have their single `form_id` migrated to pivot table
- ✅ All relationships preserved during migration
- ✅ Can rollback if needed (migration has `down()` method)

### Breaking Changes:
- ❌ `$workOrder->form` will no longer work
- ✅ Use `$workOrder->forms` (returns collection)
- ❌ `$form->project` will no longer work
- ✅ Forms are now tenant-wide resources

### API/Frontend Impact:
- Any API endpoints returning work order data need updating
- Frontend expecting single `form` object needs to handle `forms` array
- Form creation forms need project_id removed

---

## 🎉 Benefits of New Architecture

1. **Reusable Forms**: Forms can be used across multiple projects
2. **Multiple Forms per Work Order**: More flexible workflow
3. **Simpler Form Management**: Tenant admins create templates once
4. **Better Organization**: Clear separation of templates vs. work
5. **Scalability**: Easier to manage large numbers of forms

---

**Status:** Migration created, models updated. Controllers and views need updating next.

