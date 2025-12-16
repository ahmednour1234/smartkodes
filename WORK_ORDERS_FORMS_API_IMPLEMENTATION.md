# Work Orders & Forms API Implementation

## Overview
Complete API implementation for Work Orders and Forms management with location-based filtering, Google Maps integration, and form submission capabilities.

## Features Implemented

### Work Orders API
- ✅ List work orders assigned to user
- ✅ Filter by: Distance, Priority, Nearby location (radius-based)
- ✅ Sort by: Priority, Due Date, Distance, Created Date
- ✅ Get work order with map information
- ✅ Calculate distance and estimated travel time
- ✅ Google Maps URL generation
- ✅ Google Maps directions URL (redirects to app)
- ✅ Get form details for work order
- ✅ Submit form data with file uploads

### Forms API
- ✅ List forms (tenant-scoped: `tenant_id = user->tenant_id`)
- ✅ Get form with all fields
- ✅ Update form data for records (add missing data)

## Files Created

### Repositories
1. **WorkOrderRepository** (`app/Repositories/WorkOrderRepository.php`)
   - `getAssignedToUser()` - Get work orders with filters
   - `getWithMapInfo()` - Get work order with map data
   - `calculateDistance()` - Haversine formula for distance calculation
   - `getEstimatedTime()` - Calculate estimated travel time

2. **FormRepository** (`app/Repositories/FormRepository.php`)
   - `getWithFilters()` - Get forms with search/filter
   - `getForSubmission()` - Get form with all fields
   - `findPublished()` - Get published forms

### Resources
1. **WorkOrderResource** (`app/Http/Resources/Api/WorkOrderResource.php`)
   - Includes: project, assigned_user, status, priority, location, forms, distance

2. **FormResource** (`app/Http/Resources/Api/FormResource.php`)
   - Includes: fields, category, schema_json, status

3. **FormFieldResource** (`app/Http/Resources/Api/FormFieldResource.php`)
   - Includes: type, label, validation rules, options, config

4. **RecordResource** (`app/Http/Resources/Api/RecordResource.php`)
   - Includes: form, work_order, fields, files, location

5. **FileResource** (`app/Http/Resources/Api/FileResource.php`)
   - Includes: name, path, url, size, mime_type

### Controllers
1. **WorkOrderController** (`app/Http/Controllers/Api/WorkOrderController.php`)
   - `index()` - List work orders with filters
   - `show()` - Get work order with map info
   - `getForm()` - Get form details for work order
   - `submitForm()` - Submit form data
   - `getMapUrl()` - Get Google Maps URL
   - `getDirectionsUrl()` - Get Google Maps directions URL

2. **FormController** (`app/Http/Controllers/Api/FormController.php`)
   - `index()` - List forms (tenant-scoped)
   - `show()` - Get form with fields
   - `updateFormData()` - Update record with missing form data

### Request Classes
1. **ListWorkOrdersRequest** - Validation for work order listing filters
2. **SubmitFormRequest** - Dynamic validation based on form fields
3. **UpdateFormDataRequest** - Validation for updating form data

## API Endpoints

### Work Orders

#### List Work Orders
```
GET /api/v1/work-orders
Query Parameters:
  - page (default: 1)
  - per_page (default: 15)
  - status (0=draft, 1=assigned, 2=in_progress, 3=completed)
  - priority (integer)
  - latitude (required with longitude)
  - longitude (required with latitude)
  - radius (km, default: 10)
  - sort_by (priority, due_date, distance, created_at)
  - sort_order (asc, desc)
```

**Response:**
```json
{
  "success": true,
  "message": "Work orders retrieved successfully",
  "data": [
    {
      "id": "...",
      "project": {...},
      "status": 1,
      "priority_value": 5,
      "location": {
        "latitude": 30.0444,
        "longitude": 31.2357
      },
      "distance": 2.5,
      "distance_unit": "km",
      "forms": [...]
    }
  ],
  "meta": {
    "pagination": {...},
    "timestamp": "..."
  },
  "links": {...}
}
```

#### Get Work Order
```
GET /api/v1/work-orders/{id}?current_latitude=30.0444&current_longitude=31.2357
```

**Response includes:**
- Work order details
- Map information (if location provided)
- Distance calculation (if current location provided)
- Estimated travel time

#### Get Map URL
```
GET /api/v1/work-orders/{id}/map
```

**Response:**
```json
{
  "success": true,
  "data": {
    "url": "https://www.google.com/maps?q=30.0444,31.2357",
    "destination": {
      "latitude": 30.0444,
      "longitude": 31.2357
    }
  }
}
```

#### Get Directions URL
```
GET /api/v1/work-orders/{id}/directions?latitude=30.0444&longitude=31.2357
```

**Response:**
```json
{
  "success": true,
  "data": {
    "url": "https://www.google.com/maps/dir/30.0444,31.2357/30.0555,31.2468",
    "destination": {
      "latitude": 30.0555,
      "longitude": 31.2468
    }
  }
}
```

#### Get Form Details
```
GET /api/v1/work-orders/{workOrder}/forms/{form}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "...",
    "name": "Inspection Form",
    "fields": [
      {
        "id": "...",
        "name": "field_name",
        "type": "text",
        "label": "Field Label",
        "required": true,
        "validation": {...},
        "config": {...}
      }
    ]
  }
}
```

#### Submit Form
```
POST /api/v1/work-orders/{workOrder}/submit-form
Content-Type: multipart/form-data

Body:
  - form_id: required
  - work_order_id: required
  - latitude: optional
  - longitude: optional
  - {field_name}: dynamic fields based on form
  - {file_field}: file uploads
```

**Response:**
```json
{
  "success": true,
  "message": "Form submitted successfully",
  "data": {
    "id": "...",
    "form": {...},
    "work_order": {...},
    "fields": {...},
    "files": [...]
  }
}
```

### Forms

#### List Forms (Tenant-Scoped)
```
GET /api/v1/forms?page=1&per_page=15&status=1&category_id=xxx&search=keyword
```

**Note:** Automatically filtered by `tenant_id = user->tenant_id`

#### Get Form
```
GET /api/v1/forms/{id}
```

#### Update Form Data for Record
```
PUT /api/v1/forms/{form}/records/{record}
Content-Type: multipart/form-data

Body:
  - {field_name}: only missing fields
```

## Distance Calculation

Uses **Haversine formula** to calculate distance between two coordinates:
- Formula: `d = 2R × arcsin(√(sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)))`
- Returns distance in kilometers
- Used for filtering nearby work orders

## Google Maps Integration

### Map URL
- Format: `https://www.google.com/maps?q={latitude},{longitude}`
- Opens location in Google Maps

### Directions URL
- With origin: `https://www.google.com/maps/dir/{origin_lat},{origin_lon}/{dest_lat},{dest_lon}`
- Without origin: `https://www.google.com/maps/dir/?api=1&destination={dest_lat},{dest_lon}`
- Opens Google Maps app with directions

## Form Submission Flow

1. User gets work order
2. User gets form details for work order
3. User fills form fields (text, number, file uploads, etc.)
4. User submits form
5. System validates against form field rules
6. System creates Record
7. System creates RecordField entries
8. System uploads files and creates File records
9. System updates work order status to "In Progress"
10. Returns created record

## File Upload Handling

- Files stored in: `storage/app/public/tenants/{tenant_id}/records/{record_id}/`
- Supports: file, photo, video, audio field types
- File size limits:
  - Photos: 5MB
  - Videos: 50MB
  - Audio: 10MB
  - Other files: 10MB

## Filtering & Sorting

### Work Orders
- **By Status**: 0=draft, 1=assigned, 2=in_progress, 3=completed
- **By Priority**: Filter by priority_value
- **By Distance**: Requires latitude, longitude, and radius (km)
- **Sort Options**: priority, due_date, distance, created_at
- **Sort Order**: asc, desc

### Forms
- **By Status**: 0=draft, 1=live, 2=archived
- **By Category**: category_id
- **By Search**: name or description

## Security & Authorization

- All endpoints require authentication (`auth:sanctum`)
- Work orders filtered by `assigned_to = user->id`
- Forms filtered by `tenant_id = user->tenant_id`
- Records filtered by tenant
- File uploads scoped to tenant

## Postman Collection

All endpoints added to Postman collection with:
- Pre-request scripts for token management
- Test scripts for response validation
- Example requests
- Query parameter documentation

## Usage Examples

### Filter Work Orders by Nearby Location
```
GET /api/v1/work-orders?latitude=30.0444&longitude=31.2357&radius=5&sort_by=distance
```

### Get Work Order with Distance Calculation
```
GET /api/v1/work-orders/{id}?current_latitude=30.0444&current_longitude=31.2357
```

### Submit Form with File Upload
```
POST /api/v1/work-orders/{id}/submit-form
Content-Type: multipart/form-data

form_id: 01HXYZ456DEF
work_order_id: 01HXYZ123ABC
field_name: "Value"
photo_field: [file]
latitude: 30.0444
longitude: 31.2357
```

## Notes

- Distance calculation uses Haversine formula (accurate for short distances)
- Estimated time is a simple calculation (average speed 50 km/h)
- For production, consider using Google Maps Distance Matrix API for accurate travel times
- All responses follow unified format
- All errors handled globally
- Pagination is consistent across all list endpoints

