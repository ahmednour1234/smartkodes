# Smart Kodes API - Postman Collection

## Overview

This Postman collection provides a complete set of API endpoints for the Smart Kodes platform with unified response handling and automatic token management.

## Setup Instructions

### 1. Import Collection and Environment

1. Open Postman
2. Click **Import** button
3. Import both files:
   - `SmartKodes_API.postman_collection.json`
   - `SmartKodes_API.postman_environment.json`

### 2. Configure Environment Variables

Update the following variables in the environment:

- `base_url`: Your API base URL (default: `http://localhost:8000`)
- `user_email`: Your test user email
- `user_password`: Your test user password

### 3. Authentication Flow

1. Run the **Login** request in the Authentication folder
2. The token will be automatically saved to `auth_token` environment variable
3. All subsequent requests will automatically use this token via pre-request scripts

## Response Format

All API responses follow a unified format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z"
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [ ... ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7,
      "from": 1,
      "to": 15
    },
    "timestamp": "2025-01-15T10:30:00Z"
  },
  "links": {
    "first": "url?page=1",
    "last": "url?page=7",
    "prev": null,
    "next": "url?page=2"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error message"]
  },
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "error_code": "VALIDATION_ERROR"
  }
}
```

## Features

### Automatic Token Management
- Pre-request scripts automatically add Authorization header
- Token is saved after login
- Token is cleared after logout

### Response Validation
- Test scripts validate unified response format
- Pagination structure validation
- Error response validation

### Global Scripts
- Collection-level pre-request script adds Authorization header automatically
- Collection-level test script validates all responses follow unified format

## Usage

1. **Login**: Run the Login request to authenticate
2. **Use Endpoints**: All other endpoints will automatically use the token
3. **Logout**: Run Logout to invalidate the token

## Error Codes

- `VALIDATION_ERROR`: Validation failed (422)
- `UNAUTHORIZED`: Authentication required (401)
- `FORBIDDEN`: Access denied (403)
- `NOT_FOUND`: Resource not found (404)
- `SERVER_ERROR`: Internal server error (500)

## Notes

- All timestamps are in ISO 8601 format
- All responses include a `meta.timestamp` field
- Pagination is consistent across all list endpoints
- Error responses always include `error_code` in meta

